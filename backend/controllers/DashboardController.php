<?php

// The dashboard, the first page after logging in (GET dashboard). Every role can open it,
// and what it shows depends on the role. The numbers come from the User model;
// frontend/scripts/dashboard.js draws the two charts with Chart.js.
class DashboardController extends Controller
{
    public function index(): void
    {
        $user = $this->requireLogin();
        $model = new User();

        // Booking counts per course and status, already filtered by role in the model:
        // tutees get their own bookings, tutors the bookings on their slots, admins everything.
        $bookingRows = $model->dashboardBookings((int) $user['user_id'], $user['role']);

        // Add the rows up for the two charts:
        //   $subjectTotals - bookings per course, e.g. ['Data Structures' => 3]
        //   $statusTotals  - bookings per status; declined bookings are counted as cancelled
        $subjectTotals = [];
        $statusTotals = ['confirmed' => 0, 'pending' => 0, 'completed' => 0, 'cancelled' => 0];
        foreach ($bookingRows as $row) {
            $subject = $row['subject'];
            $subjectTotals[$subject] = ($subjectTotals[$subject] ?? 0) + (int) $row['total'];
            $status = $row['status'] === 'declined' ? 'cancelled' : $row['status'];
            if (array_key_exists($status, $statusTotals)) {
                $statusTotals[$status] += (int) $row['total'];
            }
        }

        // Tutees also see a few tutor cards. The model returns each tutor's courses as one
        // string like "A||B", which explode() turns into a list.
        $tutors = $user['role'] === 'tutee' ? $model->featuredTutors(8) : [];
        foreach ($tutors as &$tutor) {   // & so the changes are saved back into $tutors
            $tutor['subjects'] = $tutor['subjects'] ? explode('||', $tutor['subjects']) : [];
            $tutor['credibility'] = (float) $tutor['credibility'];
            $tutor['sessions'] = (int) $tutor['sessions'];
        }
        unset($tutor);   // break the reference so later code can't change the last tutor by mistake

        $this->render('dashboard/index', [
            'pageTitle'      => 'CodeMate - Dashboard',
            'user'           => $user,
            'stats'          => $model->dashboardStats($user['role'], (int) $user['user_id']),   // the 4 number cards
            'subjectTotals'  => $subjectTotals,
            'statusTotals'   => $statusTotals,
            'featuredTutors' => $tutors,
            'styles'         => ['dashboard', 'role-pages'],
            'scripts'        => ['dashboard'],
        ]);
    }
}
