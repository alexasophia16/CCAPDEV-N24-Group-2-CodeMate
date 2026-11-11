<?php
// 'METHOD page' => [Controller, method]

return [
    // Accounts
    'GET login'    => ['AuthController', 'showLogin'],
    'POST login'   => ['AuthController', 'login'],
    'GET signup'   => ['AuthController', 'showSignup'],
    'POST signup'  => ['AuthController', 'signup'],
    'POST logout'  => ['AuthController', 'logout'],
    'GET google-login'    => ['AuthController', 'googleLogin'],
    'GET google-signup'   => ['AuthController', 'googleSignup'],
    'GET google-callback' => ['AuthController', 'googleCallback'],
    'GET google-demo'     => ['AuthController', 'showGoogleDemo'],
    'POST google-demo'    => ['AuthController', 'googleDemoSubmit'],
    'GET google-role'     => ['AuthController', 'showGoogleRole'],
    'POST google-role'    => ['AuthController', 'saveGoogleRole'],

    // Dashboard and notifications
    'GET dashboard'               => ['DashboardController', 'index'],
    'POST notification-open'      => ['NotificationController', 'open'],
    'POST notifications-read-all' => ['NotificationController', 'readAll'],

    // Admin: users and tutor verification
    'GET admin-users'         => ['AdminUserController', 'users'],
    'GET admin-verify-tutors' => ['AdminUserController', 'verifyTutors'],

    // Finding tutors and booking
    'GET find-tutor'        => ['TutorController', 'find'],
    'GET tutor-profile'     => ['TutorController', 'profile'],
    'GET book-consultation' => ['BookingController', 'create'],
    'GET my-bookings'       => ['BookingController', 'myBookings'],

    // Admin: courses
    'GET admin-course-management' => ['CourseController', 'manage'],

    // Tutor's own pages
    'GET tutor-profile-manage' => ['TutorAreaController', 'manageProfile'],
    'GET tutor-slots'          => ['TutorAreaController', 'slots'],
    'GET tutor-bookings'       => ['TutorAreaController', 'bookingRequests'],
    'GET tutor-reviews'        => ['TutorAreaController', 'reviews'],

    // Messages
    'GET messages' => ['MessageController', 'index'],

    // Discussions, resources and reports
    'GET discussions'        => ['DiscussionController', 'index'],
    'GET discussions-thread' => ['DiscussionController', 'thread'],
    'GET resources'          => ['ResourceController', 'index'],
    'GET reports'            => ['ReportController', 'index'],

    // Admin: moderation and reports
    'GET admin-moderation' => ['ModerationController', 'moderation'],
    'GET admin-reports'    => ['ModerationController', 'reports'],
];
