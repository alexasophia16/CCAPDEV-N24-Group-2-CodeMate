<?php

class ResourceController extends Controller
{
    public function index(): void
    {
        $user = $this->requireLogin();
        
        global $pdo;
        $resourceModel = new ResourceModel($pdo);

        // Fetch resources (approved ones + any pending ones owned by the user)
        $resources = $resourceModel->getResourcesForUser($user['user_id'], $user['role']);

        $this->render('resources', [
            'pageTitle'   => 'CodeMate - Resources',
            'resources'   => $resources,
            'currentUser' => $user,
            'error'       => get_flash('error'),
            'success'     => get_flash('success'),
            'styles'      => ['role-pages'],
            'scripts'     => ['resources']
        ]);
    }

    public function store(): void
    {
        // Only tutors can upload resources
        $user = $this->requireLogin(['tutor']);
        verify_csrf();

        $title  = trim((string)input('title'));
        $course = trim((string)input('course'));
        $type   = trim((string)input('type'));

        if ($title === '' || $course === '' || $type === '') {
            flash('error', 'Please fill in all resource fields.');
            redirect('resources');
        }

        global $pdo;
        $resourceModel = new ResourceModel($pdo);

        try {
            $resourceModel->createResource($user['user_id'], $title, $course, $type);
            flash('success', 'Resource submitted for moderation.');
        } catch (Exception $e) {
            flash('error', 'Could not upload resource: ' . $e->getMessage());
        }

        redirect('resources');
    }
}
