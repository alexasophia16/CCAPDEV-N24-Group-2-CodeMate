<?php

class TutorAreaController extends Controller
{
    public function manageProfile(): void
    {
        $this->requireLogin(['tutor']);
        
        $userId = $_SESSION['user_id'];
        $db = Database::connection();

        $stmt = $db->prepare("
            SELECT u.full_name, u.account_status, tp.bio, tp.experience, tp.verification_status 
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

    public function updateProfile(): void
    {
        $this->requireLogin(['tutor']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $tutorModel = new Tutor();
            $tutorModel->updateProfile(
                $_SESSION['user_id'],
                $_POST['bio'] ?? '',
                $_POST['experience'] ?? '',
                $_POST['subjects'] ?? ''
            );
        }

        header('Location: index.php?page=tutor-profile-manage');
        exit;
    }

    public function slots(): void
    {
        $this->requireLogin(['tutor']);
        
        $tutorModel = new Tutor();
        $slots = $tutorModel->getTutorSlots($_SESSION['user_id']);
        
        // fetch courses for the add slot dropdown 
        $courseModel = new Course(); 
        $courses = $courseModel->all();
        
        $this->render('tutors/tutor-slots', [
            'slots' => $slots,
            'courses' => $courses,
            'styles'  => ['role-pages']
        ]);
    }

public function addSlot(): void
{
    $this->requireLogin(['tutor']);
    try {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $tutorModel = new Tutor();
            $mode = $_POST['mode'] ?? 'Online'; 
            
            $tutorModel->addSlot(
                $_SESSION['user_id'],
                (int)($_POST['course_id'] ?? 0),
                $_POST['slot_date'] ?? '',
                $_POST['start_time'] ?? '',
                $_POST['end_time'] ?? '',
                $mode
            );
        }
        header('Location: index.php?page=tutor-slots');
        exit;
    } catch (\Throwable $e) {
        die("<div style='padding:20px; background:#fee2e2; color:#991b1b;'><b>Slot Error:</b> " . $e->getMessage() . " on line " . $e->getLine() . "</div>");
    }
}

    public function deleteSlot(): void 
    {
        $this->requireLogin(['tutor']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['slot_id'])) {
            $tutor = new Tutor();
            $tutor->execute('DELETE FROM tutor_slots WHERE slot_id = ?', [$_POST['slot_id']]);
        }
        header('Location: index.php?page=tutor-slots');
        exit;
    }

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

public function updateBooking(): void
{
    $this->requireLogin(['tutor']);
    try {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'])) {
            $tutorModel = new Tutor();
            $status = $_POST['status'] ?? 'completed';
            
            $tutorModel->updateBookingStatus((int)$_POST['booking_id'], $status);
        }
        header('Location: index.php?page=tutor-bookings');
        exit;
    } catch (\Throwable $e) {
        die("<div style='padding:20px; background:#fee2e2; color:#991b1b;'><b>Booking Error:</b> " . $e->getMessage() . " on line " . $e->getLine() . "</div>");
    }
}
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