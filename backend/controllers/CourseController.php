<?php

// Admin Course Management: list, add, edit, archive and restore courses.
// All methods are admin-only. The POST methods follow the same steps as BookingController:
// check login -> validate -> save -> flash() + redirect().
class CourseController extends Controller
{
    // GET admin-course-management
    public function manage(): void
    {
        $this->requireLogin(['admin']);

        $this->render('admin/courses', [
            'pageTitle' => 'CodeMate - Course Management',
            'courses'   => (new Course())->allWithTutorCount(),
            // if adding a course failed, refill the form with what the admin typed
            'old'       => $_SESSION['old_course'] ?? ['course_code' => '', 'course_name' => ''],
            'styles'    => ['role-pages'],
        ]);
        unset($_SESSION['old_course']);
    }

    // POST course-create
    public function store(): void
    {
        $this->requireLogin(['admin']);
        [$code, $name] = $this->courseInput();
        $courses = new Course();

        // remember the input in case validate() redirects back with an error
        $_SESSION['old_course'] = ['course_code' => $code, 'course_name' => $name];
        $this->validate($courses, $code, $name);
        unset($_SESSION['old_course']);

        $courses->create($code, $name);
        flash('success', "$code added.");
        redirect('admin-course-management');
    }

    // POST course-update (the form inside the "Edit" dropdown)
    public function update(): void
    {
        $this->requireLogin(['admin']);
        $courseId = (int) ($_POST['course_id'] ?? 0);
        [$code, $name] = $this->courseInput();
        $courses = new Course();

        if ($courses->find($courseId) === null) {
            flash('error', 'That course could not be found.');
            redirect('admin-course-management');
        }
        // pass $courseId so the course's own current code isn't counted as a duplicate
        $this->validate($courses, $code, $name, $courseId);

        $courses->update($courseId, $code, $name);
        flash('success', "$code updated.");
        redirect('admin-course-management');
    }

    // POST course-status (the Archive / Restore button)
    public function setStatus(): void
    {
        $this->requireLogin(['admin']);
        $status = input('status');

        if ((new Course())->setStatus((int) ($_POST['course_id'] ?? 0), $status)) {
            flash('success', $status === 'archived' ? 'Course archived.' : 'Course restored.');
        } else {
            flash('error', 'The course could not be updated.');
        }
        redirect('admin-course-management');
    }

    // Reads the form and cleans the code: "cs intsy" becomes "CSINTSY"
    private function courseInput(): array
    {
        return [strtoupper(str_replace(' ', '', input('course_code'))), input('course_name')];
    }

    // Shared checks for add and edit. On the first problem it flashes an error and
    // redirects, which stops the request (redirect() calls exit).
    private function validate(Course $courses, string $code, string $name, int $courseId = 0): void
    {
        $error = null;
        // 3 to 10 capital letters or digits, e.g. CCPROG3
        if (!preg_match('/^[A-Z0-9]{3,10}$/', $code)) {
            $error = 'Course codes are 3 to 10 letters or numbers, e.g. CCPROG3.';
        } elseif ($name === '' || mb_strlen($name) > 100) {
            $error = 'Please enter a course name (up to 100 characters).';
        } elseif ($courses->codeExists($code, $courseId)) {
            $error = "$code already exists.";
        }

        if ($error !== null) {
            flash('error', $error);
            redirect('admin-course-management');
        }
    }
}
