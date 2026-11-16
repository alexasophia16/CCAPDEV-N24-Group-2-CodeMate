<?php

// The tutor's own pages: My Tutor Profile, Tutoring Slots, Booking Requests and Reviews.
// Every method requires the 'tutor' role, and every query uses the logged-in tutor's id,
// so a tutor can only see and change their own profile, slots and bookings.
class TutorAreaController extends Controller
{
    // GET tutor-profile-manage: bio, experience, subjects and verification status
    public function manageProfile(): void
    {
        $this->requireLogin(['tutor']);
        
        $userId = $_SESSION['user_id'];
        $db = Database::connection();

        $stmt = $db->prepare("
            SELECT u.full_name, u.account_status, tp.bio, tp.experience, tp.verification_status,tp.subjects 
            FROM users u 
            JOIN tutor_profiles tp ON u.user_id = tp.tutor_id 
            WHERE u.user_id = ?
        ");
        $stmt->execute([$userId]);
        $profile = $stmt->fetch();

        $this->render('tutors/tutor-profile-manage', [
            'profile' => $profile,
            'styles'  => ['role-pages']
        ]);
    }

    // POST tutor-profile-manage: save the profile form (lengths checked on the server)
    public function updateProfile(): void
    {
        $user = $this->requireLogin(['tutor']);
        $bio = input('bio');
        $experience = input('experience');
        $subjects = input('subjects');

        if (mb_strlen($bio) > 1000 || mb_strlen($experience) > 1000) {
            flash('error', 'Bio and experience can be up to 1000 characters each.');
            redirect('tutor-profile-manage');
        }
        if (mb_strlen($subjects) > 255) {
            flash('error', 'Subjects can be up to 255 characters.');
            redirect('tutor-profile-manage');
        }

        (new Tutor())->updateProfile((int) $user['user_id'], $bio, $experience, $subjects);
        flash('success', 'Profile saved.');
        redirect('tutor-profile-manage');
    }

    // GET tutor-slots: the tutor's slots and the "add slot" form
    public function slots(): void
    {
        $user = $this->requireLogin(['tutor']);

        $this->render('tutors/tutor-slots', [
            'slots'   => (new Tutor())->getTutorSlots((int) $user['user_id']),
            // archived courses can't be booked, so they aren't offered for new slots
            'courses' => (new Course())->active(),
            'styles'  => ['role-pages']
        ]);
    }

    // POST tutor-slots: add a slot after checking course, date, times, mode and overlaps
    public function addSlot(): void
    {
        $user = $this->requireLogin(['tutor']);
        $tutorId = (int) $user['user_id'];
        $courseId = (int) ($_POST['course_id'] ?? 0);
        $date = input('slot_date');
        $start = input('start_time');
        $end = input('end_time');
        $mode = input('mode');
        $tutors = new Tutor();

        // the browser checks these too, but the form can be edited, so check again here
        $course = (new Course())->find($courseId);
        $validDate = DateTime::createFromFormat('Y-m-d', $date);
        $validTimes = preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $start) && preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $end);

        $error = null;
        if ($course === null || $course['status'] !== 'active') {
            $error = 'Please choose an active course.';
        } elseif (!$validDate || $validDate->format('Y-m-d') !== $date || !$validTimes) {
            $error = 'Please enter a valid date and time.';
        } elseif (strtotime("$date $start") <= time()) {
            $error = 'The slot has to be in the future.';
        } elseif (strtotime("$date $end") <= strtotime("$date $start")) {
            $error = 'The end time must be after the start time.';
        } elseif (!in_array($mode, ['Online', 'Face-to-face'], true)) {
            $error = 'Please choose Online or Face-to-face.';
        } elseif ($tutors->hasOverlappingSlot($tutorId, $date, $start, $end)) {
            $error = 'You already have a slot that overlaps this time.';
        }

        if ($error !== null) {
            flash('error', $error);
            redirect('tutor-slots');
        }

        $tutors->addSlot($tutorId, $courseId, $date, $start, $end, $mode);
        flash('success', 'Slot added.');
        redirect('tutor-slots');
    }

    public function deleteSlot(): void
    {
        $user = $this->requireLogin(['tutor']);

        // only your own slots, and only while they're still open (deleting a booked slot
        // would also delete its booking, because of ON DELETE CASCADE)
        if ((new Tutor())->deleteSlot((int) ($_POST['slot_id'] ?? 0), (int) $user['user_id'])) {
            flash('success', 'Slot deleted.');
        } else {
            flash('error', 'Only your own open slots can be deleted.');
        }
        redirect('tutor-slots');
    }

    // GET tutor-bookings: bookings on this tutor's slots, with the student's name
    public function bookingRequests(): void
    {
        $this->requireLogin(['tutor']);
        
        $tutorModel = new Tutor();
        $bookings = $tutorModel->getTutorBookings($_SESSION['user_id']);

        $this->render('tutors/tutor-bookings', [
            'bookings' => $bookings,
            'styles'   => ['role-pages']
        ]);
    }

    // POST tutor-booking-update: "Mark completed" on a confirmed session; the tutee is notified
    public function updateBooking(): void
    {
        $user = $this->requireLogin(['tutor']);

        // accepting/declining is done by the admin now; tutors can only mark their own
        // confirmed sessions completed
        $tutors = new Tutor();
        $bookingId = (int) ($_POST['booking_id'] ?? 0);
        if ($tutors->markBookingCompleted($bookingId, (int) $user['user_id'])) {
            // let the tutee know they can review the session now
            $booking = $tutors->bookingSummary($bookingId, (int) $user['user_id']);
            (new Notification())->create((int) $booking['tutee_id'],
                "Your {$booking['course_code']} session with {$booking['tutor_name']} is completed. You can now leave a review.",
                'my-bookings');
            flash('success', 'Session marked as completed. The tutee can now leave a review.');
        } else {
            flash('error', 'Only your own confirmed bookings can be marked completed.');
        }
        redirect('tutor-bookings');
    }

    // GET tutor-reviews: reviews left for this tutor; the average is worked out here in PHP
    public function reviews(): void
    {
        $this->requireLogin(['tutor']);
        
        $tutorModel = new Tutor();
        $reviews = $tutorModel->reviews($_SESSION['user_id']);
        
        // calculate average rating 
        $totalReviews = count($reviews);
        $averageRating = 0;
        if ($totalReviews > 0) {
            $sum = array_sum(array_column($reviews, 'rating'));
            $averageRating = round($sum / $totalReviews, 1);
        }

        $this->render('tutors/tutor-reviews', [
            'reviews' => $reviews,
            'totalReviews' => $totalReviews,
            'averageRating' => $averageRating,
            'styles'        => ['role-pages']
        ]);
    }
}