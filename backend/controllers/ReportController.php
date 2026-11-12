<?php

class ReportController extends Controller
{
    // Pages that have Report buttons, and the one URL parameter each needs.
    // After a report is filed the user is sent back to the page they were on.
    private const BACK_PAGES = [
        'tutor-profile'      => 'id',
        'discussions-thread' => 'id',
        'resources'          => null,
        'messages'           => 'c',
    ];

    // GET reports: the reports you have filed
    public function index(): void
    {
        $user = $this->requireLogin(['tutee', 'tutor']);

        $this->render('reports/index', [
            'pageTitle' => 'CodeMate - Reports',
            'reports'   => (new ReportModel())->getReportsByUser((int)$user['user_id']),
            'styles'    => ['reports'],
        ]);
    }

    // POST store-report: sent by the shared report pop-up (partials/report-modal.php)
    public function store(): void
    {
        $user = $this->requireLogin(['tutee', 'tutor']);

        $targetType = input('target_type');
        $targetId   = (int)input('target_id');
        $reason     = input('reason');
        $details    = input('details');
        $reportModel = new ReportModel();

        // target_type and target_id come from hidden inputs, which can be edited,
        // so everything is checked again here
        $error = null;
        if (!$reportModel->targetExists($targetType, $targetId)) {
            $error = "We couldn't find what you're reporting.";
        } elseif ($targetType === 'User' && $targetId === (int)$user['user_id']) {
            $error = "You can't report yourself.";
        } elseif ($reason === '' || mb_strlen($reason) > 255) {
            $error = 'Please give a reason (up to 255 characters).';
        } elseif (mb_strlen($details) > 1000) {
            $error = 'Details can be up to 1000 characters.';
        }

        if ($error !== null) {
            flash('error', $error);
        } else {
            $reportModel->createReport((int)$user['user_id'], $targetType, $targetId, $reason, $details);
            flash('success', 'Thanks. Your report was sent to the moderators. You can follow it on the Reports page.');
        }
        $this->redirectBack(input('back'));
    }

    // "back" is the query string of the page the pop-up was on, e.g. "page=tutor-profile&id=2".
    // Only pages from BACK_PAGES with a numeric id are allowed, so it can't redirect anywhere else.
    private function redirectBack(string $back): void
    {
        parse_str($back, $query);
        $page = (string)($query['page'] ?? '');

        if (array_key_exists($page, self::BACK_PAGES)) {
            $param = self::BACK_PAGES[$page];
            if ($param === null) {
                redirect($page);
            }
            if (ctype_digit((string)($query[$param] ?? ''))) {
                redirect($page, [$param => (int)$query[$param]]);
            }
        }
        redirect('reports');
    }
}
