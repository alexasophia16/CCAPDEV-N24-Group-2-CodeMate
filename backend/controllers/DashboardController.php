<?php
class DashboardController extends Controller
{
    public function index(): void {
        $user = $this->requireLogin();
        $this->render('dashboard/index', [
            'pageTitle'=>'CodeMate - Dashboard',
            'user'=>$user,
            'stats'=>(new User())->dashboardStats($user['role'], (int)$user['user_id']),
            'styles'=>['dashboard', 'role-pages']
        ]);
    }
}
