<?php

// Model for reading tutor information (Find a Tutor, Tutor Profile, Book a Consultation).
// A "tutor" is a row in users + a row in tutor_profiles with the same id.
class Tutor extends Model
{
    // Shared SELECT used by search() and find().
    // - Only verified tutors whose accounts are active are shown.
    // - rating, review_count and sessions are not stored in the database; they are
    //   calculated here with subqueries, so they are always up to date.
    // - GROUP_CONCAT joins all of a tutor's course names into one string like
    //   "Database Systems|Computer Architecture" (split back into an array later).
    // - Archived courses are left out because of the c.status = "active" join condition.
    private const BASE_QUERY = '
        SELECT u.user_id, u.full_name, tp.bio, tp.experience,
               (SELECT ROUND(AVG(r.rating), 1)
                  FROM reviews r
                  JOIN bookings b ON b.booking_id = r.booking_id
                  JOIN tutor_slots s ON s.slot_id = b.slot_id
                 WHERE s.tutor_id = u.user_id) AS rating,
               (SELECT COUNT(*)
                  FROM reviews r
                  JOIN bookings b ON b.booking_id = r.booking_id
                  JOIN tutor_slots s ON s.slot_id = b.slot_id
                 WHERE s.tutor_id = u.user_id) AS review_count,
               (SELECT COUNT(*)
                  FROM bookings b
                  JOIN tutor_slots s ON s.slot_id = b.slot_id
                 WHERE s.tutor_id = u.user_id AND b.status = "completed") AS sessions,
               GROUP_CONCAT(DISTINCT c.course_name ORDER BY c.course_name SEPARATOR "|") AS courses
          FROM tutor_profiles tp
          JOIN users u ON u.user_id = tp.tutor_id
          LEFT JOIN tutor_courses tc ON tc.tutor_id = tp.tutor_id
          LEFT JOIN courses c ON c.course_id = tc.course_id AND c.status = "active"
         WHERE tp.verification_status = "verified"
           AND u.account_status = "active"';

    // Allowed ORDER BY options for the "Sort" dropdown.
    // Only these exact strings can reach the SQL, so a user can't inject their own ORDER BY.
    // "rating IS NULL" puts tutors with no reviews yet at the bottom.
    private const SORTS = [
        'rating'   => 'rating IS NULL, rating DESC, sessions DESC, u.full_name',
        'sessions' => 'sessions DESC, rating DESC, u.full_name',
        'name'     => 'u.full_name',
    ];

