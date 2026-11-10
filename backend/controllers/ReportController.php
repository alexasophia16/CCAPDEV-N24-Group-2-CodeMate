<?php

class ReportController extends Controller
{
    public function index(): void
    {
        $user = $this->requireLogin(['tutee', 'tutor']);
        
        global $pdo;
        $reportModel = new ReportModel($pdo);
        
        // Fetch only reports submitted by the current user
        $reports = $reportModel->getReportsByUser((int)$user['user_id']);

        $this->render('reports', [
            'pageTitle' => 'CodeMate - Reports',
            'reports'   => $reports,
            'error'     => get_flash('error'),
            'success'   => get_flash('success'),
            'styles'    => ['reports'],
            'scripts'   => ['reports']
        ]);
    }

    public function store(): void
    {
        $user = $this->requireLogin(['tutee', 'tutor']);
        verify_csrf();

        $target = trim((string)input('target'));
        $reason = trim((string)input('reason'));

        if ($target === '' || $reason === '') {
            flash('error', 'Please provide a target and a reason for the report.');
            redirect('reports');
        }

        global $pdo;
        $reportModel = new ReportModel($pdo);
        
        try {
            $reportModel->createReport((int)$user['user_id'], $target, $reason);
            flash('success', 'Your report has been submitted to moderation.');
        } catch (Exception $e) {
            flash('error', 'Could not file report: ' . $e->getMessage());
        }

        redirect('reports');
    }
}
