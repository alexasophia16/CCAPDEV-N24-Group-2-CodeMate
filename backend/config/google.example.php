<?php

// Copy this file to google.php and enter credentials from Google Cloud Console.
return [
    'client_id'     => 'YOUR_GOOGLE_CLIENT_ID',
    'client_secret' => 'YOUR_GOOGLE_CLIENT_SECRET',
    // Must exactly match an "Authorized redirect URI" in Google Cloud Console, e.g.
    // http://localhost/YOUR_PROJECT_FOLDER/index.php?page=google-callback
    'redirect_uri'  => 'http://localhost/YOUR_PROJECT_FOLDER/index.php?page=google-callback',
];
