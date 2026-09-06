<?php
defined('ABSPATH') || exit;

/**
 * Plugin settings page — WooCommerce → Tour Bookings → Settings.
 */
final class WRB_Admin_Settings
{
    private static ?WRB_Admin_Settings $instance = null;

    private const PAGE_SLUG = 'wr-bookings';

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
        add_action('admin_menu', [$this, 'register_menu']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function register_menu(): void
    {
        add_submenu_page(
            'woocommerce',
            __('Tour Bookings', 'wr-bookings'),
            __('Tour Bookings', 'wr-bookings'),
            'manage_woocommerce',
            self::PAGE_SLUG,
            [$this, 'render_settings_page']
        );
    }

    public function register_settings(): void
    {
        register_setting('wrb_settings', 'wrb_engine_override', [
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ]);
        register_setting('wrb_settings', 'wrb_cancellation_policy', [
            'sanitize_callback' => 'sanitize_textarea_field',
            'default' => 'Cancel up to 24 hours before your tour for a full refund. Cancellations inside 24 hours receive a credit.',
        ]);
        register_setting('wrb_settings', 'wrb_confirmation_note', [
            'sanitize_callback' => 'sanitize_textarea_field',
            'default' => 'Please arrive 15 minutes early at the meeting point with your ticket number.',
        ]);
    }

    public function render_settings_page(): void
    {
        $tab = sanitize_key($_GET['tab'] ?? 'settings');
        $tabs = [
            'settings' => __('Settings', 'wr-bookings'),
            'bookings' => __('All Bookings', 'wr-bookings'),
            'about' => __('Compatibility', 'wr-bookings'),
        ];
        ?>
        <div class="wrap">
          <h1><?php esc_html_e('Walkridge — Tour Bookings', 'wr-bookings'); ?></h1>

          <nav class="nav-tab-wrapper">
            <?php foreach ($tabs as $slug => $label) { ?>
              <a href="<?php echo esc_url(admin_url('admin.php?page='.self::PAGE_SLUG.'&tab='.$slug)); ?>"
                 class="nav-tab<?php echo $tab === $slug ? ' nav-tab-active' : ''; ?>">
                <?php echo esc_html($label); ?>
              </a>
            <?php } ?>
          </nav>
          <div class="tab-content" style="margin-top:20px;">
          <?php
          match ($tab) {
              'bookings' => $this->render_bookings_tab(),
              'about' => $this->render_compat_tab(),
              default => $this->render_settings_tab(),
          };
        ?>
          </div>
        </div>
        <?php
    }

    private function render_settings_tab(): void
    {
        ?>
        <form method="post" action="options.php">
          <?php settings_fields('wrb_settings'); ?>
          <table class="form-table" role="presentation">
            <tr>
              <th scope="row"><label for="wrb_engine_override"><?php esc_html_e('Booking engine', 'wr-bookings'); ?></label></th>
              <td>
                <select id="wrb_engine_override" name="wrb_engine_override">
                  <option value=""><?php esc_html_e('— Auto-detect —', 'wr-bookings'); ?></option>
                  <?php
                  $current = get_option('wrb_engine_override', '');
        foreach (WRB_Compat::available_engines() as $id => $label) {
            printf(
                '<option value="%s"%s>%s</option>',
                esc_attr($id),
                selected($current, $id, false),
                esc_html($label)
            );
        }
        ?>
                </select>
                <p class="description">
                  <?php esc_html_e('Auto-detect picks the first available engine in priority order. Force "Native" unless you specifically want to delegate to an external booking plugin.', 'wr-bookings'); ?>
                </p>
                <p class="description">
                  <?php printf(
                      /* translators: engine label */
                      esc_html__('Currently active: %s', 'wr-bookings'),
                      '<strong>'.esc_html(wrb_engine()->engine_label()).'</strong>'
                  ); ?>
                </p>
              </td>
            </tr>
            <tr>
              <th scope="row"><label for="wrb_cancellation_policy"><?php esc_html_e('Cancellation policy', 'wr-bookings'); ?></label></th>
              <td>
                <textarea id="wrb_cancellation_policy" name="wrb_cancellation_policy" rows="3" class="large-text"><?php echo esc_textarea(get_option('wrb_cancellation_policy')); ?></textarea>
                <p class="description"><?php esc_html_e('Shown in the booking widget and confirmation emails.', 'wr-bookings'); ?></p>
              </td>
            </tr>
            <tr>
              <th scope="row"><label for="wrb_confirmation_note"><?php esc_html_e('Confirmation note', 'wr-bookings'); ?></label></th>
              <td>
                <textarea id="wrb_confirmation_note" name="wrb_confirmation_note" rows="3" class="large-text"><?php echo esc_textarea(get_option('wrb_confirmation_note')); ?></textarea>
                <p class="description"><?php esc_html_e('Shown on the Thank You page and in booking confirmation emails.', 'wr-bookings'); ?></p>
              </td>
            </tr>
          </table>
          <?php submit_button(); ?>
        </form>
        <?php
    }

    private function render_bookings_tab(): void
    {
        WRB_Admin_Bookings::instance()->render_page();
    }

    private function render_compat_tab(): void
    {
        $engines = WRB_Compat::available_engines();
        ?>
        <h2><?php esc_html_e('Detected booking plugins', 'wr-bookings'); ?></h2>
        <p><?php esc_html_e('The following booking engines are available on this site. Use the Settings tab to pin a specific engine.', 'wr-bookings'); ?></p>
        <table class="widefat striped" style="max-width:640px;">
          <thead><tr>
            <th><?php esc_html_e('Engine', 'wr-bookings'); ?></th>
            <th><?php esc_html_e('Status', 'wr-bookings'); ?></th>
            <th><?php esc_html_e('Notes', 'wr-bookings'); ?></th>
          </tr></thead>
          <tbody>
          <?php
          $current = wrb_engine()->engine_id();
        $notes = [
            'native' => __('Built-in slots + bookings tables. No external plugin required.', 'wr-bookings'),
            'wc-bookings' => __('Official WooCommerce Bookings plugin by Automattic.', 'wr-bookings'),
            'yith' => __('YITH WooCommerce Booking plugin.', 'wr-bookings'),
            'bkap' => __('Booking & Appointment Plugin by Tyche Softwares.', 'wr-bookings'),
            'amelia' => __('Amelia — enterprise booking & scheduling.', 'wr-bookings'),
        ];
        foreach ($engines as $id => $label) {
            $active = $id === $current;
            ?>
          <tr>
            <td><strong><?php echo esc_html($label); ?></strong></td>
            <td>
              <?php if ($active) { ?>
                <span style="color:#46b450;font-weight:600;">&#10003; <?php esc_html_e('Active', 'wr-bookings'); ?></span>
              <?php } else { ?>
                <?php esc_html_e('Available', 'wr-bookings'); ?>
              <?php } ?>
            </td>
            <td><?php echo esc_html($notes[$id] ?? '—'); ?></td>
          </tr>
          <?php } ?>
          </tbody>
        </table>

        <h2 style="margin-top:30px;"><?php esc_html_e('Payment gateway compatibility', 'wr-bookings'); ?></h2>
        <p><?php esc_html_e('The booking widget routes all payments through WooCommerce checkout, so every active payment gateway works automatically.', 'wr-bookings'); ?></p>
        <?php
        $gateways = WC()->payment_gateways ? WC()->payment_gateways->get_available_payment_gateways() : [];
        if ($gateways) { ?>
        <table class="widefat striped" style="max-width:640px;">
          <thead><tr>
            <th><?php esc_html_e('Gateway', 'wr-bookings'); ?></th>
            <th><?php esc_html_e('ID', 'wr-bookings'); ?></th>
          </tr></thead>
          <tbody>
          <?php foreach ($gateways as $gw) { ?>
          <tr>
            <td><strong><?php echo esc_html($gw->get_title()); ?></strong></td>
            <td><code><?php echo esc_html($gw->id); ?></code></td>
          </tr>
          <?php } ?>
          </tbody>
        </table>
        <?php } else { ?>
        <p><?php esc_html_e('No payment gateways are currently enabled. Enable at least one in WooCommerce → Settings → Payments.', 'wr-bookings'); ?></p>
        <?php } ?>
        <?php
    }
}
