<?php

defined('ABSPATH') || exit;

/**
 * Booking engine contract.
 *
 * Every booking backend (native or external bridge) implements this interface
 * so the rest of the plugin is engine-agnostic.
 */
interface WRB_Engine_Interface
{
    /** Unique slug identifying this engine, e.g. 'native', 'wc-bookings'. */
    public function engine_id(): string;

    /** Human-readable label shown in settings. */
    public function engine_label(): string;

    /**
     * Return available slots for a product in a given month.
     *
     * @param  int  $product_id  WooCommerce product ID.
     * @param  string  $year_month  'YYYY-MM'.
     * @return array<string, array<array{id:int, time:string, capacity:int, booked:int, available:int}>>
     *                                                                                                   Keyed by 'YYYY-MM-DD', each value is an array of slot data.
     */
    public function get_slots_by_month(int $product_id, string $year_month): array;

    /**
     * Return remaining capacity for a specific slot.
     *
     * @param  int  $slot_id  Engine-specific slot/resource ID.
     * @return int Seats remaining (0 = sold out).
     */
    public function get_availability(int $slot_id): int;

    /**
     * Create a booking record.
     *
     * Called on successful WooCommerce order placement.
     *
     * @param array{
     *   order_id: int,
     *   order_item_id: int,
     *   product_id: int,
     *   slot_id: int,
     *   customer_name: string,
     *   customer_email: string,
     *   adults: int,
     *   children: int,
     *   seniors: int,
     *   notes: string,
     * } $data
     * @return int|false Booking ID, or false on failure.
     */
    public function create_booking(array $data): int|false;

    /**
     * Confirm that a slot can accept $guests more guests.
     */
    public function check_capacity(int $slot_id, int $guests): true|WP_Error;

    /**
     * Update the status of a booking.
     *
     * @param  int  $booking_id  Engine booking ID.
     * @param  string  $status  'confirmed', 'cancelled', 'pending', 'failed'.
     */
    public function update_booking_status(int $booking_id, string $status): void;

    /**
     * Retrieve a single booking.
     */
    public function get_booking(int $booking_id): ?object;

    /**
     * List bookings, with optional filters.
     *
     * @param array{
     *   product_id?: int,
     *   status?: string,
     *   slot_id?: int,
     *   order_id?: int,
     *   limit?: int,
     *   offset?: int,
     * } $args
     * @return array<object>
     */
    public function get_bookings(array $args = []): array;

    /**
     * Whether this engine supports admin slot management inside this plugin's UI.
     * Engines that delegate slot management to an external UI return false.
     */
    public function supports_slot_management(): bool;
}
