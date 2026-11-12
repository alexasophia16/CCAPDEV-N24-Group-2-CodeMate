<?php

// Model for a tutee's bookings: creating, listing, cancelling and reviewing.
// A booking points to a tutor_slot; the tutor, course, date and mode all come from that slot.
class Booking extends Model
{
    // Every value bookings.status can have (same as the ENUM in the .sql file).
    // Used to check the "Show" filter on My Bookings.
    public const STATUSES = ['pending', 'confirmed', 'completed', 'cancelled', 'declined'];

    // Books a slot for a tutee. Returns the new booking id, or null if the slot is no longer open.
    // Two things happen together (a transaction): the slot is marked "booked" and the booking is
    // inserted. If anything fails in between, rollBack() undoes both so the data never half-saves.
    public function create(int $slotId, int $tuteeId, string $notes): ?int
    {
        $this->db->beginTransaction();
        try {
            // Claim the slot first. The WHERE only matches if the slot is still open, its course is
            // active and it's in the future. If two tutees click at the same time, only the first
            // UPDATE matches; the second one changes 0 rows and gets null back.
            $claimed = $this->execute(
                'UPDATE tutor_slots s
                   JOIN courses c ON c.course_id = s.course_id
                    SET s.status = "booked"
                  WHERE s.slot_id = ? AND s.status = "open" AND c.status = "active"
                    AND (s.slot_date > CURDATE() OR (s.slot_date = CURDATE() AND s.start_time > CURTIME()))',
                [$slotId]
            );
            if ($claimed === 0) {
                $this->db->rollBack();
                return null;
            }

            // empty notes are saved as NULL instead of an empty string
            $this->execute(
                'INSERT INTO bookings (slot_id, tutee_id, notes) VALUES (?, ?, ?)',
                [$slotId, $tuteeId, $notes === '' ? null : $notes]
            );
            $bookingId = $this->lastInsertId();
            $this->db->commit();
            return $bookingId;
        } catch (Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }

    // All bookings of one tutee for the My Bookings table, newest schedule first.
    // $status filters the list ('' or an unknown value shows everything).
    // is_upcoming is 1 if the session hasn't started yet; the view uses it to show the Cancel button.
    // review_rating comes from a LEFT JOIN, so it's NULL when the booking has no review yet.
    public function forTutee(int $tuteeId, string $status): array
    {
        $sql = 'SELECT b.booking_id, b.status, b.notes, s.slot_date, s.start_time, s.end_time, s.mode,
                       s.tutor_id, u.full_name AS tutor_name, c.course_code, c.course_name,
                       r.rating AS review_rating,
                       (s.slot_date > CURDATE() OR (s.slot_date = CURDATE() AND s.start_time > CURTIME())) AS is_upcoming
                  FROM bookings b
                  JOIN tutor_slots s ON s.slot_id = b.slot_id
                  JOIN users u ON u.user_id = s.tutor_id
                  JOIN courses c ON c.course_id = s.course_id
                  LEFT JOIN reviews r ON r.booking_id = b.booking_id
                 WHERE b.tutee_id = ?';
        $params = [$tuteeId];

        if (in_array($status, self::STATUSES, true)) {
            $sql .= ' AND b.status = ?';
            $params[] = $status;
        }

        return $this->fetchAll($sql . ' ORDER BY s.slot_date DESC, s.start_time DESC', $params);
    }

    // One booking, but only if it belongs to this tutee (returns null otherwise).
    // Checking tutee_id here stops someone from cancelling or reviewing another person's booking
    // by changing the booking_id in the form.
    public function findForTutee(int $bookingId, int $tuteeId): ?array
    {
        return $this->fetchOne(
            'SELECT b.booking_id, b.status, b.slot_id, s.tutor_id, s.slot_date, c.course_code,
                    (s.slot_date > CURDATE() OR (s.slot_date = CURDATE() AND s.start_time > CURTIME())) AS is_upcoming,
                    (SELECT COUNT(*) FROM reviews r WHERE r.booking_id = b.booking_id) AS has_review
               FROM bookings b
               JOIN tutor_slots s ON s.slot_id = b.slot_id
               JOIN courses c ON c.course_id = s.course_id
              WHERE b.booking_id = ? AND b.tutee_id = ?',
            [$bookingId, $tuteeId]
        );
    }

    // Cancels a pending/confirmed booking and puts its slot back to "open" so others can book it.
    // Both changes are in one transaction, like create().
    public function cancel(int $bookingId, int $slotId): bool
    {
        $this->db->beginTransaction();
        try {
            $cancelled = $this->execute(
                'UPDATE bookings SET status = "cancelled"
                  WHERE booking_id = ? AND status IN ("pending", "confirmed")',
                [$bookingId]
            );
            // only reopen the slot if the booking was actually cancelled
            if ($cancelled > 0) {
                $this->execute('UPDATE tutor_slots SET status = "open" WHERE slot_id = ?', [$slotId]);
            }
            $this->db->commit();
            return $cancelled > 0;
        } catch (Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }

    // Admin approval 
    // A new booking stays "pending" until an admin approves or declines it on the
    // Verify Tutors page. These methods are used by AdminUserController.

    // Every pending booking, oldest request first, for the admin's Verify Tutors page.
    // is_upcoming is 0 if the schedule has already started (such a request can only be declined).
    public function pendingForAdmin(): array
    {
        return $this->fetchAll(
            'SELECT b.booking_id, b.notes, b.created_at,
                    s.slot_date, s.start_time, s.end_time, s.mode,
                    tutee.full_name AS tutee_name, tutee.email AS tutee_email,
                    tutor.full_name AS tutor_name,
                    c.course_code, c.course_name,
                    (s.slot_date > CURDATE() OR (s.slot_date = CURDATE() AND s.start_time > CURTIME())) AS is_upcoming
               FROM bookings b
               JOIN tutor_slots s ON s.slot_id = b.slot_id
               JOIN users tutee ON tutee.user_id = b.tutee_id
               JOIN users tutor ON tutor.user_id = s.tutor_id
               JOIN courses c ON c.course_id = s.course_id
              WHERE b.status = "pending"
              ORDER BY b.created_at ASC'
        );
    }

    // Admin decision on a pending booking: $decision is 'confirmed' or 'declined'.
    // Returns the booking's details (for the notifications), or null if it was no longer
    // pending (already cancelled/decided) or the decision is invalid.
    // Like create() and cancel(), the booking and its slot change together in one transaction:
    // a declined booking puts its slot back to "open" so other tutees can book it.
    public function review(int $bookingId, string $decision): ?array
    {
        if (!in_array($decision, ['confirmed', 'declined'], true)) {
            return null;
        }

        $this->db->beginTransaction();
        try {
            $sql = 'UPDATE bookings b
                      JOIN tutor_slots s ON s.slot_id = b.slot_id
                       SET b.status = ?
                     WHERE b.booking_id = ? AND b.status = "pending"';
            // don't approve a session that has already started
            if ($decision === 'confirmed') {
                $sql .= ' AND (s.slot_date > CURDATE() OR (s.slot_date = CURDATE() AND s.start_time > CURTIME()))';
            }

            $changed = $this->execute($sql, [$decision, $bookingId]);
            if ($changed === 0) {
                $this->db->rollBack();
                return null;
            }

            if ($decision === 'declined') {
                $this->execute(
                    'UPDATE tutor_slots s JOIN bookings b ON b.slot_id = s.slot_id
                        SET s.status = "open" WHERE b.booking_id = ?',
                    [$bookingId]
                );
            }
            $this->db->commit();
        } catch (Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }

        return $this->fetchOne(
            'SELECT b.booking_id, b.tutee_id, s.tutor_id, s.slot_date, s.start_time, s.end_time,
                    c.course_code, tutor.full_name AS tutor_name, tutee.full_name AS tutee_name
               FROM bookings b
               JOIN tutor_slots s ON s.slot_id = b.slot_id
               JOIN courses c ON c.course_id = s.course_id
               JOIN users tutor ON tutor.user_id = s.tutor_id
               JOIN users tutee ON tutee.user_id = b.tutee_id
              WHERE b.booking_id = ?',
            [$bookingId]
        );
    }

    // Saves a review. reviews.booking_id is UNIQUE in the database, so a booking
    // can never get two reviews even if the controller check were skipped.
    public function addReview(int $bookingId, int $rating, string $comment): void
    {
        $this->execute(
            'INSERT INTO reviews (booking_id, rating, comment) VALUES (?, ?, ?)',
            [$bookingId, $rating, $comment === '' ? null : $comment]
        );
    }
}
