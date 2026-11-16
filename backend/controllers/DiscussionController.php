<?php

// Discussions: the list of questions per course, one thread with its replies,
// and posting a question or a reply. Every role can read; admins can't post (view-only).
class DiscussionController extends Controller
{
    // GET discussions (optionally &course=CCPROG2): the course tabs filter the list.
    // 'ALL' shows every course. Only visible threads are listed (see DiscussionModel).
    public function index(): void
    {
        $this->requireLogin();

        $courseFilter = trim((string)($_GET['course'] ?? 'ALL'));
        $searchQuery  = trim((string)($_GET['q'] ?? ''));

        $discussionModel = new DiscussionModel();
        $courseModel     = new Course();

        $this->render('discussions/index', [
            'pageTitle'      => 'CodeMate - Discussions',
            'threads'        => $discussionModel->getDiscussions($courseFilter),
            'courses'        => $courseModel->active(),
            'selectedFilter' => $courseFilter,
            'searchQuery'    => $searchQuery,
            'styles'         => ['discussions'],
            'scripts'        => ['discussions'] 
        ]);
        
    
    }

    // GET discussions-thread&id=7: one question and its replies.
    // A thread that doesn't exist or was hidden by a moderator shows a 404 page.
    public function thread(): void
    {
        $this->requireLogin();

        $id = (int)($_GET['id'] ?? 0);

        $discussionModel = new DiscussionModel();
        $replyModel      = new ReplyModel();
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

        $this->render('discussions/thread', [
            'pageTitle' => 'CodeMate - ' . $thread['title'],
            'thread'    => $thread,
            'replies'   => $replyModel->getRepliesByDiscussionId($id),
            'styles'    => ['discussions', 'report-modal'],
            'scripts'   => ['report-modal'],
        ]);
    }

    // POST store-discussion: the "Ask Question" form.
    // The course is sent as its code (e.g. CCPROG2); createDiscussion() turns it into the id
    // and throws if the code doesn't exist, which is shown as an error message.
    public function store(): void
    {
        $user = $this->requireLogin();

        // admins are view-only; checked here on the server, not just by hiding the button
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

        $discussionModel = new DiscussionModel();
        
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

    // POST store-reply: the reply box at the bottom of a thread.
    // After saving, the thread's author is notified (unless they replied to their own question).
    public function storeReply(): void
    {
        $user = $this->requireLogin();

        // Matches 'thread_id' from the frontend form and JS

        $discussionId = (int)input('thread_id');
        $content      = input('content');

        // the thread must exist and still be visible
        if ($discussionId === 0 || !(new DiscussionModel())->getDiscussionById($discussionId)) {
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


        $replyModel = new ReplyModel();
        
        $replyModel->createReply(
            $discussionId, 
            (int)$user['user_id'], 
            $content
        );

        $thread = (new DiscussionModel())->getDiscussionById($discussionId);
        if ((int)$thread['author_id'] !== (int)$user['user_id']) {
            (new Notification())->create((int)$thread['author_id'],
                "{$user['full_name']} replied to your discussion \"{$thread['title']}\".",
                'discussions-thread&id=' . $discussionId);
        }
        flash('success', 'Reply posted successfully.');
        redirect('discussions-thread', ['id' => $discussionId]);
    }
}
