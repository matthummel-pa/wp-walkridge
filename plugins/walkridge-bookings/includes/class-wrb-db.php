<?php

defined('ABSPATH') || exit;

/**
 * Database layer — two custom tables:
 *   {prefix}wrb_slots    — bookable time slots per product
 *   {prefix}wrb_bookings — booking records
 *
 * This class is used by WRB_Native_Engine; external engine bridges manage
 * their own persistence.
 */
final class WRB_DB
{
    private static ?WRB_DB $instance = null;

    private wpdb $db;

    public string $tbl_slots;

    public string $tbl_bookings;

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    private function __construct()
    {
        global $wpdb;
        $this->db = $wpdb;
        $this->tbl_slots = $wpdb->prefix.'wrb_slots';
        $this->tbl_bookings = $wpdb->prefix.'wrb_bookings';
    }

    /* ── Schema ──────────────────────────────────────────────────────────── */

    public static function create_tables(): void
    {
        global $wpdb;
        $slots = $wpdb->prefix.'wrb_slots';
        $bookings = $wpdb->prefix.'wrb_bookings';
        $charset = $wpdb->get_charset_collate();

        require_once ABSPATH.'wp-admin/includes/upgrade.php';

        // Use standard MySQL syntax — the SQLite integration plugin translates it.
        // We keep types simple (no UNSIGNED, no ON UPDATE) to maximise compatibility.
        dbDelta("CREATE TABLE {$slots} (
          id          BIGINT NOT NULL AUTO_INCREMENT,
          product_id  BIGINT NOT NULL DEFAULT 0,
          slot_date   VARCHAR(12) NOT NULL DEFAULT '',
          slot_time   VARCHAR(10) NOT NULL DEFAULT '09:00',
          capacity    INT NOT NULL DEFAULT 15,
          booked      INT NOT NULL DEFAULT 0,
          status      VARCHAR(20) NOT NULL DEFAULT 'active',
          notes       TEXT NOT NULL,
          created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY  (id),
          KEY product_date (product_id, slot_date)
        ) {$charset};");

        dbDelta("CREATE TABLE {$bookings} (
          id               BIGINT NOT NULL AUTO_INCREMENT,
          order_id         BIGINT NOT NULL DEFAULT 0,
          order_item_id    BIGINT NOT NULL DEFAULT 0,
          product_id       BIGINT NOT NULL DEFAULT 0,
          slot_id          BIGINT NOT NULL DEFAULT 0,
          customer_name    VARCHAR(160) NOT NULL DEFAULT '',
          customer_email   VARCHAR(200) NOT NULL DEFAULT '',
          adults           INT NOT NULL DEFAULT 1,
          children         INT NOT NULL DEFAULT 0,
          seniors          INT NOT NULL DEFAULT 0,
          ticket_number    VARCHAR(32) NOT NULL DEFAULT '',
          status           VARCHAR(30) NOT NULL DEFAULT 'pending',
          special_requests TEXT,
          created_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          updated_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (id),
          KEY order_id (order_id),
          KEY slot_product (product_id, status),
          UNIQUE KEY ticket_number (ticket_number)
        ) {$charset};");

        update_option('wrb_db_version', WRB_VERSION);
    }

    /* ── Slot CRUD ───────────────────────────────────────────────────────── */

    public function get_slot(int $id): ?object
    {
        return $this->db->get_row(
            $this->db->prepare("SELECT * FROM {$this->tbl_slots} WHERE id = %d", $id)
        ) ?: null;
    }

    public function get_slots_for_product(
        int $product_id,
        string $from,
        string $to,
        bool $future_only = true
    ): array {
        $today = current_time('Y-m-d');
        $future = $future_only ? $this->db->prepare('AND slot_date >= %s', $today) : '';

        return (array) $this->db->get_results(
            $this->db->prepare(
                "SELECT * FROM {$this->tbl_slots}
                 WHERE product_id = %d
                   AND status = 'active'
                   AND slot_date BETWEEN %s AND %s
                   {$future}
                 ORDER BY slot_date ASC, slot_time ASC",
                $product_id, $from, $to
            )
        );
    }

