<?php

class ReportController extends Controller
{
    public function index(): void
    {
        $user = $this->requireLogin(['tutee', 'tutor']);
        
        $reportModel = new ReportModel();
        
        // Fetch only reports submitted by the current user
        $reports = $reportModel->getReportsByUser((int)$user['user_id']);

        $this->render('reports/index', [
            'pageTitle' => 'CodeMate - Reports',
            'reports'   => $reports,
            'targetTypes' => array_keys(ReportModel::TARGETS),
            'error'   => get_flash('error'),
            'success'   => get_flash('success'),
            'styles'    => ['reports'],
            'scripts'   => ['reports']
        ]);
    }

    public function store(): void
    {
        $user = $this->requireLogin(['tutee', 'tutor']);
        verify_csrf();

        $targetType = input('target_type');
        $targetId   = (int)input('target_id');
        $reason     = input('reason');
        $details    = input('details');
        $reportModel = new ReportModel();

        $error = null;
        if (!$reportModel->targetExists($targetType, $targetId)) {
            $error = "We couldn't find what you're reporting. Please check the type and ID.";
        } elseif ($targetType === 'User' && $targetId === (int)$user['user_id']) {
            $error = "You can't report yourself.";
        } elseif ($reason === '' || mb_strlen($reason) > 255) {
            $error = 'Please give a reason (up to 255 characters).';
        } elseif (mb_strlen($details) > 1000) {
            $error = 'Details can be up to 1000 characters.';
        }

        if ($error !== null) {
            flash('error', $error);
            redirect('reports');
        }

        $reportModel->createReport((int)$user['user_id'], $targetType, $targetId, $reason, $details);
        flash('success', 'Your report has been submitted to moderation.');
        redirect('reports');
    }
}
