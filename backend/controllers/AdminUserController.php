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
        verify_csrf();
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
            'styles'=>['role-pages']
        ]);
    }
    public function reviewTutor(): void {
        $this->requireLogin(['admin']);
        verify_csrf();
        $id = (int)($_POST['tutor_id'] ?? 0);
        $decision = input('decision');
        if ((new TutorVerification())->review($id, $decision)) {
            flash('success', $decision === 'verified' ? 'Tutor application approved.' : 'Tutor application rejected.');
        } else {
            flash('error', 'This application is no longer pending or could not be updated.');
        }
        redirect('admin-verify-tutors');
    }
}
