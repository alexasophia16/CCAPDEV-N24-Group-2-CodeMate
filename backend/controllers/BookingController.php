<?php

// Booking flow for tutees: Book a Consultation, My Bookings, cancelling and reviewing.
// Every method requires the 'tutee' role, so tutors and admins get "Access denied".
//
// Pattern used by all the POST methods below:
//   1. requireLogin()  - make sure the right kind of user is logged in
//   2. validate input  - on a problem: flash() an error and redirect() back
//   3. save to the database through the model
//   4. flash() a success message and redirect() (so refreshing doesn't submit twice)
class BookingController extends Controller
{
    // GET book-consultation&tutor=2 (optionally &slot=12 to pre-select a schedule)
    public function create(): void
    {
        $this->requireLogin(['tutee']);
        $tutorId = (int) ($_GET['tutor'] ?? 0);
        $tutors = new Tutor();
        $tutor = $tutors->find($tutorId);

        if ($tutor === null) {
            flash('error', 'That tutor could not be found.');
            redirect('find-tutor');
        }

        $this->render('bookings/create', [
            'pageTitle'    => 'CodeMate - Book a Consultation',
            'tutor'        => $tutor,
            'slots'        => $tutors->openSlots($tutorId),
            'selectedSlot' => (int) ($_GET['slot'] ?? 0),
            'activePage'   => 'book-consultation',
            'styles'       => ['book-consultation'],
        ]);
    }

    // POST book-consultation: the booking form was submitted
    public function store(): void
    {
        $user = $this->requireLogin(['tutee']);
        $tutorId = (int) ($_POST['tutor_id'] ?? 0);
        $slotId = (int) ($_POST['slot_id'] ?? 0);
        $notes = input('notes');

        if ($slotId === 0) {
            flash('error', 'Please choose a schedule.');
            redirect('book-consultation', ['tutor' => $tutorId]);
        }
        // the textarea also has maxlength="500", but the browser check can be bypassed,
        // so the server checks again
        if (mb_strlen($notes) > 500) {
            flash('error', 'Notes can be up to 500 characters.');
            redirect('book-consultation', ['tutor' => $tutorId, 'slot' => $slotId]);
        }

        // null means someone else booked the slot first (or it's no longer open)
        $bookingId = (new Booking())->create($slotId, (int) $user['user_id'], $notes);
        if ($bookingId === null) {
            flash('error', 'Sorry, that schedule was just taken or is no longer available. Please pick another one.');
            redirect('book-consultation', ['tutor' => $tutorId]);
        }

        // The booking is saved as "pending". It has to be approved by an admin first, so the
        // admins are notified (the notification links to their Verify Tutors page).
        // The tutor and the tutee are notified once the admin decides.
        $booking = (new Booking())->findForTutee($bookingId, (int) $user['user_id']);
        (new Notification())->notifyAdmins(
            "New booking request from {$user['full_name']} for {$booking['course_code']} needs approval.",
            'admin-verify-tutors'
        );

        flash('success', 'Booking request sent! It will show as confirmed once an admin approves it.');
        redirect('my-bookings');
    }

    // GET my-bookings (optionally &status=pending etc. from the "Show" filter)
    public function myBookings(): void
    {
        $user = $this->requireLogin(['tutee']);
        $status = (string) ($_GET['status'] ?? '');

        $this->render('bookings/index', [
            'pageTitle' => 'CodeMate - My Bookings',
            'bookings'  => (new Booking())->forTutee((int) $user['user_id'], $status),
            // unknown status values are treated as "All bookings"
            'status'    => in_array($status, Booking::STATUSES, true) ? $status : '',
            'styles'    => ['my-bookings'],
            'scripts'   => ['my-bookings'],
        ]);
    }

    // POST booking-cancel
    public function cancel(): void
    {
        $user = $this->requireLogin(['tutee']);
        $bookings = new Booking();
        // findForTutee() returns null for other people's bookings
        $booking = $bookings->findForTutee((int) ($_POST['booking_id'] ?? 0), (int) $user['user_id']);

        // only upcoming pending/confirmed bookings can be cancelled
        // (not past sessions, and not ones already cancelled, declined or completed)
        if ($booking === null || !$booking['is_upcoming'] || !in_array($booking['status'], ['pending', 'confirmed'], true)) {
            flash('error', 'Only upcoming pending or confirmed bookings can be cancelled.');
            redirect('my-bookings');
        }

        $bookings->cancel((int) $booking['booking_id'], (int) $booking['slot_id']);
        (new Notification())->create(
            (int) $booking['tutor_id'],
            "{$user['full_name']} cancelled their {$booking['course_code']} booking.",
            'tutor-bookings'
        );

        flash('success', 'Booking cancelled.');
        redirect('my-bookings');
    }

    // POST booking-review: the "Leave a review" form on a completed booking
    public function review(): void
    {
        $user = $this->requireLogin(['tutee']);
        $bookings = new Booking();
        $booking = $bookings->findForTutee((int) ($_POST['booking_id'] ?? 0), (int) $user['user_id']);
        $rating = (int) ($_POST['rating'] ?? 0);
        $comment = input('comment');

        // must be your own booking, completed, and not reviewed yet
        if ($booking === null || $booking['status'] !== 'completed' || $booking['has_review']) {
            flash('error', 'You can only review a completed session once.');
            redirect('my-bookings');
        }
        if ($rating < 1 || $rating > 5) {
            flash('error', 'Please choose a rating from 1 to 5 stars.');
            redirect('my-bookings');
        }
        if (mb_strlen($comment) > 1000) {
            flash('error', 'Reviews can be up to 1000 characters.');
            redirect('my-bookings');
        }

        $bookings->addReview((int) $booking['booking_id'], $rating, $comment);
        (new Notification())->create(
            (int) $booking['tutor_id'],
            "{$user['full_name']} left you a {$rating}-star review.",
            'tutor-reviews'
        );

        flash('success', 'Thanks for your review!');
        redirect('my-bookings');
    }
}
