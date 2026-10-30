<?php

class CourseController extends Controller
{
    public function manage(): void
    {
        $this->requireLogin(['admin']);
        $this->placeholder('Course Management', 'admin-course-management.html');
    }
}
