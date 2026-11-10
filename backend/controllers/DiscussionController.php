<?php

class DiscussionController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();

        // Grab app's database connection 
        global $pdo;
        
        $courseFilter = trim((string)($_GET['course'] ?? 'ALL'));
        $searchQuery  = trim((string)($_GET['q'] ?? ''));

        $discussionModel = new DiscussionModel($pdo);
        $courseModel     = new Course();

        $this->render('discussions', [
            'pageTitle'      => 'CodeMate - Discussions',
            'threads'        => $discussionModel->getDiscussions($courseFilter),
            'courses'        => $courseModel->active(),
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

        global $pdo;
        
        $id = (int)($_GET['id'] ?? 0);

        $discussionModel = new DiscussionModel($pdo);
        $replyModel      = new ReplyModel($pdo);
        $thread = $discussionModel->getDiscussionById($id);

        
        if ($thread === false || $thread === null) {
            http_response_code(404);
            $this->render('errors/error', [
                'pageTitle' => 'CodeMate - Not Found',
                'title'     => 'Discussion Not Found',
                'message'   => 'The discussion thread you are looking for does not exist or has been removed.',
                'styles'    => ['role-pages']
            ]);
            return;
        }

        $this->render('discussions-thread', [
            'pageTitle' => 'CodeMate - ' . $thread['title'],
            'thread'    => $thread,
            'replies'   => $replyModel->getRepliesByDiscussionId($id),
            'error'     => get_flash('error'),
            'success'   => get_flash('success'),
            'styles'    => ['discussions'],
            'scripts'   => ['discussions-thread']
        ]);
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

        global $pdo;
        $discussionModel = new DiscussionModel($pdo);
        
        try {
            $discussionModel->createDiscussion(
                (int)$user['user_id'], 
                $course, 
                $title, 
                $content
            );
            flash('success', 'Discussion published successfully.');
        } catch (Exception $e) {
            flash('error', 'Could not create discussion: ' . $e->getMessage());
        }

        redirect('discussions');
    }

    

    public function storeReply(): void
    {
        $user = $this->requireLogin();
        verify_csrf();

        // Matches 'thread_id' from the frontend form and JS

        $discussionId = (int)input('thread_id');
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

        global $pdo;

        $replyModel = new ReplyModel($pdo);
        
        $replyModel->createReply(
            $discussionId, 
            (int)$user['user_id'], 
            $content
        );

        flash('success', 'Reply posted successfully.');
        redirect('discussions-thread', ['id' => $discussionId]);
    }
}
