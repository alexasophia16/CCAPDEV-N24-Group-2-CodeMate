<?php

class TutorAreaController extends Controller
{
    public function manageProfile(): void
    {
        $this->requireLogin(['tutor']);
        $this->placeholder('My Tutor Profile', 'tutor-profile-manage.html');
    }

    public function slots(): void
    {
        $this->requireLogin(['tutor']);
        $this->placeholder('Tutoring Slots', 'tutor-slots.html');
    }

    public function bookingRequests(): void
    {
        $this->requireLogin(['tutor']);
        $this->placeholder('Booking Requests', 'tutor-bookings.html');
    }

    public function reviews(): void
    {
        $this->requireLogin(['tutor']);
        $this->placeholder('Reviews', 'tutor-reviews.html');
    }
}
