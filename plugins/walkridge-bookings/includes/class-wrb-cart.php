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
        // AJAX handler for widget "Add to cart".
        add_action('wp_ajax_wrb_add_to_cart', [$this, 'ajax_add_to_cart']);
        add_action('wp_ajax_nopriv_wrb_add_to_cart', [$this, 'ajax_add_to_cart']);
    }

    /* ── Cart data ───────────────────────────────────────────────────────── */

    /**
     * Capture booking parameters passed by the booking widget.
     * Expected POST keys: wrb_slot_id, wrb_adults, wrb_children, wrb_seniors.
     */
    public function add_cart_item_data(array $cart_item_data, int $product_id, int $variation_id): array
    {
        if (! WRB_Product_Meta::is_bookable($product_id)) {
            return $cart_item_data;
        }
        $slot_id = (int) ($_REQUEST['wrb_slot_id'] ?? 0);
        $adults = (int) ($_REQUEST['wrb_adults'] ?? 1);
        $children = (int) ($_REQUEST['wrb_children'] ?? 0);
        $seniors = (int) ($_REQUEST['wrb_seniors'] ?? 0);
        $requests = sanitize_textarea_field($_REQUEST['wrb_special_requests'] ?? '');

        if ($slot_id <= 0 && ! isset($_REQUEST['wrb_skip_slot'])) {
            return $cart_item_data;
        }

        $slot = $slot_id > 0 ? WRB_DB::instance()->get_slot($slot_id) : null;

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
            $item['data']->set_price($item['hgb']['charge_now']);
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
        if (! WRB_Product_Meta::is_bookable($product_id)) {
            return $passed;
        }
        $slot_id = (int) ($_REQUEST['wrb_slot_id'] ?? 0);
        if ($slot_id <= 0) {
            return $passed; // widget not invoked — let through
        }
        $adults = (int) ($_REQUEST['wrb_adults'] ?? 1);
        $children = (int) ($_REQUEST['wrb_children'] ?? 0);
        $seniors = (int) ($_REQUEST['wrb_seniors'] ?? 0);
        $total = $adults + $children + $seniors;

        $check = wrb_engine()->check_capacity($slot_id, $total);
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
        $new_slot = (int) ($_REQUEST['wrb_slot_id'] ?? 0);
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

        $product_id = (int) ($_POST['product_id'] ?? 0);
        $slot_id = (int) ($_POST['slot_id'] ?? 0);
        $adults = max(0, (int) ($_POST['adults'] ?? 1));
        $children = max(0, (int) ($_POST['children'] ?? 0));
        $seniors = max(0, (int) ($_POST['seniors'] ?? 0));
        $requests = sanitize_textarea_field($_POST['special_requests'] ?? '');

        if (! $product_id || ($adults + $children + $seniors) < 1) {
            wp_send_json_error(['message' => __('Invalid booking data.', 'wr-bookings')]);
        }

        // Inject into $_REQUEST so woocommerce_add_cart_item_data picks it up.
        $_REQUEST['wrb_slot_id'] = $slot_id;
        $_REQUEST['wrb_adults'] = $adults;
        $_REQUEST['wrb_children'] = $children;
        $_REQUEST['wrb_seniors'] = $seniors;
        $_REQUEST['wrb_special_requests'] = $requests;

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
