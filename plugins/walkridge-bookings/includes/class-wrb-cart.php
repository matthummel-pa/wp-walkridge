<?php

defined('ABSPATH') || exit;

/**
 * WooCommerce cart integration.
 *
 * • Attaches booking metadata to cart items.
 * • Recalculates item price based on party size + tiered pricing.
 * • Applies deposit amount when configured.
 * • Validates slot availability before add-to-cart and before payment.
 * • Displays booking details in cart/checkout/order items.
 * • Handles AJAX add-to-cart for the booking widget.
 */
final class WRB_Cart
{
    private static ?WRB_Cart $instance = null;

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self;
            self::$instance->hooks();
        }

        return self::$instance;
    }

    private function hooks(): void
    {
        // Attach booking data when added to cart.
        add_filter('woocommerce_add_cart_item_data', [$this, 'add_cart_item_data'], 10, 3);
        // Validate availability.
        add_filter('woocommerce_add_to_cart_validation', [$this, 'validate_add_to_cart'], 20, 5);
        // Re-price the cart item.
        add_action('woocommerce_before_calculate_totals', [$this, 'set_cart_item_price'], 20);
        // Display booking details in cart table.
        add_filter('woocommerce_get_item_data', [$this, 'display_cart_item_data'], 10, 2);
        // Persist booking data to order item meta.
        add_action('woocommerce_checkout_create_order_line_item', [$this, 'add_order_item_meta'], 10, 4);
        // Restore cart data from session.
        add_filter('woocommerce_get_cart_item_from_session', [$this, 'restore_cart_item'], 10, 2);
        // Block duplicate bookings of the same slot.
        add_filter('woocommerce_add_to_cart_validation', [$this, 'prevent_duplicate_slot'], 30, 3);
        // Re-check every booking before payment (classic and block checkout).
        add_action('woocommerce_check_cart_items', [$this, 'revalidate_cart']);
        // Tours need a date, so shop/archive buttons link to the tour page instead of adding to cart.
        add_filter('woocommerce_product_add_to_cart_url', [$this, 'loop_button_url'], 10, 2);
        add_filter('woocommerce_product_add_to_cart_text', [$this, 'loop_button_text'], 10, 2);
        add_filter('woocommerce_product_supports', [$this, 'loop_button_no_ajax'], 10, 3);
        // AJAX handler for widget "Add to cart".
        add_action('wp_ajax_wrb_add_to_cart', [$this, 'ajax_add_to_cart']);
        add_action('wp_ajax_nopriv_wrb_add_to_cart', [$this, 'ajax_add_to_cart']);
    }

    /* ── Request parsing + validation (shared by every add-to-cart path) ─── */

    /**
     * Read the booking fields from the current request.
     *
     * Counts keep their sign so validate_booking() can reject tampered (negative) values
     * instead of silently repricing them.
     *
     * @return array{slot_id:int, adults:int, children:int, seniors:int, requests:string}
     */
    private static function read_booking_request(): array
    {
        // phpcs:disable WordPress.Security.NonceVerification.Recommended -- WooCommerce add-to-cart has no nonce by design; the AJAX path verifies wrb_ajax first.
        return [
            'slot_id' => intval(wp_unslash($_REQUEST['wrb_slot_id'] ?? 0)),
            'adults' => intval(wp_unslash($_REQUEST['wrb_adults'] ?? 1)),
            'children' => intval(wp_unslash($_REQUEST['wrb_children'] ?? 0)),
            'seniors' => intval(wp_unslash($_REQUEST['wrb_seniors'] ?? 0)),
            'requests' => mb_substr(sanitize_textarea_field(wp_unslash($_REQUEST['wrb_special_requests'] ?? '')), 0, 1000),
        ];
        // phpcs:enable
    }

    /**
     * Server-side rules for a booking. The widget enforces the same limits, but anyone can edit the request.
     *
     * @param  array{slot_id:int, adults:int, children:int, seniors:int}  $b
     * @param  int  $held  Seats on this slot already held by the shopper's own unpaid order (a payment retry).
     */
    public static function validate_booking(int $product_id, array $b, int $held = 0): true|WP_Error
    {
        $counts = [(int) $b['adults'], (int) $b['children'], (int) $b['seniors']];
        $guests = array_sum($counts);
        $max = (int) get_post_meta($product_id, '_wrb_max_group', true) ?: 99; // Same default as booking.js.

        if (min($counts) < 0 || $guests < 1 || $guests > $max) {
            return new WP_Error('wrb_party', sprintf(
                /* translators: %d: maximum number of guests */
                __('Please choose between 1 and %d guests.', 'wr-bookings'),
                $max
            ));
        }
        if ((int) $b['slot_id'] <= 0) {
            return new WP_Error('wrb_slot', __('Please choose a departure date.', 'wr-bookings'));
        }

        // Native slots live in our table, so we can check they belong to this tour and haven't passed.
        // Bridged engines own their slot IDs; their check_capacity() is the source of truth.
        if (wrb_engine()->engine_id() === 'native') {
            $slot = WRB_DB::instance()->get_slot((int) $b['slot_id']);
            if (! $slot
                || (int) $slot->product_id !== $product_id
                || $slot->status !== 'active'
                || $slot->slot_date < current_time('Y-m-d')) {
                return new WP_Error('wrb_slot', __('That departure is no longer available.', 'wr-bookings'));
            }
        }

        return wrb_engine()->check_capacity((int) $b['slot_id'], max(0, $guests - $held));
    }

    /**
     * Seats on a slot held by this shopper's unpaid order (classic `order_awaiting_payment` or the
     * block checkout's draft order). A payment retry must not be blocked by its own earlier attempt.
     */
    private static function seats_held_by_session(int $slot_id): int
    {
        if (! WC()->session) {
            return 0;
        }
        $order_ids = array_unique(array_filter([
            (int) WC()->session->get('order_awaiting_payment'),
            (int) WC()->session->get('store_api_draft_order'),
        ]));
        $held = 0;
        foreach ($order_ids as $order_id) {
            foreach (wrb_engine()->get_bookings(['order_id' => $order_id, 'slot_id' => $slot_id]) as $booking) {
                if (in_array($booking->status, ['pending', 'confirmed'], true)) {
                    $held += (int) $booking->adults + (int) $booking->children + (int) $booking->seniors;
                }
            }
        }

        return $held;
    }

    /* ── Cart data ───────────────────────────────────────────────────────── */

    /**
     * Capture booking parameters passed by the booking widget.
     * Expected request keys: wrb_slot_id, wrb_adults, wrb_children, wrb_seniors, wrb_special_requests.
     *
     * @throws Exception When the booking is invalid; WC_Cart::add_to_cart() shows the message and adds nothing.
     */
    public function add_cart_item_data(array $cart_item_data, int $product_id, int $variation_id): array
    {
        if (! WRB_Product_Meta::is_bookable($product_id)) {
            return $cart_item_data;
        }
        $b = self::read_booking_request();
        // Validate here too, so booking data can never be attached without passing the rules.
        $check = self::validate_booking($product_id, $b);
        if (is_wp_error($check)) {
            throw new Exception(esc_html($check->get_error_message()));
        }
        $slot_id = $b['slot_id'];
        $adults = $b['adults'];
        $children = $b['children'];
        $seniors = $b['seniors'];
        $requests = $b['requests'];

        $slot = WRB_DB::instance()->get_slot($slot_id);

        $pricing = WRB_Product_Meta::get_pricing($product_id);
        $full_total = ($adults * $pricing['adult'])
                     + ($children * $pricing['child'])
                     + ($seniors * $pricing['senior']);
        $charge_now = WRB_Product_Meta::calc_deposit($product_id, $full_total);
        $deposit_cfg = WRB_Product_Meta::get_deposit($product_id);

        $cart_item_data['hgb'] = [
            'slot_id' => $slot_id,
            'slot_date' => $slot ? $slot->slot_date : '',
            'slot_time' => $slot ? substr($slot->slot_time, 0, 5) : '',
            'adults' => $adults,
            'children' => $children,
            'seniors' => $seniors,
            'pricing' => $pricing,
            'full_total' => $full_total,
            'charge_now' => $charge_now,
            'is_deposit' => $charge_now < $full_total,
            'deposit_type' => $deposit_cfg['type'],
            'balance_due' => round($full_total - $charge_now, wc_get_price_decimals()),
            'special_requests' => $requests,
            'unique_key' => md5($slot_id.'-'.time().'-'.wp_rand()),
        ];

        return $cart_item_data;
    }

    /** Make each booking a unique cart item (no quantity merging). */
    public function restore_cart_item(array $cart_item, array $values): array
    {
        if (isset($values['hgb'])) {
            $cart_item['hgb'] = $values['hgb'];
        }

        return $cart_item;
    }

    /* ── Price override ─────────────────────────────────────────────────── */

    public function set_cart_item_price(WC_Cart $cart): void
    {
        if (is_admin() && ! wp_doing_ajax()) {
            return;
        }
        foreach ($cart->get_cart() as $item) {
            if (empty($item['hgb'])) {
                continue;
            }
            // Backstop: a booking line can never lower the cart total.
            $item['data']->set_price(max(0.0, (float) $item['hgb']['charge_now']));
            // Force quantity 1 — the party count is encoded in the booking data.
            $item['data']->set_sold_individually(true);
        }
    }

    /* ── Display ─────────────────────────────────────────────────────────── */

    public function display_cart_item_data(array $item_data, array $cart_item): array
    {
        if (empty($cart_item['hgb'])) {
            return $item_data;
        }
        $b = $cart_item['hgb'];
        $pricing = $b['pricing'];

        if ($b['slot_date']) {
            $item_data[] = [
                'key' => __('Tour date', 'wr-bookings'),
                'value' => esc_html(
                    date_i18n(get_option('date_format'), strtotime($b['slot_date']))
                    .($b['slot_time'] ? ' @ '.$b['slot_time'] : '')
                ),
            ];
        }

        $party = [];
        if ($b['adults'] > 0) {
            $party[] = sprintf(_n('%d adult', '%d adults', $b['adults'], 'wr-bookings'), $b['adults']).' ('.wc_price($pricing['adult']).')';
        }
        if ($b['children'] > 0) {
            $party[] = sprintf(_n('%d child', '%d children', $b['children'], 'wr-bookings'), $b['children']).' ('.wc_price($pricing['child']).')';
        }
        if ($b['seniors'] > 0) {
            $party[] = sprintf(_n('%d senior', '%d seniors', $b['seniors'], 'wr-bookings'), $b['seniors']).' ('.wc_price($pricing['senior']).')';
        }

        $item_data[] = [
            'key' => __('Party', 'wr-bookings'),
            'value' => implode(', ', $party),
        ];

        if ($b['is_deposit']) {
            $item_data[] = [
                'key' => __('Payment', 'wr-bookings'),
                'value' => sprintf(
                    /* translators: 1: deposit amount, 2: full total */
                    __('Deposit now: %1$s — Balance due: %2$s', 'wr-bookings'),
                    wc_price($b['charge_now']),
                    wc_price($b['balance_due'])
                ),
            ];
        }

        if ($b['special_requests']) {
            $item_data[] = [
                'key' => __('Special requests', 'wr-bookings'),
                'value' => esc_html($b['special_requests']),
            ];
        }

        return $item_data;
    }

    /* ── Validation ──────────────────────────────────────────────────────── */

    public function validate_add_to_cart(bool $passed, int $product_id, int $qty): bool
    {
        if (! $passed || ! WRB_Product_Meta::is_bookable($product_id)) {
            return $passed;
        }
        // A bookable product always needs a valid slot and party; there is no slot-less path.
        $check = self::validate_booking($product_id, self::read_booking_request());
        if (is_wp_error($check)) {
            wc_add_notice($check->get_error_message(), 'error');

            return false;
        }

        return $passed;
    }

    public function prevent_duplicate_slot(bool $passed, int $product_id, int $qty): bool
    {
        if (! $passed) {
            return false;
        }
        $new_slot = self::read_booking_request()['slot_id'];
        if (! $new_slot) {
            return $passed;
        }
        foreach (WC()->cart->get_cart() as $item) {
            if (! empty($item['hgb']) && (int) $item['hgb']['slot_id'] === $new_slot) {
                wc_add_notice(
                    __('This departure slot is already in your cart.', 'wr-bookings'),
                    'notice'
                );

                return false;
            }
        }

        return $passed;
    }

    /**
     * Re-check bookings before payment: seats may have sold out, or the date passed, while the item sat in the cart.
     * WooCommerce blocks checkout while an error notice is present (classic and Store API).
     */
    public function revalidate_cart(): void
    {
        if (! WC()->cart) {
            return;
        }
        foreach (WC()->cart->get_cart() as $item) {
            $product_id = (int) $item['product_id'];
            if (! WRB_Product_Meta::is_bookable($product_id)) {
                continue;
            }
            $b = $item['hgb'] ?? null;
            $check = $b === null
                ? new WP_Error('wrb_slot', __('Please choose a departure date.', 'wr-bookings'))
                : self::validate_booking($product_id, $b, self::seats_held_by_session((int) $b['slot_id']));
            if (is_wp_error($check)) {
                wc_add_notice(sprintf(
                    /* translators: 1: tour name, 2: reason */
                    __('%1$s: %2$s Please remove it from your cart and book again.', 'wr-bookings'),
                    esc_html($item['data']->get_name()),
                    esc_html($check->get_error_message())
                ), 'error');
            }
        }
    }

    /* ── Shop/archive buttons ────────────────────────────────────────────── */

    public function loop_button_url(string $url, WC_Product $product): string
    {
        return WRB_Product_Meta::is_bookable($product->get_id()) ? $product->get_permalink() : $url;
    }

    public function loop_button_text(string $text, WC_Product $product): string
    {
        return WRB_Product_Meta::is_bookable($product->get_id()) ? __('Choose a date', 'wr-bookings') : $text;
    }

    public function loop_button_no_ajax(bool $supports, string $feature, WC_Product $product): bool
    {
        return $feature === 'ajax_add_to_cart' && WRB_Product_Meta::is_bookable($product->get_id()) ? false : $supports;
    }

    /* ── Order item meta ─────────────────────────────────────────────────── */

    public function add_order_item_meta(WC_Order_Item_Product $item, string $cart_item_key, array $values, WC_Order $order): void
    {
        if (empty($values['hgb'])) {
            return;
        }
        $b = $values['hgb'];
        $item->add_meta_data('_wrb_slot_id', $b['slot_id'], true);
        $item->add_meta_data('_wrb_slot_date', $b['slot_date'], true);
        $item->add_meta_data('_wrb_slot_time', $b['slot_time'], true);
        $item->add_meta_data('_wrb_adults', $b['adults'], true);
        $item->add_meta_data('_wrb_children', $b['children'], true);
        $item->add_meta_data('_wrb_seniors', $b['seniors'], true);
        $item->add_meta_data('_wrb_full_total', $b['full_total'], true);
        $item->add_meta_data('_wrb_charge_now', $b['charge_now'], true);
        $item->add_meta_data('_wrb_balance_due', $b['balance_due'], true);
        $item->add_meta_data('_wrb_is_deposit', $b['is_deposit'], true);
        $item->add_meta_data('_wrb_special_requests', $b['special_requests'], true);
    }

    /* ── AJAX add-to-cart for booking widget ─────────────────────────────── */

    public function ajax_add_to_cart(): void
    {
        check_ajax_referer('wrb_ajax', 'nonce');

        $product_id = absint(wp_unslash($_POST['product_id'] ?? 0));
        if (! $product_id) {
            wp_send_json_error(['message' => __('Invalid booking data.', 'wr-bookings')]);
        }

        // Hand the widget's fields to the same reader the classic add-to-cart uses, so there's one place
        // that sanitizes them (read_booking_request) and one set of rules (validate_booking).
        $fields = ['slot_id' => 'wrb_slot_id', 'adults' => 'wrb_adults', 'children' => 'wrb_children',
            'seniors' => 'wrb_seniors', 'special_requests' => 'wrb_special_requests'];
        foreach ($fields as $from => $to) {
            unset($_REQUEST[$to]);
            if (isset($_POST[$from])) {
                $_REQUEST[$to] = $_POST[$from]; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- sanitized in read_booking_request().
            }
        }

        // WC_Cart::add_to_cart() doesn't run this filter itself (only WooCommerce's form/AJAX handlers do),
        // so run it here: capacity, slot and party-size rules plus the duplicate-slot check.
        if (! apply_filters('woocommerce_add_to_cart_validation', true, $product_id, 1)) {
            $notices = array_merge(wc_get_notices('error'), wc_get_notices('notice')); // Duplicate-slot uses 'notice'.
            wc_clear_notices();
            wp_send_json_error(['message' => $notices ? wp_strip_all_tags($notices[0]['notice'])
                : __('Could not add to cart. Please try again.', 'wr-bookings')]);
        }

        $ok = WC()->cart->add_to_cart($product_id, 1);

        if (! $ok) {
            $notices = wc_get_notices('error');
            $msg = $notices ? wp_strip_all_tags($notices[0]['notice'])
                                : __('Could not add to cart. Please try again.', 'wr-bookings');
            wc_clear_notices();
            wp_send_json_error(['message' => $msg]);
        }

        wp_send_json_success([
            'message' => __('Booking added to cart.', 'wr-bookings'),
            'cart_url' => wc_get_cart_url(),
            'cart_count' => WC()->cart->get_cart_contents_count(),
        ]);
    }
}
