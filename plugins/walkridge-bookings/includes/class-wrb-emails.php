<?php
defined('ABSPATH') || exit;

/**
 * Email enhancements — adds booking details + ticket number to WooCommerce
 * order emails (Processing, Completed, On-Hold, Customer Invoice).
 */
final class WRB_Emails
{
    private static ?WRB_Emails $instance = null;

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
        // Inject booking section after the order table in customer emails.
        add_action('woocommerce_email_after_order_table', [$this, 'email_booking_details'], 10, 4);
    }

    public function email_booking_details(WC_Order $order, bool $sent_to_admin, bool $plain_text, WC_Email $email): void
    {
        $tickets = [];
        foreach ($order->get_items() as $item) {
            $ticket = (string) $item->get_meta('_wrb_ticket_number', true);
            if (! $ticket) {
                continue;
            }
            $tickets[] = [
                'ticket' => $ticket,
                'tour' => $item->get_name(),
                'date' => (string) $item->get_meta('_wrb_slot_date', true),
                'time' => (string) $item->get_meta('_wrb_slot_time', true),
                'adults' => (int) $item->get_meta('_wrb_adults', true),
                'children' => (int) $item->get_meta('_wrb_children', true),
                'seniors' => (int) $item->get_meta('_wrb_seniors', true),
                'balance' => (float) $item->get_meta('_wrb_balance_due', true),
                'special' => (string) $item->get_meta('_wrb_special_requests', true),
                'meeting' => (string) get_post_meta($item->get_product_id(), '_wrb_meeting_point', true),
            ];
        }

        if (empty($tickets)) {
            return;
        }

        $confirm_note = get_option('wrb_confirmation_note', '');
        $cancel_policy = get_option('wrb_cancellation_policy', '');

        if ($plain_text) {
            echo "\n".strtoupper(__('Booking Details', 'wr-bookings'))."\n";
            echo str_repeat('-', 40)."\n";
            foreach ($tickets as $t) {
                echo __('Tour', 'wr-bookings').': '.$t['tour']."\n";
                if ($t['date']) {
                    echo __('Date', 'wr-bookings').': '
                        .date_i18n(get_option('date_format'), strtotime($t['date']))
                        .($t['time'] ? ' @ '.$t['time'] : '')."\n";
                }
                echo __('Ticket', 'wr-bookings').': '.$t['ticket']."\n";
                if ($t['meeting']) {
                    echo __('Meeting point', 'wr-bookings').': '.$t['meeting']."\n";
                }
                if ($t['balance']) {
                    echo __('Balance due at tour', 'wr-bookings').': '.strip_tags(wc_price($t['balance']))."\n";
                }
                if ($t['special']) {
                    echo __('Special requests', 'wr-bookings').': '.$t['special']."\n";
                }
                echo "\n";
            }
            if ($confirm_note) {
                echo $confirm_note."\n\n";
            }
            if ($cancel_policy) {
                echo __('Cancellation policy', 'wr-bookings').': '.$cancel_policy."\n";
            }

            return;
        }

        // HTML email.
        ?>
        <div style="margin:30px 0 0;border-top:2px solid #0c1218;padding-top:24px;">
          <h2 style="font-size:18px;font-weight:700;color:#0c1218;margin:0 0 16px;">
            <?php esc_html_e('Your tour booking details', 'wr-bookings'); ?>
          </h2>
          <?php foreach ($tickets as $t) { ?>
          <table style="width:100%;border-collapse:collapse;margin-bottom:18px;background:#07111c;color:#f7f1e3;border-radius:4px;overflow:hidden;">
            <tr>
              <td style="padding:14px 18px;font-size:11px;text-transform:uppercase;letter-spacing:.12em;color:#e0be72;border-bottom:1px solid rgba(224,177,90,.2);" colspan="2">
                <?php esc_html_e('Booking confirmed', 'wr-bookings'); ?>
              </td>
            </tr>
            <tr>
              <td style="padding:12px 18px;font-size:15px;font-weight:700;color:#f7f1e3;" colspan="2">
                <?php echo esc_html($t['tour']); ?>
              </td>
            </tr>
            <?php if ($t['date']) { ?>
            <tr>
              <td style="padding:6px 18px;color:#d5ccbb;font-size:13px;"><?php esc_html_e('Date & Time', 'wr-bookings'); ?></td>
              <td style="padding:6px 18px;font-size:13px;font-weight:600;">
                <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($t['date'])).($t['time'] ? ' @ '.$t['time'] : '')); ?>
              </td>
            </tr>
            <?php } ?>
            <?php
            $party = [];
              if ($t['adults']) {
                  $party[] = $t['adults'].' '._n('adult', 'adults', $t['adults'], 'wr-bookings');
              }
              if ($t['children']) {
                  $party[] = $t['children'].' '._n('child', 'children', $t['children'], 'wr-bookings');
              }
              if ($t['seniors']) {
                  $party[] = $t['seniors'].' '._n('senior', 'seniors', $t['seniors'], 'wr-bookings');
              }
              if ($party) { ?>
            <tr>
              <td style="padding:6px 18px;color:#d5ccbb;font-size:13px;"><?php esc_html_e('Party', 'wr-bookings'); ?></td>
              <td style="padding:6px 18px;font-size:13px;font-weight:600;"><?php echo esc_html(implode(', ', $party)); ?></td>
            </tr>
            <?php } ?>
            <?php if ($t['meeting']) { ?>
            <tr>
              <td style="padding:6px 18px;color:#d5ccbb;font-size:13px;"><?php esc_html_e('Meeting point', 'wr-bookings'); ?></td>
              <td style="padding:6px 18px;font-size:13px;"><?php echo esc_html($t['meeting']); ?></td>
            </tr>
            <?php } ?>
            <?php if ($t['balance'] > 0) { ?>
            <tr>
              <td style="padding:6px 18px;color:#d5ccbb;font-size:13px;"><?php esc_html_e('Balance due at tour', 'wr-bookings'); ?></td>
              <td style="padding:6px 18px;font-size:13px;font-weight:600;color:#f0d9a0;"><?php echo wc_price($t['balance']); ?></td>
            </tr>
            <?php } ?>
            <?php if ($t['special']) { ?>
            <tr>
              <td style="padding:6px 18px;color:#d5ccbb;font-size:13px;"><?php esc_html_e('Special requests', 'wr-bookings'); ?></td>
              <td style="padding:6px 18px;font-size:13px;"><?php echo esc_html($t['special']); ?></td>
            </tr>
            <?php } ?>
            <tr>
              <td colspan="2" style="padding:12px 18px;border-top:1px solid rgba(224,177,90,.2);">
                <span style="font-family:monospace;font-size:16px;font-weight:700;color:#e0be72;letter-spacing:.08em;">
                  <?php echo esc_html($t['ticket']); ?>
                </span>
                <div style="font-size:11px;color:#a39886;margin-top:3px;">
                  <?php esc_html_e('Present this ticket number at the meeting point.', 'wr-bookings'); ?>
                </div>
              </td>
            </tr>
          </table>
          <?php } ?>

          <?php if ($confirm_note) { ?>
          <p style="font-size:13px;color:#444;margin:12px 0;"><?php echo esc_html($confirm_note); ?></p>
          <?php } ?>

          <?php if ($cancel_policy) { ?>
          <p style="font-size:12px;color:#666;margin:8px 0;">
            <strong><?php esc_html_e('Cancellation policy:', 'wr-bookings'); ?></strong>
            <?php echo esc_html($cancel_policy); ?>
          </p>
          <?php } ?>
        </div>
        <?php
    }
}
