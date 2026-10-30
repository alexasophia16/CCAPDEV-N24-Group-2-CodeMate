<?php

class ReportController extends Controller
{
    public function index(): void
    {
        $this->requireLogin(['tutee', 'tutor']);
        $this->placeholder('Reports', 'reports.html');
    }
}
