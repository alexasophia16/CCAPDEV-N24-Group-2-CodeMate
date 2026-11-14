<?php
class DashboardController extends Controller
{
    public function index(): void
    {
        $user = $this->requireLogin();
        $model = new User();
        $bookingRows = $model->dashboardBookings((int) $user['user_id'], $user['role']);

        $subjectTotals = [];
        $statusTotals = ['confirmed' => 0, 'pending' => 0, 'completed' => 0, 'cancelled' => 0];
        foreach ($bookingRows as $row) {
            $subject = $row['subject'];
            $subjectTotals[$subject] = ($subjectTotals[$subject] ?? 0) + (int) $row['total'];
            $status = $row['status'] === 'declined' ? 'cancelled' : $row['status'];
            if (array_key_exists($status, $statusTotals)) $statusTotals[$status] += (int) $row['total'];
        }

        $tutors = $user['role'] === 'tutee' ? $model->featuredTutors(8) : [];
        foreach ($tutors as &$tutor) {
            $tutor['subjects'] = $tutor['subjects'] ? explode('||', $tutor['subjects']) : [];
            $tutor['credibility'] = (float) $tutor['credibility'];
            $tutor['sessions'] = (int) $tutor['sessions'];
        }
        unset($tutor);

        $this->render('dashboard/index', [
            'pageTitle' => 'CodeMate - Dashboard',
            'user' => $user,
            'stats' => $model->dashboardStats($user['role'], (int) $user['user_id']),
            'subjectTotals' => $subjectTotals,
            'statusTotals' => $statusTotals,
            'featuredTutors' => $tutors,
            'styles' => ['dashboard', 'role-pages'],
            'scripts' => ['dashboard'],
        ]);
    }
}
