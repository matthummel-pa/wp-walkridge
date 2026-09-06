<?php

use Automattic\WooCommerce\Utilities\FeaturesUtil;

/**
 * Plugin Name:     Walkridge Bookings
 * Plugin URI:      https://github.com/matthummel-pa/wp-walkridge
 * Description:     Advanced tour-booking layer for WooCommerce. Ships a built-in native engine (date/time slots, party-size pricing, seat gating, multi-step widget, admin screen) and includes compatibility bridges for WooCommerce Bookings, YITH WC Booking, Tyche/BKAP, and Amelia — so it co-exists with any booking system already on the site.
 * Version:         1.0.0
 * Author:          Ridges & Valleys Studio
 * License:         GPLv2 or later
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:     wr-bookings
 * Domain Path:     /languages
 * Requires PHP:    8.1
 * Requires at least: 6.4
 * WC requires at least: 7.0
 * WC tested up to:    11.0
 */
defined('ABSPATH') || exit;

/* ── Constants ──────────────────────────────────────────────────────────── */
define('WRB_VERSION', '1.0.0');
define('WRB_FILE', __FILE__);
define('WRB_DIR', plugin_dir_path(__FILE__));
define('WRB_URL', plugin_dir_url(__FILE__));

/* ── HPOS compatibility declaration ─────────────────────────────────────── */
add_action('before_woocommerce_init', function () {
    if (class_exists(FeaturesUtil::class)) {
        FeaturesUtil::declare_compatibility(
            'custom_order_tables', WRB_FILE, true
        );
        FeaturesUtil::declare_compatibility(
            'cart_checkout_blocks', WRB_FILE, true
        );
    }
});

/* ── Autoload includes ───────────────────────────────────────────────────── */
$wrb_includes = [
    'class-wrb-db',
    'class-wrb-engine-interface',
    'class-wrb-native-engine',
    'class-wrb-compat',
    'class-wrb-product-type',
    'class-wrb-product-meta',
    'class-wrb-cart',
    'class-wrb-checkout',
    'class-wrb-rest-api',
    'class-wrb-admin-settings',
    'class-wrb-admin-bookings',
    'class-wrb-emails',
];
foreach ($wrb_includes as $f) {
    require_once WRB_DIR.'includes/'.$f.'.php';
}

/* ── Plugin singleton ────────────────────────────────────────────────────── */
final class WRB_Plugin
{
    private static ?WRB_Plugin $instance = null;

    private WRB_Engine_Interface $engine;

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /** Return the active booking engine (native or external bridge). */
    public function engine(): WRB_Engine_Interface
    {
        return $this->engine;
    }

    private function __construct()
    {
        register_activation_hook(WRB_FILE, [$this, 'activate']);
        register_deactivation_hook(WRB_FILE, [$this, 'deactivate']);
        add_action('plugins_loaded', [$this, 'boot'], 15);
    }

    public function activate(): void
    {
        WRB_DB::create_tables();
        flush_rewrite_rules();
    }

    public function deactivate(): void
    {
        flush_rewrite_rules();
    }

    public function boot(): void
    {
        if (! class_exists('WooCommerce')) {
            add_action('admin_notices', function () {
                printf(
                    '<div class="error"><p>%s</p></div>',
                    esc_html__('Walkridge Bookings requires WooCommerce.', 'wr-bookings')
                );
            });

            return;
        }

        load_plugin_textdomain('wr-bookings', false, dirname(plugin_basename(WRB_FILE)).'/languages');

        /* Resolve active booking engine */
        $this->engine = WRB_Compat::resolve_engine();

        /* Boot subsystems */
        WRB_Product_Type::instance();
        WRB_Product_Meta::instance();
        WRB_Cart::instance();
        WRB_Checkout::instance();
        WRB_REST_API::instance();
        WRB_Admin_Settings::instance();
        WRB_Admin_Bookings::instance();
        WRB_Emails::instance();

        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin']);

        // Inject booking widget on single product pages.
        add_action('woocommerce_single_product_summary', [$this, 'inject_booking_widget'], 35);
        // CSS-hide the native add-to-cart form when the widget is present —
        // the widget's AJAX add-to-cart action handles the cart instead.
        add_action('wp_head', [$this, 'hide_native_atc_css']);

        /**
         * Fired after the plugin is fully booted.
         *
         * @param  WRB_Engine_Interface  $engine  The resolved booking engine.
         */
        do_action('wrb_booted', $this->engine);
    }

