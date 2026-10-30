<?php

class AdminUserController extends Controller
{
    public function users(): void
    {
        $this->requireLogin(['admin']);
        $this->placeholder('User Management', 'admin-users.html');
    }

    public function verifyTutors(): void
    {
        $this->requireLogin(['admin']);
        $this->placeholder('Verify Tutors', 'admin-verify-tutors.html');
    }
}
