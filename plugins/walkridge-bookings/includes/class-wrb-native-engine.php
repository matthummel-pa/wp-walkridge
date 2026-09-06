<?php

defined('ABSPATH') || exit;

/**
 * Native booking engine — stores everything in {prefix}wrb_slots + {prefix}wrb_bookings.
 * This is the default engine when no supported third-party booking plugin is active.
 */
final class WRB_Native_Engine implements WRB_Engine_Interface
{
    public function engine_id(): string
    {
        return 'native';
    }

    public function engine_label(): string
    {
        return __('Native (built-in)', 'wr-bookings');
    }

    public function supports_slot_management(): bool
    {
        return true;
    }

    private WRB_DB $db;

    public function __construct()
    {
        $this->db = WRB_DB::instance();
    }

    public function get_slots_by_month(int $product_id, string $year_month): array
    {
        $raw = $this->db->get_slots_by_month($product_id, $year_month);
        $out = [];
        foreach ($raw as $date => $slots) {
            $out[$date] = array_map([$this, 'format_slot'], $slots);
        }

        return $out;
    }

    public function get_availability(int $slot_id): int
    {
        return $this->db->get_availability($slot_id);
    }

    public function check_capacity(int $slot_id, int $guests): true|WP_Error
    {
        $avail = $this->db->get_availability($slot_id);
        if ($guests > $avail) {
            return new WP_Error(
                'insufficient_capacity',
                sprintf(
                    /* translators: 1: seats requested, 2: seats available */
                    __('Requested %1$d seat(s) but only %2$d available.', 'wr-bookings'),
                    $guests, $avail
                )
            );
        }

        return true;
    }

    public function create_booking(array $data): int|false
    {
        return $this->db->create_booking($data);
    }

    public function update_booking_status(int $booking_id, string $status): void
    {
        $this->db->update_booking($booking_id, ['status' => $status]);
    }

    public function get_booking(int $booking_id): ?object
    {
        return $this->db->get_booking($booking_id);
    }

    public function get_bookings(array $args = []): array
    {
        return $this->db->get_bookings($args);
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    public function format_slot(object $slot): array
    {
        $avail = max(0, (int) $slot->capacity - (int) $slot->booked);

        return [
            'id' => (int) $slot->id,
            'date' => $slot->slot_date,
            'time' => substr($slot->slot_time, 0, 5), // HH:MM
            'capacity' => (int) $slot->capacity,
            'booked' => (int) $slot->booked,
            'available' => $avail,
            'status' => $avail === 0 ? 'sold-out' : ($avail <= 3 ? 'few-left' : 'open'),
        ];
    }
}
