<?php

class DashboardController extends Controller
{
    public function index(): void
    {
        $user = $this->requireLogin();

        $this->render('dashboard/index', [
            'pageTitle' => 'CodeMate - Dashboard',
            'user'      => $user,
            'styles'    => ['dashboard', 'role-pages'],
        ]);
    }
}
