<?php

// Copy this file to google.php and enter credentials from Google Cloud Console.
return [
    'client_id' => 'YOUR_GOOGLE_CLIENT_ID',
    'client_secret' => 'YOUR_GOOGLE_CLIENT_SECRET',
    // Must exactly match the Authorized redirect URI in Google Cloud Console.
    'redirect_uri' => 'https://YOUR_HOST/YOUR_PROJECT_PATH/index.php?page=google-callback',
];