    public function get_slots_by_month(int $product_id, string $year_month): array
    {
        [$y, $m] = explode('-', $year_month.'-01');
        $from = sprintf('%04d-%02d-01', $y, $m);
        $to = gmdate('Y-m-t', strtotime($from));
        $rows = $this->get_slots_for_product($product_id, $from, $to);
        $out = [];
        foreach ($rows as $r) {
            $out[$r->slot_date][] = $r;
        }

        return $out;
    }

    public function create_slot(array $data): int|false
    {
        $ok = $this->db->insert(
            $this->tbl_slots,
            [
                'product_id' => (int) $data['product_id'],
                'slot_date' => sanitize_text_field($data['slot_date']),
                'slot_time' => sanitize_text_field($data['slot_time'] ?? '09:00:00'),
                'capacity' => (int) ($data['capacity'] ?? 15),
                'booked' => 0,
                'status' => 'active',
                'notes' => sanitize_text_field($data['notes'] ?? ''),
            ],
            ['%d', '%s', '%s', '%d', '%d', '%s', '%s']
        );

        return $ok ? $this->db->insert_id : false;
    }

    public function update_slot(int $id, array $data): bool
    {
        $allowed = ['slot_date', 'slot_time', 'capacity', 'status', 'notes'];
        $update = array_intersect_key($data, array_flip($allowed));
        if (empty($update)) {
            return false;
        }

        return (bool) $this->db->update($this->tbl_slots, $update, ['id' => $id]);
    }

    public function delete_slot(int $id): true|WP_Error
    {
        $live = (int) $this->db->get_var(
            $this->db->prepare(
                "SELECT COUNT(*) FROM {$this->tbl_bookings}
                 WHERE slot_id = %d AND status NOT IN ('cancelled','failed')",
                $id
            )
        );
        if ($live > 0) {
            return new WP_Error(
                'slot_has_bookings',
                __('Slot has active bookings — cancel them before deleting.', 'wr-bookings')
            );
        }
        $this->db->delete($this->tbl_slots, ['id' => $id], ['%d']);

        return true;
    }

    public function adjust_booked(int $slot_id, int $delta): void
    {
        $this->db->query(
            $this->db->prepare(
                "UPDATE {$this->tbl_slots}
                 SET booked = GREATEST(0, CAST(booked AS SIGNED) + %d)
                 WHERE id = %d",
                $delta, $slot_id
            )
        );
    }

    public function get_availability(int $slot_id): int
    {
        $slot = $this->get_slot($slot_id);
        if (! $slot || $slot->status !== 'active') {
            return 0;
        }

        return max(0, (int) $slot->capacity - (int) $slot->booked);
    }

    /* ── Booking CRUD ────────────────────────────────────────────────────── */

    public function create_booking(array $data): int|false
    {
        $ticket = $this->generate_ticket((int) $data['slot_id']);
        $ok = $this->db->insert(
            $this->tbl_bookings,
            [
                'order_id' => (int) ($data['order_id'] ?? 0),
                'order_item_id' => (int) ($data['order_item_id'] ?? 0),
                'product_id' => (int) $data['product_id'],
                'slot_id' => (int) ($data['slot_id'] ?? 0),
                'customer_name' => sanitize_text_field($data['customer_name'] ?? ''),
                'customer_email' => sanitize_email($data['customer_email'] ?? ''),
                'adults' => (int) ($data['adults'] ?? 1),
                'children' => (int) ($data['children'] ?? 0),
                'seniors' => (int) ($data['seniors'] ?? 0),
                'ticket_number' => $ticket,
                'status' => 'pending',
                'special_requests' => sanitize_textarea_field($data['special_requests'] ?? ''),
            ],
            ['%d', '%d', '%d', '%d', '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%s']
        );
        if (! $ok) {
            return false;
        }
        $booking_id = $this->db->insert_id;
        $guests = (int) ($data['adults'] ?? 1) + (int) ($data['children'] ?? 0) + (int) ($data['seniors'] ?? 0);
        if ((int) ($data['slot_id'] ?? 0) > 0) {
            $this->adjust_booked((int) $data['slot_id'], $guests);
        }

        return $booking_id;
    }