    // Find a Tutor: search by text, filter by course, and sort.
    // $query    - text from the search box (matches tutor name, course code or course name)
    // $courseId - course from the dropdown, 0 means "All courses"
    // $sort     - 'rating', 'sessions' or 'name'
    public function search(string $query, int $courseId, string $sort): array
    {
        $sql = self::BASE_QUERY;
        $params = [];

        if ($query !== '') {
            // % and _ are wildcards in LIKE, so escape them to search for them as normal text
            $like = '%' . addcslashes($query, '%_\\') . '%';
            $sql .= ' AND (u.full_name LIKE ? OR EXISTS (
                        SELECT 1 FROM tutor_courses tc2
                          JOIN courses c2 ON c2.course_id = tc2.course_id
                         WHERE tc2.tutor_id = tp.tutor_id
                           AND (c2.course_code LIKE ? OR c2.course_name LIKE ?)))';
            array_push($params, $like, $like, $like);
        }

        if ($courseId > 0) {
            // EXISTS keeps the tutor if they teach that course, without hiding their other courses
            $sql .= ' AND EXISTS (SELECT 1 FROM tutor_courses tc3 WHERE tc3.tutor_id = tp.tutor_id AND tc3.course_id = ?)';
            $params[] = $courseId;
        }

        $sql .= ' GROUP BY u.user_id, u.full_name, tp.bio, tp.experience';
        // unknown sort values fall back to sorting by rating
        $sql .= ' ORDER BY ' . (self::SORTS[$sort] ?? self::SORTS['rating']);

        // turn each tutor's "a|b|c" course string into an array
        return array_map([$this, 'withCourseList'], $this->fetchAll($sql, $params));
    }

    // One tutor for the profile and booking pages.
    // Returns null if the tutor doesn't exist or isn't verified/active.
    public function find(int $tutorId): ?array
    {
        $tutor = $this->fetchOne(
            self::BASE_QUERY . ' AND tp.tutor_id = ? GROUP BY u.user_id, u.full_name, tp.bio, tp.experience',
            [$tutorId]
        );
        return $tutor ? $this->withCourseList($tutor) : null;
    }

    // Slots a tutee can still book: status "open", course still active,
    // and the date/time hasn't passed yet (today's slots only if they haven't started).
    public function openSlots(int $tutorId): array
    {
        return $this->fetchAll(
            'SELECT s.slot_id, s.slot_date, s.start_time, s.end_time, s.mode, c.course_code, c.course_name
               FROM tutor_slots s
               JOIN courses c ON c.course_id = s.course_id
              WHERE s.tutor_id = ? AND s.status = "open" AND c.status = "active"
                AND (s.slot_date > CURDATE() OR (s.slot_date = CURDATE() AND s.start_time > CURTIME()))
              ORDER BY s.slot_date, s.start_time',
            [$tutorId]
        );
    }

    // Reviews for the profile page, newest first.
    // A review is linked to a booking, the booking to a slot, and the slot to the tutor,
    // so we join through those tables to find this tutor's reviews.
    public function reviews(int $tutorId): array
    {
        return $this->fetchAll(
            'SELECT r.rating, r.comment, r.created_at, u.full_name AS reviewer, c.course_name
               FROM reviews r
               JOIN bookings b ON b.booking_id = r.booking_id
               JOIN tutor_slots s ON s.slot_id = b.slot_id
               JOIN users u ON u.user_id = b.tutee_id
               JOIN courses c ON c.course_id = s.course_id
              WHERE s.tutor_id = ?
              ORDER BY r.created_at DESC',
            [$tutorId]
        );
    }

    // "Database Systems|Computer Architecture" -> ['Database Systems', 'Computer Architecture']
    private function withCourseList(array $tutor): array
    {
        $tutor['courses'] = $tutor['courses'] === null ? [] : explode('|', $tutor['courses']);
        return $tutor;
    }

    // fetch all slots for logged-in tutor's dashboard
    public function getTutorSlots(int $tutorId): array
    {
        return $this->fetchAll(
            'SELECT s.*, c.course_code, c.course_name
               FROM tutor_slots s
               JOIN courses c ON c.course_id = s.course_id
              WHERE s.tutor_id = ?
              ORDER BY s.slot_date DESC, s.start_time ASC',
            [$tutorId]
        );
    }


    // insert new availability slot
    public function addSlot(int $tutorId, int $courseId, string $slotDate, string $startTime, string $endTime, string $mode): void
    {
        $this->execute(
            'INSERT INTO tutor_slots (tutor_id, course_id, slot_date, start_time, end_time, mode, status)
             VALUES (?, ?, ?, ?, ?, ?, "open")',
            [$tutorId, $courseId, $slotDate, $startTime, $endTime, $mode]
        );
    }

    public function getTutorBookings(int $tutorId): array
    {
        return $this->fetchAll(
            'SELECT b.*, ts.slot_date, ts.start_time, ts.end_time, ts.mode, c.course_code, s.full_name AS student_name
               FROM bookings b
               JOIN tutor_slots ts ON b.slot_id = ts.slot_id
               JOIN courses c ON ts.course_id = c.course_id
               JOIN users s ON b.tutee_id = s.user_id
              WHERE ts.tutor_id = ?
              ORDER BY ts.slot_date DESC, ts.start_time ASC',
            [$tutorId]
        );
    }
    public function updateProfile(int $tutorId, string $bio, string $experience, string $subjects = ''): void
    {
            $this->execute(
            'UPDATE tutor_profiles SET bio = ?, experience = ?, subjects = ? WHERE tutor_id = ?',
            [$bio, $experience, $subjects, $tutorId]
        );
    }

    public function updateBookingStatus(int $bookingId, string $status): void
{
    $this->execute(
        'UPDATE bookings SET status = ? WHERE booking_id = ?',
        [$status, $bookingId]
    );
}
}

