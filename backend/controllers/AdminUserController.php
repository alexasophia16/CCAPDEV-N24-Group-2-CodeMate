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
        $id = (int)($_POST['tutor_id'] ?? 0);
        $decision = input('decision');
        if ((new TutorVerification())->review($id, $decision)) {
            flash('success', $decision === 'verified' ? 'Tutor application approved.' : 'Tutor application rejected.');
        } else {
            flash('error', 'This application is no longer pending or could not be updated.');
        }
        redirect('admin-verify-tutors');
    }

    // GET admin-reports: every report from tutees and tutors, newest first
    public function reports(): void {
        $this->requireLogin(['admin']);
        $this->render('admin/reports', [
            'pageTitle' => 'CodeMate - Admin Reports',
            'reports'   => (new ReportModel())->getAllReports(),
            'statuses'  => ReportModel::STATUSES,
            'styles'    => ['role-pages'],
        ]);
    }

    // POST admin-report-update: the status dropdown in the Reports table
    public function updateReport(): void {
        $admin = $this->requireLogin(['admin']);

        if ((new ReportModel())->updateStatus((int)input('report_id'), input('status'), (int)$admin['user_id'])) {
            flash('success', 'Report status updated.');
        } else {
            flash('error', 'That report could not be updated.');
        }
        redirect('admin-reports');
    }

    // GET admin-moderation (optionally &filter=pending|kept|hidden|all)
    public function moderation(): void {
        $this->requireLogin(['admin']);
        $filter = $_GET['filter'] ?? 'pending';
        if (!in_array($filter, ModerationModel::FILTERS, true)) {
            $filter = 'pending';
        }
        $this->render('admin/moderation', [
            'pageTitle' => 'CodeMate - Content Moderation',
            'items'     => (new ModerationModel())->getItemsByStatus($filter),
            'filter'    => $filter,
            'styles'    => ['role-pages'],
        ]);
    }

    // POST admin-moderation-update: Approve/Keep, Hide, or Undo on a queue item
    public function updateModeration(): void {
        $admin = $this->requireLogin(['admin']);
        $status = input('status');
        $filter = input('filter');

        if ((new ModerationModel())->updateStatus(input('item_type'), (int)input('item_id'), $status, (int)$admin['user_id'])) {
            $done = ['kept' => 'kept', 'hidden' => 'hidden', 'pending' => 'moved back to pending'][$status];
            flash('success', "Item $done.");
        } else {
            flash('error', 'That item could not be updated.');
        }
        // go back to the same filter the admin was looking at
        redirect('admin-moderation', in_array($filter, ModerationModel::FILTERS, true) ? ['filter' => $filter] : []);
    }
}
