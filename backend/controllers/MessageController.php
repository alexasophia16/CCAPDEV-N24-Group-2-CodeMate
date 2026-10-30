<?php

class MessageController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $this->placeholder('Messages', 'messages.html');
    }
}
