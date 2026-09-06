<?php

defined('ABSPATH') || exit;

/**
 * REST API endpoints for the field map.
 *
 * GET  /wp-json/wr-field-map/v1/monuments     — serve data/monuments.json (~800 OSM pins)
 * GET  /wp-json/wr-field-map/v1/places        — serve tour geography places
 * PUT  /wp-json/wr-field-map/v1/places        — owner admin: save custom places (admin only)
 * GET  /wp-json/wr-field-map/v1/maps-config   — serve map centre/zoom/rotation
 * PUT  /wp-json/wr-field-map/v1/maps-config   — owner admin: save map view config (admin only)
 */
final class WRFM_REST
{
    public static function register_routes(): void
    {
        $ns = 'wr-field-map/v1';

        register_rest_route($ns, '/monuments', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [self::class, 'get_monuments'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route($ns, '/places', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [self::class, 'get_places'],
                'permission_callback' => '__return_true',
            ],
            [
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => [self::class, 'save_places'],
                'permission_callback' => fn () => current_user_can('manage_options'),
                'args' => [
                    'places' => ['required' => true, 'type' => 'array'],
                ],
            ],
        ]);

        register_rest_route($ns, '/maps-config', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [self::class, 'get_maps_config'],
                'permission_callback' => '__return_true',
            ],
            [
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => [self::class, 'save_maps_config'],
                'permission_callback' => fn () => current_user_can('manage_options'),
            ],
        ]);
    }

    /* ── Monuments (~800 OSM entries, served from plugin data dir) ─────── */

    public static function get_monuments(): WP_REST_Response
    {
        // Serve from WP option first (admin may have customised),
        // then fall back to the bundled JSON file.
        $custom = get_option('wrfm_monuments_custom');

        if ($custom) {
            return new WP_REST_Response($custom);
        }

        $file = WRFM_DIR.'data/monuments.json';

        if (! file_exists($file)) {
            return new WP_REST_Response(['monuments' => []], 200);
        }

        $data = json_decode((string) file_get_contents($file), true);
        if (! is_array($data)) {
            return new WP_REST_Response(['monuments' => []], 200);
        }

        // Marketplace hosts must not hotlink Wikimedia at runtime — keep credits/pages only.
        if (! empty($data['monuments']) && is_array($data['monuments'])) {
            foreach ($data['monuments'] as &$monument) {
                if (! empty($monument['image']) && is_string($monument['image'])
                    && (str_contains($monument['image'], 'wikimedia.org') || str_contains($monument['image'], 'upload.wikimedia.org'))) {
                    $monument['image'] = '';
                }
            }
            unset($monument);
        }

        return new WP_REST_Response($data);
    }

    /* ── Tour geography places ──────────────────────────────────────────── */

    public static function get_places(): WP_REST_Response
    {
        $custom = get_option('wrfm_places_custom');

        if ($custom) {
            return new WP_REST_Response(['places' => $custom]);
        }

        $file = WRFM_DIR.'data/area-map.json';

        if (! file_exists($file)) {
            return new WP_REST_Response(['places' => []]);
        }

        $data = json_decode(file_get_contents($file), true);

        return new WP_REST_Response($data ?: ['places' => []]);
    }

    public static function save_places(WP_REST_Request $request): WP_REST_Response
    {
        $places = $request->get_param('places');

        if (! is_array($places)) {
            return new WP_REST_Response(['error' => 'Invalid places data.'], 400);
        }

        // Basic sanitisation — keep only known scalar fields.
        $clean = array_map(function (array $p): array {
            return [
                'id' => sanitize_key($p['id'] ?? ''),
                'title' => sanitize_text_field($p['title'] ?? ''),
                'blurb' => sanitize_textarea_field($p['blurb'] ?? ''),
                'category' => sanitize_key($p['category'] ?? 'area'),
                'tourHref' => esc_url_raw($p['tourHref'] ?? ''),
                'tourLabel' => sanitize_text_field($p['tourLabel'] ?? ''),
                'lat' => (float) ($p['lat'] ?? 0),
                'lng' => (float) ($p['lng'] ?? 0),
                'x' => (int) ($p['x'] ?? 50),
                'z' => (int) ($p['z'] ?? 50),
                'elev' => (int) ($p['elev'] ?? 20),
                'visible' => (bool) ($p['visible'] ?? true),
            ];
        }, array_values($places));

        update_option('wrfm_places_custom', $clean, false);

        return new WP_REST_Response(['saved' => true, 'count' => count($clean)]);
    }

    /* ── Map view config (centre/zoom/rotation) ─────────────────────────── */

    public static function get_maps_config(): WP_REST_Response
    {
        $custom = get_option('wrfm_maps_config');

        if ($custom) {
            return new WP_REST_Response($custom);
        }

        $file = WRFM_DIR.'data/maps-config.json';

        if (! file_exists($file)) {
            return new WP_REST_Response([]);
        }

        $data = json_decode(file_get_contents($file), true);

        return new WP_REST_Response($data ?: []);
    }

    public static function save_maps_config(WP_REST_Request $request): WP_REST_Response
    {
        $body = $request->get_json_params();

        if (empty($body) || ! is_array($body)) {
            return new WP_REST_Response(['error' => 'Invalid config.'], 400);
        }

        $clean = [
            'zoom' => isset($body['zoom']) ? (float) $body['zoom'] : 13.4,
            'rotation' => isset($body['rotation']) ? (float) $body['rotation'] : 0,
        ];

        if (! empty($body['center']['lat']) && ! empty($body['center']['lng'])) {
            $clean['center'] = [
                'lat' => (float) $body['center']['lat'],
                'lng' => (float) $body['center']['lng'],
            ];
        }

        update_option('wrfm_maps_config', $clean, false);

        return new WP_REST_Response(['saved' => true]);
    }
}
