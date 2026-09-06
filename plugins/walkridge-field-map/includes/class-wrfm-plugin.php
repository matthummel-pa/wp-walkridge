<?php

defined('ABSPATH') || exit;

/**
 * Main plugin class — shortcode, asset enqueueing, and glue.
 */
final class WRFM_Plugin
{
    private static ?WRFM_Plugin $instance = null;

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
        add_action('init', [$this, 'register_shortcode']);
        add_action('rest_api_init', [WRFM_REST::class, 'register_routes']);
        add_action('wp_enqueue_scripts', [$this, 'maybe_enqueue_assets']);
        add_action('admin_menu', [WRFM_Admin::class, 'register_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }

    public function register_shortcode(): void
    {
        add_shortcode('wr_field_map', [$this, 'render_shortcode']);
    }

    /** [wr_field_map] shortcode — renders the full interactive map widget. */
    public function render_shortcode(array $atts): string
    {
        $atts = shortcode_atts([
            'height' => '620px',
            'class' => '',
        ], $atts, 'wr_field_map');

        $this->enqueue_assets();
        ob_start();
        include WRFM_DIR.'templates/map-section.php';

        return ob_get_clean();
    }

    /** Enqueue assets whenever the shortcode might be present. */
    public function maybe_enqueue_assets(): void
    {
        // Enqueue proactively on pages that use the page-area template.
        if (is_page('area') || is_singular() && has_shortcode(get_post()->post_content ?? '', 'wr_field_map')) {
            $this->enqueue_assets();
        }
    }

    public function enqueue_assets(): void
    {
        static $enqueued = false;

        if ($enqueued) {
            return;
        }

        $enqueued = true;

        // OpenLayers 10 + CSS from jsDelivr CDN (no API key required).
        wp_enqueue_style(
            'openlayers',
            'https://cdn.jsdelivr.net/npm/ol@10.6.1/ol.css',
            [],
            '10.6.1'
        );
        wp_enqueue_script(
            'openlayers',
            'https://cdn.jsdelivr.net/npm/ol@10.6.1/dist/ol.js',
            [],
            '10.6.1',
            true
        );

        // jsPDF for Save-as-PDF from CDN.
        wp_enqueue_script(
            'jspdf',
            'https://cdn.jsdelivr.net/npm/jspdf@2.5.2/dist/jspdf.umd.min.js',
            [],
            '2.5.2',
            true
        );

        // Plugin CSS.
        wp_enqueue_style(
            'hgfm-field-map',
            WRFM_URL.'assets/field-map.css',
            ['openlayers'],
            WRFM_VERSION
        );

        // Plugin JS — depends on OL and jsPDF being loaded first.
        wp_enqueue_script(
            'hgfm-field-map',
            WRFM_URL.'assets/field-map.js',
            ['openlayers', 'jspdf'],
            WRFM_VERSION,
            true
        );

        $shop_url = function_exists('wc_get_page_permalink')
            ? wc_get_page_permalink('shop')
            : home_url('/shop');

        wp_localize_script('hgfm-field-map', 'hgfmConfig', [
            'version' => WRFM_VERSION,
            'root' => esc_url_raw(rest_url('wr-field-map/v1/')),
            'nonce' => wp_create_nonce('wp_rest'),
            'isAdmin' => current_user_can('manage_options'),
            'shopUrl' => esc_url($shop_url),
            'toursUrl' => esc_url(home_url('/tours')),
            'guidesUrl' => esc_url(home_url('/guides')),
            'areaUrl' => esc_url(home_url('/area')),
            'endpoints' => [
                'monuments' => esc_url_raw(rest_url('wr-field-map/v1/monuments')),
                'places' => esc_url_raw(rest_url('wr-field-map/v1/places')),
                'mapsConfig' => esc_url_raw(rest_url('wr-field-map/v1/maps-config')),
                'savePlaces' => esc_url_raw(rest_url('wr-field-map/v1/places')),
                'saveMaps' => esc_url_raw(rest_url('wr-field-map/v1/maps-config')),
            ],
        ]);
    }

    public function enqueue_admin_assets(string $hook): void
    {
        if ($hook !== 'toplevel_page_wr-field-map') {
            return;
        }

        $this->enqueue_assets();
        wp_localize_script('hgfm-field-map', 'hgfmAdmin', ['active' => true]);
    }
}
