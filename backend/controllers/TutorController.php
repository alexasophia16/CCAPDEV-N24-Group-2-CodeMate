<?php

// Find a Tutor and Tutor Profile pages.
// Both are GET pages: they only read data, so the search and filters show up in the URL
// (e.g. index.php?page=find-tutor&q=data&sort=name) and can be bookmarked or refreshed.
class TutorController extends Controller
{
    // GET find-tutor
    public function find(): void
    {
        // any logged-in role can browse tutors; only tutees see the "Book session" button
        $user = $this->requireLogin();
        $query = trim((string) ($_GET['q'] ?? ''));
        $courseId = (int) ($_GET['course'] ?? 0);
        $sort = (string) ($_GET['sort'] ?? 'rating');

        $this->render('tutors/find', [
            'pageTitle' => 'CodeMate - Find a Tutor',
            'tutors'    => (new Tutor())->search($query, $courseId, $sort),
            'courses'   => (new Course())->active(),
            // sent back to the view so the form keeps what the user searched
            'query'     => $query,
            'courseId'  => $courseId,
            'sort'      => $sort,
            'canBook'   => $user['role'] === 'tutee',
            'styles'    => ['find-tutor'],
        ]);
    }

    // GET tutor-profile&id=2
    public function profile(): void
    {
        $user = $this->requireLogin();
        $tutorId = (int) ($_GET['id'] ?? 0);
        $tutors = new Tutor();
        $tutor = $tutors->find($tutorId);

        // unknown, unverified or deactivated tutors send the user back to the search page
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
            // tutor-profile.css builds on the card styles from find-tutor.css
            'styles'    => ['find-tutor', 'tutor-profile', 'report-modal'],
            'scripts'   => ['report-modal'],
        ]);
    }
}
