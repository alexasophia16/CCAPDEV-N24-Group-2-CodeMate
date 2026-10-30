<?php

class ModerationController extends Controller
{
    public function moderation(): void
    {
        $this->requireLogin(['admin']);
        $this->placeholder('Content Moderation', 'admin-moderation.html');
    }

    public function reports(): void
    {
        $this->requireLogin(['admin']);
        $this->placeholder('Reports', 'admin-reports.html');
    }
}
