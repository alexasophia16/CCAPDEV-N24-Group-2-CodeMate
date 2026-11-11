<?php
// 'METHOD page' => [Controller, method]

return [
    // Accounts
    'GET login'    => ['AuthController', 'showLogin'],
    'POST login'   => ['AuthController', 'login'],
    'GET signup'   => ['AuthController', 'showSignup'],
    'POST signup'  => ['AuthController', 'signup'],
    'POST logout'  => ['AuthController', 'logout'],
    'GET google-demo'  => ['AuthController', 'googleDemo'],
    'POST google-demo' => ['AuthController', 'googleDemoSubmit'],

    // Dashboard and notifications
    'GET dashboard'               => ['DashboardController', 'index'],
    'POST notification-open'      => ['NotificationController', 'open'],
    'POST notifications-read-all' => ['NotificationController', 'readAll'],

    // Admin: users and tutor verification
    'GET admin-users'         => ['AdminUserController', 'users'],
    'POST admin-user-status' => ['AdminUserController', 'updateStatus'],
    'GET admin-verify-tutors' => ['AdminUserController', 'verifyTutors'],
    'POST admin-review-tutor' => ['AdminUserController', 'reviewTutor'],

    // Finding tutors and booking
    'GET find-tutor'         => ['TutorController', 'find'],
    'GET tutor-profile'      => ['TutorController', 'profile'],
    'GET book-consultation'  => ['BookingController', 'create'],
    'POST book-consultation' => ['BookingController', 'store'],
    'GET my-bookings'        => ['BookingController', 'myBookings'],
    'POST booking-cancel'    => ['BookingController', 'cancel'],
    'POST booking-review'    => ['BookingController', 'review'],

    // Admin: courses
    'GET admin-course-management' => ['CourseController', 'manage'],
    'POST course-create'          => ['CourseController', 'store'],
    'POST course-update'          => ['CourseController', 'update'],
    'POST course-status'          => ['CourseController', 'setStatus'],

    // Tutor's own pages
    'GET tutor-profile-manage' => ['TutorAreaController', 'manageProfile'],
    'POST tutor-profile-manage' => ['TutorAreaController', 'updateProfile'],
    'GET tutor-slots'          => ['TutorAreaController', 'slots'],
    'POST tutor-slots'         => ['TutorAreaController', 'addSlot'],
    'GET tutor-bookings'       => ['TutorAreaController', 'bookingRequests'],
    'POST tutor-booking-update' => ['TutorAreaController', 'updateBooking'],
    'GET tutor-reviews'        => ['TutorAreaController', 'reviews'],
    'POST tutor-booking-update' => ['TutorAreaController', 'updateBooking'],
    'POST tutor-slot-delete' => ['TutorAreaController', 'deleteSlot'],

    // Messages
    'GET messages' => ['MessageController', 'index'],
    'POST messages' => ['MessageController', 'send'],

    // Discussions, resources and reports
    'GET discussions'        => ['DiscussionController', 'index'],
    'GET discussions-thread' => ['DiscussionController', 'thread'],
    'POST store-discussion'  => ['DiscussionController', 'store'],
    'POST store-reply'       => ['DiscussionController', 'storeReply'],
    'GET resources'          => ['ResourceController', 'index'],
    'POST store-resource'    => ['ResourceController', 'store'],
    'GET reports'            => ['ReportController', 'index'],
    'POST store-report'      => ['ReportController', 'store'],

    // Admin: moderation and reports
    'GET admin-moderation' => ['ModerationController', 'moderation'],
    'POST admin/update-moderation'=> ['AdminUserController', 'updateModeration'],
    'GET admin-reports'    => ['ModerationController', 'reports'],
    'POST admin/update-report'=> ['AdminUserController', 'updateReport']
];