    /* ── Asset registration ────────────────────────────────────────────── */

    public function inject_booking_widget(): void
    {
        if (! is_singular('product')) {
            return;
        }
        $product_id = (int) get_the_ID();
        if (! WRB_Product_Meta::is_bookable($product_id)) {
            return;
        }
        $product = wc_get_product($product_id);
        include WRB_DIR.'templates/single-product-booking.php';
    }

    /** Inline CSS: hide the default WC add-to-cart form on bookable product pages. */
    public function hide_native_atc_css(): void
    {
        if (! is_singular('product')) {
            return;
        }
        $product_id = (int) get_the_ID();
        if (! WRB_Product_Meta::is_bookable($product_id)) {
            return;
        }
        echo '<style>.single-product .summary form.cart,.single-product .summary .quantity{display:none!important;}</style>'."\n";
    }

    public function enqueue_frontend(): void
    {
        if (! is_singular('product') && ! is_cart() && ! is_checkout()) {
            return;
        }
        $this->register_assets();
        wp_enqueue_style('hgb-booking');
        wp_enqueue_script('hgb-booking');
        wp_localize_script('hgb-booking', 'hgbConfig', $this->js_config());
    }

    public function enqueue_admin(string $hook): void
    {
        $screens = ['post.php', 'post-new.php', 'toplevel_page_wr-bookings', 'woocommerce_page_wr-bookings'];
        if (! in_array($hook, $screens, true)) {
            return;
        }
        $this->register_assets();
        wp_enqueue_style('hgb-booking');
        wp_enqueue_script('hgb-booking');
        wp_localize_script('hgb-booking', 'hgbConfig', array_merge($this->js_config(), ['isAdmin' => true]));
    }

    private function register_assets(): void
    {
        static $registered = false;
        if ($registered) {
            return;
        }
        $registered = true;

        wp_register_style(
            'hgb-booking',
            WRB_URL.'assets/booking.css',
            [],
            WRB_VERSION
        );
        wp_register_script(
            'hgb-booking',
            WRB_URL.'assets/booking.js',
            [],
            WRB_VERSION,
            true
        );
    }

    private function js_config(): array
    {
        return [
            'root' => esc_url_raw(rest_url('wr-bookings/v1/')),
            'nonce' => wp_create_nonce('wp_rest'),
            'ajaxUrl' => esc_url_raw(admin_url('admin-ajax.php')),
            'ajaxNonce' => wp_create_nonce('wrb_ajax'),
            'cartUrl' => function_exists('wc_get_cart_url') ? wc_get_cart_url() : '',
            'currency' => function_exists('get_woocommerce_currency_symbol') ? get_woocommerce_currency_symbol() : '$',
            'engine' => $this->engine->engine_id(),
            'isAdmin' => false,
            'i18n' => [
                'selectDate' => __('Choose a departure date to continue.', 'wr-bookings'),
                'selectParty' => __('Add at least one guest to continue.', 'wr-bookings'),
                'capacityError' => __('Not enough spots for this group — try another date.', 'wr-bookings'),
                'soldOut' => __('Sold out', 'wr-bookings'),
                'fewLeft' => __('Few spots left', 'wr-bookings'),
                'available' => __('Available', 'wr-bookings'),
                'noSlots' => __('No departures scheduled this month.', 'wr-bookings'),
                'adults' => __('Adults', 'wr-bookings'),
                'children' => __('Children (6–12)', 'wr-bookings'),
                'seniors' => __('Seniors (65+)', 'wr-bookings'),
                'added' => __('Booking added to cart.', 'wr-bookings'),
                'confirmDelete' => __('Delete this slot? This cannot be undone.', 'wr-bookings'),
            ],
        ];
    }
}

/* Boot */
WRB_Plugin::instance();

/**
 * Global helper — returns the active booking engine.
 */
function wrb_engine(): WRB_Engine_Interface
{
    return WRB_Plugin::instance()->engine();
}
