<?php

class BookingController extends Controller
{
    public function create(): void
    {
        $this->requireLogin(['tutee']);
        $this->placeholder('Book a Consultation', 'book-consultation.html');
    }

    public function myBookings(): void
    {
        $this->requireLogin(['tutee']);
        $this->placeholder('My Bookings', 'my-bookings.html');
    }
}
