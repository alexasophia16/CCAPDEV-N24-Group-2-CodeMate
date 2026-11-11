<?php

class TutorController extends Controller
{
    public function find(): void
    {
        $this->requireLogin();
        $this->placeholder('Find a Tutor', 'find-tutor.html');
    }

    public function profile(): void
    {
        $this->requireLogin();
        $this->placeholder('Tutor Profile', 'tutor-profile.html');
    }
}
