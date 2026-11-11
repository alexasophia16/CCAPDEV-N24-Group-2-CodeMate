<?php

class DiscussionController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $this->placeholder('Discussions', 'discussions.html');
    }

    public function thread(): void
    {
        $this->requireLogin();
        $this->placeholder('Discussion', 'discussions-thread.html');
    }
}
