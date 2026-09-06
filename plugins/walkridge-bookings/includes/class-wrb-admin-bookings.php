<?php
defined('ABSPATH') || exit;

/**
 * Admin bookings list — filterable, searchable table of all bookings.
 * Rendered on the WooCommerce → Tour Bookings → All Bookings tab.
 */
final class WRB_Admin_Bookings
{
    private static ?WRB_Admin_Bookings $instance = null;

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    public function render_page(): void
    {
        $args = $this->parse_filters();
        $total = WRB_DB::instance()->count_bookings($args);
        $per = 25;
        $page = max(1, (int) ($_GET['paged'] ?? 1));
        $args['limit'] = $per;
        $args['offset'] = ($page - 1) * $per;
        $rows = WRB_DB::instance()->get_bookings($args);
        $pages = max(1, (int) ceil($total / $per));
        ?>
        <div class="hgb-admin-bookings">
        <?php $this->render_filter_bar($args); ?>
        <p style="color:#666;font-size:13px;">
          <?php printf(esc_html__('%d booking(s) found.', 'wr-bookings'), $total); ?>
        </p>
        <table class="wp-list-table widefat fixed striped">
          <thead>
            <tr>
              <th><?php esc_html_e('Ticket', 'wr-bookings'); ?></th>
              <th><?php esc_html_e('Tour', 'wr-bookings'); ?></th>
              <th><?php esc_html_e('Date', 'wr-bookings'); ?></th>
              <th><?php esc_html_e('Time', 'wr-bookings'); ?></th>
              <th><?php esc_html_e('Guest', 'wr-bookings'); ?></th>
              <th><?php esc_html_e('Party', 'wr-bookings'); ?></th>
              <th><?php esc_html_e('Status', 'wr-bookings'); ?></th>
              <th><?php esc_html_e('Order', 'wr-bookings'); ?></th>
            </tr>
          </thead>
          <tbody>
          <?php if ($rows) { ?>
            <?php foreach ($rows as $b) { ?>
            <tr>
              <td><code><?php echo esc_html($b->ticket_number); ?></code></td>
              <td><?php echo esc_html($b->tour_name ?? '—'); ?></td>
              <td><?php echo esc_html($b->slot_date ? date_i18n(get_option('date_format'), strtotime($b->slot_date)) : '—'); ?></td>
              <td><?php echo esc_html($b->slot_time ? substr($b->slot_time, 0, 5) : '—'); ?></td>
              <td>
                <?php echo esc_html($b->customer_name); ?><br>
                <small><a href="mailto:<?php echo esc_attr($b->customer_email); ?>"><?php echo esc_html($b->customer_email); ?></a></small>
              </td>
              <td>
                <?php
                $parts = [];
                if ((int) $b->adults) {
                    $parts[] = $b->adults.' '._n('adult', 'adults', (int) $b->adults, 'wr-bookings');
                }
                if ((int) $b->children) {
                    $parts[] = $b->children.' '._n('child', 'children', (int) $b->children, 'wr-bookings');
                }
                if ((int) $b->seniors) {
                    $parts[] = $b->seniors.' '._n('senior', 'seniors', (int) $b->seniors, 'wr-bookings');
                }
                echo esc_html(implode(', ', $parts) ?: '—');
                ?>
              </td>
              <td><span class="hgb-status hgb-status--<?php echo esc_attr($b->status); ?>"><?php echo esc_html($b->status); ?></span></td>
              <td>
                <?php if ($b->order_id) { ?>
                <a href="<?php echo esc_url(admin_url('post.php?post='.$b->order_id.'&action=edit')); ?>">
                  #<?php echo (int) $b->order_id; ?>
                </a>
                <?php } else { ?>—<?php } ?>
              </td>
            </tr>
            <?php } ?>
          <?php } else { ?>
            <tr><td colspan="8" style="text-align:center;padding:24px;">
              <?php esc_html_e('No bookings found.', 'wr-bookings'); ?>
            </td></tr>
          <?php } ?>
          </tbody>
        </table>
        <?php $this->render_pagination($page, $pages, $args); ?>
        </div>

        <style>
        .hgb-status { display:inline-block; padding:2px 8px; border-radius:4px; font-size:12px; font-weight:600; text-transform:capitalize; }
        .hgb-status--confirmed { background:#d7f0d9; color:#1d6b21; }
        .hgb-status--pending   { background:#fff3cd; color:#856404; }
        .hgb-status--cancelled { background:#f8d7da; color:#721c24; }
        .hgb-status--failed    { background:#f8d7da; color:#721c24; }
        .hgb-admin-bookings { margin-top:12px; }
        </style>
        <?php
    }

    private function parse_filters(): array
    {
        $args = [];
        if (! empty($_GET['wrb_status'])) {
            $args['status'] = sanitize_key($_GET['wrb_status']);
        }
        if (! empty($_GET['wrb_search'])) {
            $args['search'] = sanitize_text_field($_GET['wrb_search']);
        }
        if (! empty($_GET['wrb_product'])) {
            $args['product_id'] = (int) $_GET['wrb_product'];
        }

        return $args;
    }

    private function render_filter_bar(array $args): void
    {
        $base = admin_url('admin.php?page=wr-bookings&tab=bookings');
        ?>
        <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;margin-bottom:12px;">
          <form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>" style="display:contents;">
            <input type="hidden" name="page" value="wr-bookings">
            <input type="hidden" name="tab"  value="bookings">
            <input type="search" name="wrb_search" placeholder="<?php esc_attr_e('Name, email, ticket…', 'wr-bookings'); ?>"
              value="<?php echo esc_attr($args['search'] ?? ''); ?>" style="width:220px;">
            <select name="wrb_status">
              <option value=""><?php esc_html_e('All statuses', 'wr-bookings'); ?></option>
              <?php foreach (['pending', 'confirmed', 'cancelled', 'failed'] as $s) { ?>
              <option value="<?php echo esc_attr($s); ?>"<?php selected($args['status'] ?? '', $s); ?>><?php echo esc_html(ucfirst($s)); ?></option>
              <?php } ?>
            </select>
            <button type="submit" class="button"><?php esc_html_e('Filter', 'wr-bookings'); ?></button>
            <?php if (! empty($args)) { ?>
            <a href="<?php echo esc_url($base); ?>" class="button button-link"><?php esc_html_e('Clear', 'wr-bookings'); ?></a>
            <?php } ?>
          </form>
        </div>
        <?php
    }

    private function render_pagination(int $page, int $pages, array $args): void
    {
        if ($pages <= 1) {
            return;
        }
        $base = add_query_arg(array_filter([
            'page' => 'wr-bookings',
            'tab' => 'bookings',
            'wrb_status' => $args['status'] ?? '',
            'wrb_search' => $args['search'] ?? '',
            'wrb_product' => $args['product_id'] ?? '',
        ]), admin_url('admin.php'));
        echo '<div class="tablenav"><div class="tablenav-pages">';
        for ($i = 1; $i <= $pages; $i++) {
            if ($i === $page) {
                echo '<span class="current" style="padding:4px 8px;background:#2271b1;color:#fff;">'.$i.'</span> ';
            } else {
                echo '<a href="'.esc_url(add_query_arg('paged', $i, $base)).'" class="page-numbers" style="padding:4px 8px;">'.$i.'</a> ';
            }
        }
        echo '</div></div>';
    }
}
