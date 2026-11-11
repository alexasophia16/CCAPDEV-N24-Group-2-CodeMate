<?php

class CourseController extends Controller
{
    public function manage(): void
    {
        $this->requireLogin(['admin']);

        $this->render('admin/courses', [
            'pageTitle' => 'CodeMate - Course Management',
            'courses'   => (new Course())->allWithTutorCount(),
            'old'       => $_SESSION['old_course'] ?? ['course_code' => '', 'course_name' => ''],
            'styles'    => ['role-pages'],
        ]);
        unset($_SESSION['old_course']);
    }

    public function store(): void
    {
        $this->requireLogin(['admin']);
        verify_csrf();
        [$code, $name] = $this->courseInput();
        $courses = new Course();

        $_SESSION['old_course'] = ['course_code' => $code, 'course_name' => $name];
        $this->validate($courses, $code, $name);
        unset($_SESSION['old_course']);

        $courses->create($code, $name);
        flash('success', "$code added.");
        redirect('admin-course-management');
    }

    public function update(): void
    {
        $this->requireLogin(['admin']);
        verify_csrf();
        $courseId = (int) ($_POST['course_id'] ?? 0);
        [$code, $name] = $this->courseInput();
        $courses = new Course();

        if ($courses->find($courseId) === null) {
            flash('error', 'That course could not be found.');
            redirect('admin-course-management');
        }
        $this->validate($courses, $code, $name, $courseId);

        $courses->update($courseId, $code, $name);
        flash('success', "$code updated.");
        redirect('admin-course-management');
    }

    public function setStatus(): void
    {
        $this->requireLogin(['admin']);
        verify_csrf();
        $status = input('status');

        if ((new Course())->setStatus((int) ($_POST['course_id'] ?? 0), $status)) {
            flash('success', $status === 'archived' ? 'Course archived.' : 'Course restored.');
        } else {
            flash('error', 'The course could not be updated.');
        }
        redirect('admin-course-management');
    }

    private function courseInput(): array
    {
        return [strtoupper(str_replace(' ', '', input('course_code'))), input('course_name')];
    }

    private function validate(Course $courses, string $code, string $name, int $courseId = 0): void
    {
        $error = null;
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
