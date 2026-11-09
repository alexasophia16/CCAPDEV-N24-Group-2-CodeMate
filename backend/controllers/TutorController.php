<?php

class TutorController extends Controller
{
    public function find(): void
    {
        $user = $this->requireLogin();
        $query = trim((string) ($_GET['q'] ?? ''));
        $courseId = (int) ($_GET['course'] ?? 0);
        $sort = (string) ($_GET['sort'] ?? 'rating');

        $this->render('tutors/find', [
            'pageTitle' => 'CodeMate - Find a Tutor',
            'tutors'    => (new Tutor())->search($query, $courseId, $sort),
            'courses'   => (new Course())->active(),
            'query'     => $query,
            'courseId'  => $courseId,
            'sort'      => $sort,
            'canBook'   => $user['role'] === 'tutee',
            'styles'    => ['find-tutor'],
        ]);
    }

    public function profile(): void
    {
        $user = $this->requireLogin();
        $tutorId = (int) ($_GET['id'] ?? 0);
        $tutors = new Tutor();
        $tutor = $tutors->find($tutorId);

        if ($tutor === null) {
            flash('error', 'That tutor could not be found.');
            redirect('find-tutor');
        }

        $this->render('tutors/profile', [
            'pageTitle' => 'CodeMate - ' . $tutor['full_name'],
            'tutor'     => $tutor,
            'slots'     => $tutors->openSlots($tutorId),
            'reviews'   => $tutors->reviews($tutorId),
            'canBook'   => $user['role'] === 'tutee',
            'styles'    => ['find-tutor', 'tutor-profile'],
        ]);
    }
}
