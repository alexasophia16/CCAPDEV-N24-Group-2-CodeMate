<?php

class BookingController extends Controller
{
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

    public function store(): void
    {
        $user = $this->requireLogin(['tutee']);
        verify_csrf();
        $tutorId = (int) ($_POST['tutor_id'] ?? 0);
        $slotId = (int) ($_POST['slot_id'] ?? 0);
        $notes = input('notes');

        if ($slotId === 0) {
            flash('error', 'Please choose a schedule.');
            redirect('book-consultation', ['tutor' => $tutorId]);
        }
        if (mb_strlen($notes) > 500) {
            flash('error', 'Notes can be up to 500 characters.');
            redirect('book-consultation', ['tutor' => $tutorId, 'slot' => $slotId]);
        }

        $bookingId = (new Booking())->create($slotId, (int) $user['user_id'], $notes);
        if ($bookingId === null) {
            flash('error', 'Sorry, that schedule was just taken or is no longer available. Please pick another one.');
            redirect('book-consultation', ['tutor' => $tutorId]);
        }

        $booking = (new Booking())->findForTutee($bookingId, (int) $user['user_id']);
        (new Notification())->create(
            (int) $booking['tutor_id'],
            "New booking request from {$user['full_name']} for {$booking['course_code']}.",
            'tutor-bookings'
        );

        flash('success', 'Booking request sent! The tutor will confirm it soon.');
        redirect('my-bookings');
    }

    public function myBookings(): void
    {
        $user = $this->requireLogin(['tutee']);
        $status = (string) ($_GET['status'] ?? '');

        $this->render('bookings/index', [
            'pageTitle' => 'CodeMate - My Bookings',
            'bookings'  => (new Booking())->forTutee((int) $user['user_id'], $status),
            'status'    => in_array($status, Booking::STATUSES, true) ? $status : '',
            'styles'    => ['my-bookings'],
            'scripts'   => ['my-bookings'],
        ]);
    }

    public function cancel(): void
    {
        $user = $this->requireLogin(['tutee']);
        verify_csrf();
        $bookings = new Booking();
        $booking = $bookings->findForTutee((int) ($_POST['booking_id'] ?? 0), (int) $user['user_id']);

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

    public function review(): void
    {
        $user = $this->requireLogin(['tutee']);
        verify_csrf();
        $bookings = new Booking();
        $booking = $bookings->findForTutee((int) ($_POST['booking_id'] ?? 0), (int) $user['user_id']);
        $rating = (int) ($_POST['rating'] ?? 0);
        $comment = input('comment');

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
