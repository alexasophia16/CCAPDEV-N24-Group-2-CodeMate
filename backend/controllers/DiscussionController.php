<?php

class DiscussionController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        
        $courseFilter = trim((string)($_GET['course'] ?? 'ALL'));
        $searchQuery  = trim((string)($_GET['q'] ?? ''));

        $this->render('discussions', [
            'pageTitle'      => 'CodeMate - Discussions',
            'threads'        => (new DiscussionModel($pdo))->getDiscussions($courseFilter, $searchQuery),
            'courses'        => (new Course())->getActiveCourses(),
            'selectedFilter' => $courseFilter,
            'searchQuery'    => $searchQuery,
            'error'          => get_flash('error'),
            'success'        => get_flash('success'),
            'styles'         => ['discussions'],
            'scripts'        => ['discussions'] 
        ]);
        
    
    }

    public function thread(): void
    {
        $this->requireLogin();
        
        $id = (int)($_GET['id'] ?? 0);
        $thread = (new Discussion())->getDiscussionById($id);

        
        if ($thread === null) {
            http_response_code(404);
            $this->render('errors/error', [
                'pageTitle' => 'CodeMate - Not Found',
                'title'     => 'Discussion Not Found',
                'message'   => 'The discussion thread you are looking for does not exist or has been removed.',
                'styles'    => ['role-pages']
            ]);
            return;
        }
    }

    public function store(): void
    {
        $user = $this->requireLogin();
        verify_csrf();

        // view-only admin rule server's side
        if ($user['role'] === 'admin') {
            flash('error', 'Admins are in view-only mode and cannot post discussions.');
            redirect('discussions');
        }

        $course  = input('course');
        $title   = input('title');
        $content = input('content');

        if ($course === '' || $title === '' || $content === '') {
            flash('error', 'All fields are required to publish a question.');
            redirect('discussions');
        }

        (new Discussion())->createDiscussion([
            'user_id' => (int)$user['user_id'],
            'course'  => $course,
            'title'   => $title,
            'content' => $content
        ]);

        flash('success', 'Discussion published successfully.');
        redirect('discussions');
    }

    public function storeReply(): void
    {
        $user = $this->requireLogin();
        verify_csrf();

        $discussionId = (int)input('discussion_id');
        $content      = input('content');

        if ($discussionId === 0) {
            flash('error', 'Invalid discussion thread.');
            redirect('discussions');
        }

        if ($user['role'] === 'admin') {
            flash('error', 'Admins are in view-only mode and cannot reply to discussions.');
            redirect('discussions-thread', ['id' => $discussionId]);
        }

        if ($content === '') {
            flash('error', 'Reply content cannot be empty.');
            redirect('discussions-thread', ['id' => $discussionId]);
        }

        (new ReplyModel($pdo))->createReply([
            'discussion_id' => $discussionId,
            'user_id'       => (int)$user['user_id'],
            'content'       => $content
        ]);

        flash('success', 'Reply posted successfully.');
        redirect('discussions-thread', ['id' => $discussionId]);
    }
}
