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
            // let the applicant know the result
            (new Notification())->create($id, $decision === 'verified'
                ? 'Your tutor application was approved. You now appear in Find a Tutor, so add your tutoring slots.'
                : 'Your tutor application was not approved. Please contact an admin if you have questions.',
                $decision === 'verified' ? 'tutor-slots' : 'dashboard');
            flash('success', $decision === 'verified' ? 'Tutor application approved.' : 'Tutor application rejected.');
        } else {
            flash('error', 'This application is no longer pending or could not be updated.');
        }
        redirect('admin-verify-tutors');
    }

    // GET admin-reports (optionally &status=pending|resolved|dismissed|all; pending by default,
    // so a report disappears from the main list once it has been resolved or dismissed)
    public function reports(): void {
        $this->requireLogin(['admin']);
        $status = $_GET['status'] ?? 'pending';
        if (!in_array($status, ReportModel::FILTERS, true)) {
            $status = 'pending';
        }
        $reports = new ReportModel();
        $this->render('admin/reports', [
            'pageTitle' => 'CodeMate - Admin Reports',
            'reports'   => $reports->getAllReports($status),
            'counts'    => $reports->countsByStatus(),
            'status'    => $status,
            'styles'    => ['role-pages'],
        ]);
    }

    // POST admin-report-update: the Resolve / Dismiss / Reopen buttons on the Reports page.
    // Resolve acts on what was reported (see resolveReport); Dismiss and Reopen only change the report.
    public function updateReport(): void {
        $admin = $this->requireLogin(['admin']);
        $reports = new ReportModel();
        $report = $reports->find((int)input('report_id'));
        $status = input('status');
        $filter = input('filter');

        if ($report === null || !in_array($status, ReportModel::STATUSES, true)) {
            flash('error', 'That report could not be updated.');
        } elseif ($status === 'resolved' && $report['status'] === 'pending') {
            flash('success', $this->resolveReport($report, (int)$admin['user_id']));
        } else {
            $reports->updateStatus((int)$report['id'], $status, (int)$admin['user_id']);
            if ($status === 'dismissed' && $report['status'] !== 'dismissed') {
                (new Notification())->create((int)$report['reporter_id'],
                    "Your report about {$report['target']} was reviewed. No action was needed.", 'reports');
            }
            flash('success', $status === 'pending'
                ? 'Report reopened. Content that was removed stays removed; restore it in Content Moderation or User Management if needed.'
                : 'Report dismissed.');
        }
        // stay on the same filter the admin was looking at
        redirect('admin-reports', in_array($filter, ReportModel::FILTERS, true) ? ['status' => $filter] : []);
    }

    // Resolving a report removes what was reported:
    //   Discussion / Reply -> hidden from the site (same as Hide in Content Moderation)
    //   Resource           -> rejected, so only the uploader and admins can see it
    //   User               -> account deactivated (can't log in, hidden from Find a Tutor)
    // Every pending report about the same item is closed too, the author is told their post was
    // removed, and everyone who reported it is told it was resolved. Returns the flash message.
    private function resolveReport(array $report, int $adminId): string {
        $type = $report['target_type'];
        $id = (int)$report['target_id'];
        $reports = new ReportModel();
        $notifications = new Notification();
        $reporterIds = $reports->pendingReporterIds($type, $id);

        if (in_array($type, ['Discussion', 'Reply', 'Resource'], true)) {
            $moderation = new ModerationModel();
            $owner = $moderation->ownerOf($type, $id);
            $moderation->updateStatus($type, $id, 'hidden', $adminId);
            $what = ['Discussion' => 'discussion', 'Reply' => 'reply', 'Resource' => 'resource'][$type];
            if ($owner !== null) {
                $notifications->create((int)$owner['user_id'],
                    $type === 'Reply'
                        ? "Your reply in \"{$owner['title']}\" was removed by a moderator after a report."
                        : "Your {$what} \"{$owner['title']}\" was removed by a moderator after a report.",
                    $type === 'Resource' ? 'resources' : 'discussions');
            }
            $result = "the {$what} was removed";
        } elseif ($type === 'User') {
            // setAccountStatus() never changes admins
            $deactivated = (new User())->setAccountStatus($id, 'deactivated');
            $result = $deactivated ? 'the account was deactivated' : 'it was reviewed';
        } else {
            $result = 'it was reviewed';
        }

        $reports->closeAllFor($type, $id, 'resolved', $adminId);
        foreach ($reporterIds as $reporterId) {
            $notifications->create($reporterId, "Your report about {$report['target']} was resolved: {$result}.", 'reports');
        }
        $closed = count($reporterIds);
        return 'Report resolved: ' . $result . '.' . ($closed > 1 ? " $closed reports about it were closed." : '');
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
        $type = input('item_type');
        $id = (int)input('item_id');
        $status = input('status');
        $filter = input('filter');
        $moderation = new ModerationModel();

        // read these before the update, since it closes the pending reports
        $owner = $moderation->ownerOf($type, $id);
        $reporterIds = $type === 'Resource' ? [] : (new ReportModel())->pendingReporterIds($type, $id);

        if ($moderation->updateStatus($type, $id, $status, (int)$admin['user_id'])) {
            $this->notifyModeration($type, $status, $owner, $reporterIds);
            $done = ['kept' => 'kept', 'hidden' => 'hidden', 'pending' => 'moved back to pending'][$status];
            flash('success', "Item $done.");
        } else {
            flash('error', 'That item could not be updated.');
        }
        // go back to the same filter the admin was looking at
        redirect('admin-moderation', in_array($filter, ModerationModel::FILTERS, true) ? ['filter' => $filter] : []);
    }

    // Tells the author what happened to their post or upload, and tells the people who
    // reported it that their report was closed. Undo (back to pending) sends nothing.
    private function notifyModeration(string $type, string $status, ?array $owner, array $reporterIds): void {
        if ($owner === null || $status === 'pending') {
            return;
        }
        $notifications = new Notification();
        $title = $owner['title'];

        if ($type === 'Resource') {
            $notifications->create((int)$owner['user_id'], $status === 'kept'
                ? "Your resource \"{$title}\" was approved and is now visible to everyone."
                : "Your resource \"{$title}\" was not approved by a moderator.", 'resources');
            return;
        }

        $what = $type === 'Discussion' ? "Your discussion \"{$title}\"" : "Your reply in \"{$title}\"";
        if ($status === 'hidden') {
            $notifications->create((int)$owner['user_id'], "$what was removed by a moderator.", 'discussions');
        }
        $result = $status === 'hidden' ? 'resolved: the post was removed' : 'reviewed: the post was kept';
        foreach ($reporterIds as $reporterId) {
            $notifications->create($reporterId, "Your report about \"{$title}\" was {$result}.", 'reports');
        }
    }
}
