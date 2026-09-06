<?php
defined('ABSPATH') || exit;

/**
 * Checkout & order integration.
 *
 * • Creates booking records when an order is placed.
 * • Syncs booking status with WooCommerce order status.
 * • Records deposit/balance information on the order.
 * • Adds ticket number to the Thank You page and My Account order view.
 */
final class WRB_Checkout
{
    private static ?WRB_Checkout $instance = null;

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
        // Create booking records once WC order is created.
        add_action('woocommerce_checkout_order_created', [$this, 'create_bookings_for_order']);
        // Mirror order status → booking status.
        add_action('woocommerce_order_status_changed', [$this, 'sync_order_status'], 10, 4);
        // Confirm pending bookings when payment completes.
        add_action('woocommerce_payment_complete', [$this, 'on_payment_complete']);
        // Add ticket info to thank-you page.
        add_action('woocommerce_thankyou', [$this, 'thankyou_booking_summary']);
        // Add ticket info to My Account order detail.
        add_action('woocommerce_order_details_after_order_table', [$this, 'order_detail_booking_summary']);
        // Expose ticket in order item meta display.
        add_filter('woocommerce_order_item_get_formatted_meta_data', [$this, 'format_order_item_meta'], 10, 2);
    }

    /* ── Booking creation ────────────────────────────────────────────────── */

    public function create_bookings_for_order(WC_Order $order): void
    {
        $engine = wrb_engine();

        foreach ($order->get_items() as $item_id => $item) {
            /** @var WC_Order_Item_Product $item */
            $product_id = (int) $item->get_product_id();
            if (! WRB_Product_Meta::is_bookable($product_id)) {
                continue;
            }

            $slot_id = (int) $item->get_meta('_wrb_slot_id', true);
            $adults = (int) $item->get_meta('_wrb_adults', true) ?: 1;
            $children = (int) $item->get_meta('_wrb_children', true);
            $seniors = (int) $item->get_meta('_wrb_seniors', true);
            $requests = (string) $item->get_meta('_wrb_special_requests', true);

            $booking_id = $engine->create_booking([
                'order_id' => $order->get_id(),
                'order_item_id' => $item_id,
                'product_id' => $product_id,
                'slot_id' => $slot_id,
                'customer_name' => $order->get_formatted_billing_full_name(),
                'customer_email' => $order->get_billing_email(),
                'adults' => $adults,
                'children' => $children,
                'seniors' => $seniors,
                'special_requests' => $requests,
            ]);

            if ($booking_id) {
                $booking = $engine->get_booking($booking_id);
                if ($booking) {
                    $item->add_meta_data('_wrb_booking_id', $booking_id, true);
                    $item->add_meta_data('_wrb_ticket_number', $booking->ticket_number, true);
                    $item->save();
                }
                // Store balance-due on the order.
                $balance = (float) $item->get_meta('_wrb_balance_due', true);
                if ($balance > 0) {
                    $existing = (float) $order->get_meta('_wrb_total_balance_due', true);
                    $order->update_meta_data('_wrb_total_balance_due', $existing + $balance);
                    $order->save_meta_data();
                }

                /**
                 * Fires after a booking record is created for an order item.
                 *
                 * @param  int  $booking_id
                 * @param  WC_Order  $order
                 * @param  int  $item_id
                 */
                do_action('wrb_booking_created', $booking_id, $order, $item_id);
            }
        }
    }

    /* ── Status sync ─────────────────────────────────────────────────────── */

    public function sync_order_status(int $order_id, string $from, string $to, WC_Order $order): void
    {
        $engine = wrb_engine();

        $status_map = [
            'completed' => 'confirmed',
            'processing' => 'confirmed',
            'on-hold' => 'pending',
            'cancelled' => 'cancelled',
            'refunded' => 'cancelled',
            'failed' => 'failed',
            'pending' => 'pending',
        ];
        $booking_status = $status_map[$to] ?? 'pending';

        foreach ($order->get_items() as $item) {
            $booking_id = (int) $item->get_meta('_wrb_booking_id', true);
            if ($booking_id) {
                $engine->update_booking_status($booking_id, $booking_status);
                do_action('wrb_booking_status_changed', $booking_id, $booking_status, $order);
            }
        }
    }

    public function on_payment_complete(int $order_id): void
    {
        $order = wc_get_order($order_id);
        $engine = wrb_engine();
        if (! $order) {
            return;
        }
        foreach ($order->get_items() as $item) {
            $booking_id = (int) $item->get_meta('_wrb_booking_id', true);
            if ($booking_id) {
                $engine->update_booking_status($booking_id, 'confirmed');
            }
        }
    }

    /* ── Thank You page ──────────────────────────────────────────────────── */

    public function thankyou_booking_summary(int $order_id): void
    {
        $order = wc_get_order($order_id);
        if (! $order) {
            return;
        }
        $tickets = $this->collect_tickets($order);
        if (empty($tickets)) {
            return;
        }
        echo '<section class="hgb-thankyou-tickets">';
        echo '<h2>'.esc_html__('Your booking tickets', 'wr-bookings').'</h2>';
        foreach ($tickets as $t) {
            $this->render_ticket_card($t);
        }
        echo '</section>';
    }

    public function order_detail_booking_summary(WC_Order $order): void
    {
        $tickets = $this->collect_tickets($order);
        if (empty($tickets)) {
            return;
        }
        echo '<h2>'.esc_html__('Tour booking details', 'wr-bookings').'</h2>';
        foreach ($tickets as $t) {
            $this->render_ticket_card($t);
        }
    }

    private function collect_tickets(WC_Order $order): array
    {
        $out = [];
        foreach ($order->get_items() as $item) {
            $ticket = (string) $item->get_meta('_wrb_ticket_number', true);
            if (! $ticket) {
                continue;
            }
            $out[] = [
                'ticket' => $ticket,
                'tour' => $item->get_name(),
                'date' => (string) $item->get_meta('_wrb_slot_date', true),
                'time' => (string) $item->get_meta('_wrb_slot_time', true),
                'adults' => (int) $item->get_meta('_wrb_adults', true),
                'children' => (int) $item->get_meta('_wrb_children', true),
                'seniors' => (int) $item->get_meta('_wrb_seniors', true),
                'balance' => (float) $item->get_meta('_wrb_balance_due', true),
                'meeting' => (string) get_post_meta($item->get_product_id(), '_wrb_meeting_point', true),
            ];
        }

        return $out;
    }

    private function render_ticket_card(array $t): void
    {
        $date_fmt = $t['date'] ? date_i18n(get_option('date_format'), strtotime($t['date'])) : '—';
        $time_fmt = $t['time'] ?: '';
        $guests = array_filter([
            $t['adults'] ? sprintf(_n('%d adult', '%d adults', $t['adults'], 'wr-bookings'), $t['adults']) : '',
            $t['children'] ? sprintf(_n('%d child', '%d children', $t['children'], 'wr-bookings'), $t['children']) : '',
            $t['seniors'] ? sprintf(_n('%d senior', '%d seniors', $t['seniors'], 'wr-bookings'), $t['seniors']) : '',
        ]);
        ?>
        <div class="hgb-ticket-card">
          <div class="hgb-ticket-kicker"><?php esc_html_e('Booking confirmed', 'wr-bookings'); ?></div>
          <h3 class="hgb-ticket-tour"><?php echo esc_html($t['tour']); ?></h3>
          <ul class="hgb-ticket-details">
            <?php if ($t['date']) { ?>
            <li>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
              <?php echo esc_html($date_fmt.($time_fmt ? ' @ '.$time_fmt : '')); ?>
            </li>
            <?php } ?>
            <?php if ($guests) { ?>
            <li>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
              <?php echo esc_html(implode(', ', $guests)); ?>
            </li>
            <?php } ?>
            <?php if ($t['meeting']) { ?>
            <li>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a8 8 0 00-8 8c0 5.5 8 12 8 12s8-6.5 8-12a8 8 0 00-8-8z"/><circle cx="12" cy="10" r="3"/></svg>
              <?php echo esc_html($t['meeting']); ?>
            </li>
            <?php } ?>
            <?php if ($t['balance'] > 0) { ?>
            <li class="hgb-ticket-balance">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
              <?php printf(esc_html__('Balance due at tour: %s', 'wr-bookings'), wc_price($t['balance'])); ?>
            </li>
            <?php } ?>
          </ul>
          <div class="hgb-ticket-number"><?php echo esc_html($t['ticket']); ?></div>
          <p class="hgb-ticket-note">
            <?php esc_html_e('Please present this ticket number at the meeting point. Arrive 15 minutes early.', 'wr-bookings'); ?>
          </p>
        </div>
        <?php
    }

    /* ── Order item meta display ─────────────────────────────────────────── */

    public function format_order_item_meta(array $formatted, WC_Order_Item $item): array
    {
        // Remove internal wrb_ prefixed meta from the public display; the ticket card already shows it.
        return array_filter($formatted, function ($meta) {
            return strpos($meta->key, '_wrb_') !== 0;
        });
    }
}
