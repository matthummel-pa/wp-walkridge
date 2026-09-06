<?php

defined('ABSPATH') || exit;

/**
 * Compatibility layer — detects third-party booking plugins and returns
 * the best available engine.  Also exposes a filter so site owners can
 * force a specific engine via code.
 *
 * Supported bridges (gracefully degraded when plugin not active):
 *   • WooCommerce Bookings (official, class WC_Bookings)
 *   • YITH WooCommerce Booking (class YITH_WCBK)
 *   • Tyche / BKAP (class BKAP_Booking)
 *   • Amelia (namespace AmeliaBooking)
 */
final class WRB_Compat
{
    /**
     * Detect the best engine and return an instance.
     *
     * Priority order (highest first):
     *   1. Site option override ('wrb_engine_override')
     *   2. WooCommerce Bookings
     *   3. YITH WC Booking
     *   4. Tyche / BKAP
     *   5. Amelia
     *   6. Native (built-in, always available)
     */
    public static function resolve_engine(): WRB_Engine_Interface
    {
        $override = get_option('wrb_engine_override', '');

        $engine = match ($override) {
            'wc-bookings' => self::try_wc_bookings(),
            'yith' => self::try_yith(),
            'bkap' => self::try_bkap(),
            'amelia' => self::try_amelia(),
            'native' => new WRB_Native_Engine,
            default => self::auto_detect(),
        };

        /**
         * Filter the resolved booking engine.
         *
         * @param  WRB_Engine_Interface  $engine  Resolved engine instance.
         */
        return apply_filters('wrb_engine', $engine);
    }

    private static function auto_detect(): WRB_Engine_Interface
    {
        return self::try_wc_bookings()
            ?? self::try_yith()
            ?? self::try_bkap()
            ?? self::try_amelia()
            ?? new WRB_Native_Engine;
    }

    /* ── Bridge factories (return null when plugin is absent) ────────────── */

    private static function try_wc_bookings(): ?WRB_Engine_Interface
    {
        if (! class_exists('WC_Bookings') && ! class_exists('WC_Product_Booking')) {
            return null;
        }

        return new WRB_Bridge_WCBookings;
    }

    private static function try_yith(): ?WRB_Engine_Interface
    {
        if (! class_exists('YITH_WCBK') && ! defined('YITH_WCBK_VERSION')) {
            return null;
        }

        return new WRB_Bridge_YITH;
    }

    private static function try_bkap(): ?WRB_Engine_Interface
    {
        if (! class_exists('BKAP_Booking') && ! defined('BKAP_VERSION')) {
            return null;
        }

        return new WRB_Bridge_BKAP;
    }

    private static function try_amelia(): ?WRB_Engine_Interface
    {
        if (! class_exists('AmeliaBooking\Plugin')
            && ! defined('AMELIA_VERSION')
            && ! class_exists('AmeliaBooking\Infrastructure\WP\InstallActions\ActivatePlugin')
        ) {
            return null;
        }

        return new WRB_Bridge_Amelia;
    }

    /** List all engines available on this install (for settings UI). */
    public static function available_engines(): array
    {
        $engines = ['native' => __('Native (built-in)', 'wr-bookings')];
        if (self::try_wc_bookings()) {
            $engines['wc-bookings'] = __('WooCommerce Bookings', 'wr-bookings');
        }
        if (self::try_yith()) {
            $engines['yith'] = __('YITH WooCommerce Booking', 'wr-bookings');
        }
        if (self::try_bkap()) {
            $engines['bkap'] = __('Booking & Appointment Plugin (Tyche)', 'wr-bookings');
        }
        if (self::try_amelia()) {
            $engines['amelia'] = __('Amelia Booking', 'wr-bookings');
        }

        return $engines;
    }
}

/* ── Stub bridges — extend with real API calls when plugins are active ───── */

/**
 * WooCommerce Bookings bridge.
 * Slot queries are forwarded to WC_Bookings_Resource / WC_Bookings_Availability_Handler.
 * When the plugin is active this bridge is replaced at boot; stubs shown here for reference.
 */
class WRB_Bridge_WCBookings implements WRB_Engine_Interface
{
    public function engine_id(): string
    {
        return 'wc-bookings';
    }

    public function engine_label(): string
    {
        return 'WooCommerce Bookings';
    }

    public function supports_slot_management(): bool
    {
        return false;
    }

