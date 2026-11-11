<?php

class ResourceController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $this->placeholder('Resources', 'resources.html');
    }
}
