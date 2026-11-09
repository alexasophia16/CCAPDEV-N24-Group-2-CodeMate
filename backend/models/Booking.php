<?php

class Booking extends Model
{
    public const STATUSES = ['pending', 'confirmed', 'completed', 'cancelled', 'declined'];

    // returns the new booking id, or null if the slot is no longer open
    public function create(int $slotId, int $tuteeId, string $notes): ?int
    {
        $this->db->beginTransaction();
        try {
            // claiming the slot first stops two tutees from booking it at the same time
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

    // booking details for its owner, or null
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

    // cancels an upcoming pending/confirmed booking and reopens its slot
    public function cancel(int $bookingId, int $slotId): bool
    {
        $this->db->beginTransaction();
        try {
            $cancelled = $this->execute(
                'UPDATE bookings SET status = "cancelled"
                  WHERE booking_id = ? AND status IN ("pending", "confirmed")',
                [$bookingId]
            );
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

    public function addReview(int $bookingId, int $rating, string $comment): void
    {
        $this->execute(
            'INSERT INTO reviews (booking_id, rating, comment) VALUES (?, ?, ?)',
            [$bookingId, $rating, $comment === '' ? null : $comment]
        );
    }
}
