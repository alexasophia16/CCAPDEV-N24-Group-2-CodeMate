<?php
// Real Google sign-in. Fill in the three values from Google Cloud Console
// (steps are in google.example.php). Do not commit this file or share the secret.
return [
    'client_id'     => '690558985702-cqmqque066sm23uh3tnsitc5ogvk8ssk.apps.googleusercontent.com',
    'client_secret' => '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',
    // Must exactly match an Authorized redirect URI in Google Cloud Console, e.g.
    // http://localhost/YOUR_PROJECT_FOLDER/index.php?page=google-callback
    'redirect_uri'  => 'http://ccscloud1.dlsu.edu.ph:60103/CCAPDEV-N24/CCAPDEV-N24-Group-2-CodeMate/index.php?page=google-callback',
];
