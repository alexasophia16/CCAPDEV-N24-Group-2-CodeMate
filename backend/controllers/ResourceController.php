<?php

class ResourceController extends Controller
{
    public function index(): void
    {
        $user = $this->requireLogin();

        $resourceModel = new ResourceModel();

        // Fetch resources (approved ones + any pending ones owned by the user)
        $resources = $resourceModel->getResourcesForUser((int)$user['user_id'], $user['role']);

        $this->render('resources/index', [
            'pageTitle'   => 'CodeMate - Resources',
            'resources'   => $resources,
            'courses'     => (new Course())->active(),
            'types'       => ResourceModel::TYPES,
            'currentUser' => $user,
            'error'       => get_flash('error'),
            'success'     => get_flash('success'),
            'styles'      => ['role-pages', 'report-modal'],
            'scripts'     => ['resources', 'report-modal']
        ]);
    }

    public function store(): void
    {
        // Only tutors can upload resources
        $user = $this->requireLogin(['tutor']);

        $title       = input('title');
        $courseId    = (int)input('course_id');
        $type        = input('type');
        $url         = input('url');
        $description = input('description');
        $course      = (new Course())->find($courseId);

        $error = null;
        if ($title === '' || mb_strlen($title) > 200) {
            $error = 'Please enter a title (up to 200 characters).';
        } elseif ($course === null || $course['status'] !== 'active') {
            $error = 'Please choose a course.';
        } elseif (!in_array($type, ResourceModel::TYPES, true)) {
            $error = 'Please choose a resource type.';
        } elseif (!filter_var($url, FILTER_VALIDATE_URL) || !preg_match('#^https?://#i', $url) || strlen($url) > 500) {
            // only http(s) links, so things like javascript: links can't be saved
            $error = 'Please enter a valid link starting with http:// or https://.';
        } elseif (mb_strlen($description) > 1000) {
            $error = 'Descriptions can be up to 1000 characters.';
        }

        if ($error !== null) {
            flash('error', $error);
            redirect('resources');
        }

        (new ResourceModel())->createResource((int)$user['user_id'], $courseId, $title, $type, $url, $description);
        flash('success', 'Resource submitted for moderation.');
        redirect('resources');
    }
}