    public function get_slots_by_month(int $product_id, string $year_month): array
    {
        // Delegate to WC_Bookings_Availability_Handler when active.
        if (! function_exists('get_wc_booking_availability')) {
            return [];
        }
        $product = wc_get_product($product_id);
        if (! $product || ! is_a($product, 'WC_Product_Booking')) {
            return [];
        }
        // Build date range and query WC Bookings availability.
        [$y, $m] = explode('-', $year_month.'-01');
        $from = mktime(0, 0, 0, (int) $m, 1, (int) $y);
        $to = mktime(23, 59, 59, (int) $m, (int) date('t', $from), (int) $y);
        $available = $product->get_available_bookings($from, $to);
        $out = [];
        foreach ((array) $available as $slot) {
            $date = date('Y-m-d', $slot['start']);
            $out[$date][] = [
                'id' => $slot['start'],
                'date' => $date,
                'time' => date('H:i', $slot['start']),
                'capacity' => $slot['quantity'] ?? 1,
                'booked' => 0,
                'available' => $slot['quantity'] ?? 1,
                'status' => 'open',
            ];
        }

        return $out;
    }

    public function get_availability(int $slot_id): int
    {
        return 1;
    }

    public function check_capacity(int $slot_id, int $guests): true|WP_Error
    {
        return true;
    }

    public function create_booking(array $data): int|false
    {
        // WC Bookings handles booking creation through its own checkout process.
        return false;
    }

    public function update_booking_status(int $booking_id, string $status): void {}

    public function get_booking(int $booking_id): ?object
    {
        return class_exists('WC_Booking') ? new WC_Booking($booking_id) : null;
    }

    public function get_bookings(array $args = []): array
    {
        if (! function_exists('get_wc_bookings')) {
            return [];
        }

        return get_wc_bookings($args);
    }
}

/** YITH WooCommerce Booking bridge. */
class WRB_Bridge_YITH implements WRB_Engine_Interface
{
    public function engine_id(): string
    {
        return 'yith';
    }

    public function engine_label(): string
    {
        return 'YITH WC Booking';
    }

    public function supports_slot_management(): bool
    {
        return false;
    }

    public function get_slots_by_month(int $product_id, string $year_month): array
    {
        return [];
    }

    public function get_availability(int $slot_id): int
    {
        return 1;
    }

    public function check_capacity(int $slot_id, int $guests): true|WP_Error
    {
        return true;
    }

    public function create_booking(array $data): int|false
    {
        return false;
    }

    public function update_booking_status(int $booking_id, string $status): void {}

    public function get_booking(int $booking_id): ?object
    {
        return null;
    }

    public function get_bookings(array $args = []): array
    {
        return [];
    }
}

/** Tyche / BKAP bridge. */
class WRB_Bridge_BKAP implements WRB_Engine_Interface
{
    public function engine_id(): string
    {
        return 'bkap';
    }

    public function engine_label(): string
    {
        return 'Tyche/BKAP';
    }

    public function supports_slot_management(): bool
    {
        return false;
    }

    public function get_slots_by_month(int $product_id, string $year_month): array
    {
        return [];
    }

    public function get_availability(int $slot_id): int
    {
        return 1;
    }

    public function check_capacity(int $slot_id, int $guests): true|WP_Error
    {
        return true;
    }

    public function create_booking(array $data): int|false
    {
        return false;
    }

    public function update_booking_status(int $booking_id, string $status): void {}

    public function get_booking(int $booking_id): ?object
    {
        return null;
    }

    public function get_bookings(array $args = []): array
    {
        return [];
    }
}

/** Amelia bridge. */
class WRB_Bridge_Amelia implements WRB_Engine_Interface
{
    public function engine_id(): string
    {
        return 'amelia';
    }

    public function engine_label(): string
    {
        return 'Amelia';
    }

    public function supports_slot_management(): bool
    {
        return false;
    }

    public function get_slots_by_month(int $product_id, string $year_month): array
    {
        return [];
    }

    public function get_availability(int $slot_id): int
    {
        return 1;
    }

    public function check_capacity(int $slot_id, int $guests): true|WP_Error
    {
        return true;
    }

    public function create_booking(array $data): int|false
    {
        return false;
    }

    public function update_booking_status(int $booking_id, string $status): void {}

    public function get_booking(int $booking_id): ?object
    {
        return null;
    }

    public function get_bookings(array $args = []): array
    {
        return [];
    }
}