    public function get_booking(int $id): ?object
    {
        return $this->db->get_row(
            $this->db->prepare(
                "SELECT b.*, s.slot_date, s.slot_time, s.capacity,
                        p.post_title AS tour_name
                 FROM {$this->tbl_bookings} b
                 LEFT JOIN {$this->tbl_slots} s ON s.id = b.slot_id
                 LEFT JOIN {$this->db->posts} p ON p.ID = b.product_id
                 WHERE b.id = %d",
                $id
            )
        ) ?: null;
    }

    public function get_bookings(array $args = []): array
    {
        [$where, $values] = $this->build_booking_where($args);
        $limit = (int) ($args['limit'] ?? 50);
        $offset = (int) ($args['offset'] ?? 0);
        $values[] = $limit;
        $values[] = $offset;

        return (array) $this->db->get_results(
            $this->db->prepare(
                "SELECT b.*, s.slot_date, s.slot_time, p.post_title AS tour_name
                 FROM {$this->tbl_bookings} b
                 LEFT JOIN {$this->tbl_slots} s ON s.id = b.slot_id
                 LEFT JOIN {$this->db->posts} p ON p.ID = b.product_id
                 {$where}
                 ORDER BY s.slot_date DESC, b.created_at DESC
                 LIMIT %d OFFSET %d",
                $values
            )
        );
    }

    public function count_bookings(array $args = []): int
    {
        [$where, $values] = $this->build_booking_where($args);
        $sql = "SELECT COUNT(*) FROM {$this->tbl_bookings} b {$where}";

        return (int) ($values
            ? $this->db->get_var($this->db->prepare($sql, $values))
            : $this->db->get_var($sql)
        );
    }

    public function update_booking(int $id, array $data): void
    {
        $booking = $this->get_booking($id);
        if (! $booking) {
            return;
        }
        $allowed = ['order_id', 'order_item_id', 'status', 'ticket_number', 'special_requests'];
        $update = array_intersect_key($data, array_flip($allowed));
        if (empty($update)) {
            return;
        }
        $this->db->update($this->tbl_bookings, $update, ['id' => $id]);

        // Adjust slot booked count on status transitions.
        if (isset($data['status'])) {
            $guests = (int) $booking->adults + (int) $booking->children + (int) $booking->seniors;
            $old = $booking->status;
            $new = $data['status'];
            if ($new === 'cancelled' && $old !== 'cancelled' && (int) $booking->slot_id > 0) {
                $this->adjust_booked((int) $booking->slot_id, -$guests);
            } elseif ($old === 'cancelled' && in_array($new, ['confirmed', 'pending'], true) && (int) $booking->slot_id > 0) {
                $this->adjust_booked((int) $booking->slot_id, $guests);
            }
        }
    }

    /* ── Internals ───────────────────────────────────────────────────────── */

    private function build_booking_where(array $args): array
    {
        $clauses = [];
        $values = [];
        if (! empty($args['product_id'])) {
            $clauses[] = 'b.product_id = %d';
            $values[] = (int) $args['product_id'];
        }
        if (! empty($args['status'])) {
            $clauses[] = 'b.status = %s';
            $values[] = $args['status'];
        }
        if (! empty($args['slot_id'])) {
            $clauses[] = 'b.slot_id = %d';
            $values[] = (int) $args['slot_id'];
        }
        if (! empty($args['order_id'])) {
            $clauses[] = 'b.order_id = %d';
            $values[] = (int) $args['order_id'];
        }
        if (isset($args['search'])) {
            $clauses[] = '(b.customer_name LIKE %s OR b.customer_email LIKE %s OR b.ticket_number LIKE %s)';
            $v = '%'.$this->db->esc_like($args['search']).'%';
            $values[] = $v;
            $values[] = $v;
            $values[] = $v;
        }
        $where = $clauses ? 'WHERE '.implode(' AND ', $clauses) : '';

        return [$where, $values];
    }

    private function generate_ticket(int $slot_id): string
    {
        return sprintf('HG-%05d-%s', $slot_id, strtoupper(bin2hex(random_bytes(3))));
    }
}
