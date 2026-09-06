<?php

defined('ABSPATH') || exit;

/**
 * REST API endpoints for the booking widget.
 *
 * GET /wp-json/wr-bookings/v1/slots
 *   ?product_id=X&month=YYYY-MM
 *   → calendar data: {date: [{id, time, available, status}]}
 *
 * GET /wp-json/wr-bookings/v1/availability
 *   ?slot_id=X
 *   → {available: int}
 *
 * GET /wp-json/wr-bookings/v1/product-config
 *   ?product_id=X
 *   → {pricing, deposit, duration, meeting_point, enabled}
 *
 * GET /wp-json/wr-bookings/v1/payment-methods
 *   → [{id, title, icon_url}]  — active WC payment gateways
 *
 * POST /wp-json/wr-bookings/v1/hold  (future: temporary seat hold)
 */
final class WRB_REST_API
{
    private static ?WRB_REST_API $instance = null;

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
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes(): void
    {
        $ns = 'wr-bookings/v1';

        register_rest_route($ns, '/slots', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_slots'],
            'permission_callback' => '__return_true',
            'args' => [
                'product_id' => ['required' => true,  'type' => 'integer', 'minimum' => 1],
                'month' => ['required' => false, 'type' => 'string',  'pattern' => '^\d{4}-\d{2}$'],
            ],
        ]);

        register_rest_route($ns, '/availability', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_availability'],
            'permission_callback' => '__return_true',
            'args' => [
                'slot_id' => ['required' => true, 'type' => 'integer', 'minimum' => 1],
            ],
        ]);

        register_rest_route($ns, '/product-config', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_product_config'],
            'permission_callback' => '__return_true',
            'args' => [
                'product_id' => ['required' => true, 'type' => 'integer', 'minimum' => 1],
            ],
        ]);

        register_rest_route($ns, '/payment-methods', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_payment_methods'],
            'permission_callback' => '__return_true',
        ]);
    }

    /* ── Handlers ────────────────────────────────────────────────────────── */

    public function get_slots(WP_REST_Request $req): WP_REST_Response
    {
        $product_id = (int) $req['product_id'];
        if (! WRB_Product_Meta::is_bookable($product_id)) {
            return new WP_REST_Response(['slots' => []]);
        }
        $month = $req['month'] ?? current_time('Y-m');
        $slots = wrb_engine()->get_slots_by_month($product_id, $month);

        return new WP_REST_Response([
            'product_id' => $product_id,
            'month' => $month,
            'slots' => $slots,
        ]);
    }

    public function get_availability(WP_REST_Request $req): WP_REST_Response
    {
        $slot_id = (int) $req['slot_id'];
        $avail = wrb_engine()->get_availability($slot_id);

        return new WP_REST_Response([
            'slot_id' => $slot_id,
            'available' => $avail,
        ]);
    }

    public function get_product_config(WP_REST_Request $req): WP_REST_Response
    {
        $product_id = (int) $req['product_id'];
        if (! WRB_Product_Meta::is_bookable($product_id)) {
            return new WP_REST_Response(['enabled' => false]);
        }

        return new WP_REST_Response([
            'enabled' => true,
            'product_id' => $product_id,
            'pricing' => WRB_Product_Meta::get_pricing($product_id),
            'deposit' => WRB_Product_Meta::get_deposit($product_id),
            'duration' => (string) get_post_meta($product_id, '_wrb_duration', true),
            'meeting_point' => (string) get_post_meta($product_id, '_wrb_meeting_point', true),
            'max_group' => (int) get_post_meta($product_id, '_wrb_max_group', true),
        ]);
    }

    /**
     * Return active WooCommerce payment gateways for display in the booking widget.
     * Each entry has id, title, icon (URL or SVG key), and description.
     */
    public function get_payment_methods(WP_REST_Request $req): WP_REST_Response
    {
        $gateways = WC()->payment_gateways ? WC()->payment_gateways->get_available_payment_gateways() : [];
        $methods = [];
        foreach ($gateways as $gw) {
            $methods[] = [
                'id' => $gw->id,
                'title' => $gw->get_title(),
                'description' => wp_strip_all_tags($gw->get_description() ?: ''),
                'icon' => $gw->get_icon() ? wp_strip_all_tags($gw->get_icon()) : '',
                'icon_url' => $gw->icon ?: '',
            ];
        }

        // Always append standard accepted card icons (they apply to all card gateways).
        return new WP_REST_Response([
            'gateways' => $methods,
            'accepts_card' => $this->accepts_cards($gateways),
        ]);
    }

    private function accepts_cards(array $gateways): bool
    {
        $card_gateways = ['stripe', 'woocommerce_payments', 'square', 'ppcp-gateway',
            'braintree_cc', 'authorizenet_aim', 'cybersource', 'nmi', 'eway'];
        foreach ($card_gateways as $id) {
            if (isset($gateways[$id])) {
                return true;
            }
        }

        return false;
    }
}
