<?php
defined('ABSPATH') || exit;

/**
 * Product meta box — "Tour Booking" panel on the WooCommerce product edit screen.
 *
 * Settings stored:
 *   _wrb_enabled         1|0   — activate booking widget on this product
 *   _wrb_duration        string — e.g. "2 hours"
 *   _wrb_meeting_point   string — where guests meet
 *   _wrb_max_group       int   — max guests per slot (UI hint, capacity enforced per-slot)
 *   _wrb_child_price     float — price per child (default: 0 = use adult price)
 *   _wrb_senior_price    float — price per senior (default: 0 = use adult price)
 *   _wrb_deposit_type    none|percent|fixed
 *   _wrb_deposit_value   float
 */
final class WRB_Product_Meta
{
    private static ?WRB_Product_Meta $instance = null;

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
        add_action('woocommerce_product_data_panels', [$this, 'render_panel']);
        add_action('woocommerce_process_product_meta', [$this, 'save']);
        add_action('wp_ajax_wrb_add_slot', [$this, 'ajax_add_slot']);
        add_action('wp_ajax_wrb_delete_slot', [$this, 'ajax_delete_slot']);
        add_action('wp_ajax_wrb_get_slots', [$this, 'ajax_get_slots']);
    }

    /* ── Admin panel ─────────────────────────────────────────────────────── */

    public function render_panel(): void
    {
        global $post;
        $product_id = (int) $post->ID;
        $p = fn (string $key, $default = '') => get_post_meta($product_id, $key, true) ?: $default;
        ?>
        <div id="wrb_booking_data" class="panel woocommerce_options_panel">
          <div class="options_group">
            <?php woocommerce_wp_checkbox([
                'id' => '_wrb_enabled',
                'label' => __('Enable tour booking widget', 'wr-bookings'),
                'desc_tip' => true,
                'description' => __('Replaces the standard Add to Cart button with the multi-step booking flow.', 'wr-bookings'),
            ]); ?>
            <?php woocommerce_wp_text_input([
                'id' => '_wrb_duration',
                'label' => __('Duration', 'wr-bookings'),
                'placeholder' => '2 hours',
                'desc_tip' => true,
                'description' => __('Shown in the booking widget header (e.g. "2 hours", "90 minutes").', 'wr-bookings'),
            ]); ?>
            <?php woocommerce_wp_text_input([
                'id' => '_wrb_meeting_point',
                'label' => __('Meeting point', 'wr-bookings'),
                'placeholder' => '100 Sample Street, Gettysburg',
                'desc_tip' => true,
                'description' => __('Shown in booking confirmation and emails.', 'wr-bookings'),
            ]); ?>
            <?php woocommerce_wp_text_input([
                'id' => '_wrb_max_group',
                'label' => __('Default group size cap', 'wr-bookings'),
                'type' => 'number',
                'custom_attributes' => ['min' => 1, 'step' => 1],
                'desc_tip' => true,
                'description' => __('Pre-fills the capacity field when adding new slots. Each slot can have its own capacity.', 'wr-bookings'),
            ]); ?>
          </div>

          <div class="options_group">
            <p class="form-field"><strong><?php esc_html_e('Tiered pricing', 'wr-bookings'); ?></strong></p>
            <p style="margin:0 0 8px 162px;color:#666;font-size:12px;">
              <?php esc_html_e('Leave a tier at 0.00 to charge the product\'s regular price for that tier.', 'wr-bookings'); ?>
            </p>
            <?php woocommerce_wp_text_input([
                'id' => '_wrb_child_price',
                'label' => __('Child price (6–12)', 'wr-bookings'),
                'type' => 'number',
                'custom_attributes' => ['min' => 0, 'step' => 0.01],
                'data_type' => 'price',
                'desc_tip' => true,
                'description' => __('Per-child price. 0 = same as adult price.', 'wr-bookings'),
            ]); ?>
            <?php woocommerce_wp_text_input([
                'id' => '_wrb_senior_price',
                'label' => __('Senior price (65+)', 'wr-bookings'),
                'type' => 'number',
                'custom_attributes' => ['min' => 0, 'step' => 0.01],
                'data_type' => 'price',
                'desc_tip' => true,
                'description' => __('Per-senior price. 0 = same as adult price.', 'wr-bookings'),
            ]); ?>
          </div>

          <div class="options_group">
            <p class="form-field"><strong><?php esc_html_e('Deposit / partial payment', 'wr-bookings'); ?></strong></p>
            <p class="form-field">
              <label for="_wrb_deposit_type"><?php esc_html_e('Deposit type', 'wr-bookings'); ?></label>
              <select id="_wrb_deposit_type" name="_wrb_deposit_type" class="short">
                <?php
                $deposit_type = $p('_wrb_deposit_type', 'none');
        foreach ([
            'none' => __('None — full payment at checkout', 'wr-bookings'),
            'percent' => __('Percentage of booking total', 'wr-bookings'),
            'fixed' => __('Fixed amount per booking', 'wr-bookings'),
        ] as $val => $label) {
            printf(
                '<option value="%s"%s>%s</option>',
                esc_attr($val),
                selected($deposit_type, $val, false),
                esc_html($label)
            );
        }
        ?>
              </select>
            </p>
            <?php woocommerce_wp_text_input([
                'id' => '_wrb_deposit_value',
                'label' => __('Deposit value', 'wr-bookings'),
                'type' => 'number',
                'custom_attributes' => ['min' => 0, 'step' => 0.01],
                'desc_tip' => true,
                'description' => __('Percent (0–100) or fixed amount charged now. Balance is recorded as order meta.', 'wr-bookings'),
            ]); ?>
          </div>

          <?php if (wrb_engine()->supports_slot_management()) { ?>
          <div class="options_group">
            <p class="form-field"><strong><?php esc_html_e('Departure slots', 'wr-bookings'); ?></strong></p>
            <p style="margin:0 0 12px 162px;color:#666;font-size:12px;">
              <?php esc_html_e('Add specific departure dates and times. Each slot tracks its own availability.', 'wr-bookings'); ?>
            </p>
            <div class="hgb-slot-manager" data-product-id="<?php echo esc_attr($product_id); ?>">
              <div class="hgb-add-slot-form" style="margin:0 0 14px 162px; display:flex; gap:8px; flex-wrap:wrap; align-items:flex-end;">
                <label style="display:flex;flex-direction:column;gap:4px;font-size:12px;">
                  <?php esc_html_e('Date', 'wr-bookings'); ?>
                  <input type="date" id="wrb_new_date" style="width:140px;">
                </label>
                <label style="display:flex;flex-direction:column;gap:4px;font-size:12px;">
                  <?php esc_html_e('Time', 'wr-bookings'); ?>
                  <input type="time" id="wrb_new_time" value="09:00" style="width:100px;">
                </label>
                <label style="display:flex;flex-direction:column;gap:4px;font-size:12px;">
                  <?php esc_html_e('Capacity', 'wr-bookings'); ?>
                  <input type="number" id="wrb_new_capacity" value="<?php echo esc_attr($p('_wrb_max_group', '15')); ?>" min="1" style="width:70px;">
                </label>
                <button type="button" id="wrb_add_slot_btn" class="button button-secondary">
                  <?php esc_html_e('+ Add Slot', 'wr-bookings'); ?>
                </button>
                <span id="wrb_slot_msg" style="font-size:12px;color:#46b450;"></span>
              </div>
              <div id="wrb_slots_list" style="margin-left:162px;">
                <em style="color:#999;font-size:12px;"><?php esc_html_e('Loading slots…', 'wr-bookings'); ?></em>
              </div>
            </div>
          </div>
          <?php } ?>
        </div>
        <?php
    }

    /* ── Save ────────────────────────────────────────────────────────────── */

    public function save(int $post_id): void
    {
        $checkboxes = ['_wrb_enabled'];
        foreach ($checkboxes as $key) {
            update_post_meta($post_id, $key, isset($_POST[$key]) ? 'yes' : 'no');
        }

        $texts = ['_wrb_duration', '_wrb_meeting_point', '_wrb_deposit_type'];
        foreach ($texts as $key) {
            if (isset($_POST[$key])) {
                update_post_meta($post_id, $key, sanitize_text_field(wp_unslash($_POST[$key])));
            }
        }

        $prices = ['_wrb_child_price', '_wrb_senior_price', '_wrb_deposit_value'];
        foreach ($prices as $key) {
            if (isset($_POST[$key])) {
                update_post_meta($post_id, $key, wc_format_decimal(wp_unslash($_POST[$key])));
            }
        }

        $ints = ['_wrb_max_group'];
        foreach ($ints as $key) {
            if (isset($_POST[$key])) {
                update_post_meta($post_id, $key, absint($_POST[$key]));
            }
        }
    }

    /* ── AJAX slot management ────────────────────────────────────────────── */

    public function ajax_add_slot(): void
    {
        check_ajax_referer('wrb_ajax', 'nonce');
        if (! current_user_can('edit_products')) {
            wp_send_json_error('Unauthorized');
        }
        $product_id = (int) ($_POST['product_id'] ?? 0);
        $date = sanitize_text_field($_POST['slot_date'] ?? '');
        $time = sanitize_text_field($_POST['slot_time'] ?? '09:00');
        $capacity = (int) ($_POST['capacity'] ?? 15);

        if (! $product_id || ! $date) {
            wp_send_json_error('Missing data');
        }

        $id = WRB_DB::instance()->create_slot(compact('product_id', 'slot_date', 'slot_time', 'capacity') + [
            'slot_date' => $date,
            'slot_time' => $time.':00',
        ]);

        if (! $id) {
            wp_send_json_error('Could not create slot.');
        }
        wp_send_json_success(['slot_id' => $id, 'message' => __('Slot added.', 'wr-bookings')]);
    }

    public function ajax_delete_slot(): void
    {
        check_ajax_referer('wrb_ajax', 'nonce');
        if (! current_user_can('edit_products')) {
            wp_send_json_error('Unauthorized');
        }
        $slot_id = (int) ($_POST['slot_id'] ?? 0);
        $result = WRB_DB::instance()->delete_slot($slot_id);
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }
        wp_send_json_success(['message' => __('Slot deleted.', 'wr-bookings')]);
    }

    public function ajax_get_slots(): void
    {
        check_ajax_referer('wrb_ajax', 'nonce');
        if (! current_user_can('edit_products')) {
            wp_send_json_error('Unauthorized');
        }
        $product_id = (int) ($_GET['product_id'] ?? 0);
        $slots = WRB_DB::instance()->get_slots_for_product($product_id, '2000-01-01', '2099-12-31', false);
        wp_send_json_success(['slots' => array_map(fn ($s) => [
            'id' => (int) $s->id,
            'date' => $s->slot_date,
            'time' => substr($s->slot_time, 0, 5),
            'capacity' => (int) $s->capacity,
            'booked' => (int) $s->booked,
            'status' => $s->status,
        ], $slots)]);
    }

    /* ── Static helpers used by other classes ────────────────────────────── */

    public static function is_bookable(int $product_id): bool
    {
        return get_post_meta($product_id, '_wrb_enabled', true) === 'yes'
            || get_post_type($product_id) === 'hg-tour';
    }

    public static function get_pricing(int $product_id): array
    {
        $product = wc_get_product($product_id);
        $adult_price = $product ? (float) $product->get_price() : 0.0;
        $child_price = (float) get_post_meta($product_id, '_wrb_child_price', true);
        $senior_price = (float) get_post_meta($product_id, '_wrb_senior_price', true);

        return [
            'adult' => $adult_price,
            'child' => $child_price > 0 ? $child_price : $adult_price,
            'senior' => $senior_price > 0 ? $senior_price : $adult_price,
        ];
    }

    public static function get_deposit(int $product_id): array
    {
        return [
            'type' => get_post_meta($product_id, '_wrb_deposit_type', true) ?: 'none',
            'value' => (float) get_post_meta($product_id, '_wrb_deposit_value', true),
        ];
    }

    /**
     * Calculate deposit amount given a full booking total.
     */
    public static function calc_deposit(int $product_id, float $full_total): float
    {
        $deposit = self::get_deposit($product_id);
        if ($deposit['type'] === 'none' || $deposit['value'] <= 0) {
            return $full_total;
        }
        if ($deposit['type'] === 'percent') {
            return round($full_total * $deposit['value'] / 100, wc_get_price_decimals());
        }

        return min($full_total, round($deposit['value'], wc_get_price_decimals()));
    }
}
