<?php
class AdminUserController extends Controller
{
    public function users(): void {
        $this->requireLogin(['admin']);
        $this->render('admin/users', [
            'pageTitle'=>'CodeMate - User Management',
            'users'=>(new User())->all(),
            'styles'=>['role-pages']
        ]);
    }
    public function updateStatus(): void {
        $admin = $this->requireLogin(['admin']);
        verify_csrf();
        $userId = (int)($_POST['user_id'] ?? 0);
        $status = input('status');
        if ($userId === (int)$admin['user_id']) {
            flash('error', 'You cannot change your own account status.');
        } elseif ((new User())->setAccountStatus($userId, $status)) {
            flash('success', 'User account status updated.');
        } else {
            flash('error', 'No account was updated. Check the selected user and status.');
        }
        redirect('admin-users');
    }
    public function verifyTutors(): void {
        $this->requireLogin(['admin']);
        $this->render('admin/verify-tutors', [
            'pageTitle'=>'CodeMate - Verify Tutors',
            'tutors'=>(new TutorVerification())->pending(),
            'bookings'=>(new Booking())->pendingForAdmin(),
            'styles'=>['role-pages']
        ]);
    }
    // POST admin-review-booking: approve (confirmed) or decline (declined) a tutee's booking request
    public function reviewBooking(): void {
        $this->requireLogin(['admin']);
        verify_csrf();
        $decision = input('decision');
        $booking = (new Booking())->review((int)($_POST['booking_id'] ?? 0), $decision);

        if ($booking === null) {
            flash('error', 'This booking is no longer pending, or it can no longer be approved (the schedule may have passed).');
            redirect('admin-verify-tutors');
        }

        $when = date('M j, Y', strtotime($booking['slot_date'])) . ' at ' . date('g:i A', strtotime($booking['start_time']));
        $notifications = new Notification();
        if ($decision === 'confirmed') {
            $notifications->create((int)$booking['tutee_id'],
                "Your {$booking['course_code']} booking with {$booking['tutor_name']} on {$when} was approved.", 'my-bookings');
            $notifications->create((int)$booking['tutor_id'],
                "{$booking['tutee_name']}'s {$booking['course_code']} booking on {$when} is confirmed.", 'tutor-bookings');
            flash('success', 'Booking approved. The tutee and tutor have been notified.');
        } else {
            $notifications->create((int)$booking['tutee_id'],
                "Your {$booking['course_code']} booking with {$booking['tutor_name']} on {$when} was declined.", 'my-bookings');
            flash('success', 'Booking declined. The tutee has been notified and the schedule is open again.');
        }
        redirect('admin-verify-tutors');
    }
    public function reviewTutor(): void {
        $this->requireLogin(['admin']);
        verify_csrf();
        $id = (int)($_POST['tutor_id'] ?? 0);
        $decision = input('decision');
        if ((new TutorVerification())->review($id, $decision)) {
            flash('success', $decision === 'verified' ? 'Tutor application approved.' : 'Tutor application rejected.');
        } else {
            flash('error', 'This application is no longer pending or could not be updated.');
        }
        redirect('admin-verify-tutors');
    }

    public function reports(): void {
        $this->requireLogin(['admin']);
        
        
        $reportModel = new ReportModel();
        
        $this->render('admin/reports', [
            'pageTitle' => 'CodeMate - Admin Reports',
            'reports'   => $reportModel->getAllReports(),
            'styles'    => ['role-pages'],
            'success'   => get_flash('success'),
            'error'     => get_flash('error')
        ]);
    }

    public function updateReport(): void {
        $this->requireLogin(['admin']);
        verify_csrf();

        $reportId = (int)input('report_id');
        $status = input('status');

        
        $reportModel = new ReportModel();

        if ($reportModel->updateStatus($reportId, $status)) {
            flash('success', 'Report status updated successfully.');
        } else {
            flash('error', 'Failed to update report status.');
        }

        redirect('admin-reports');
    }

    public function moderation(): void {
        $this->requireLogin(['admin']);
        
        $filter = $_GET['filter'] ?? 'pending';
        $moderationModel = new ModerationModel();
        
        $this->render('admin/moderation', [
            'pageTitle' => 'CodeMate - Content Moderation',
            'items'     => $moderationModel->getItemsByStatus($filter),
            'filter'    => $filter,
            'styles'    => ['role-pages'],
            'success'   => get_flash('success'),
            'error'     => get_flash('error')
        ]);
    }

    public function updateModeration(): void {
        $this->requireLogin(['admin']);
        verify_csrf();

        $id = (int)input('item_id');
        $type = input('item_type'); // 'Resource', 'Discussion', or 'Reply'
        $status = input('status');  // 'kept', 'hidden', or 'pending'

        $moderationModel = new ModerationModel();

        if ($moderationModel->updateStatus($type, $id, $status)) {
            flash('success', "Item successfully marked as {$status}.");
        } else {
            flash('error', 'Failed to update moderation status.');
        }

        redirect('admin-moderation');
    }
}
