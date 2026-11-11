<?php

// Copy this file to google.php and enter credentials from Google Cloud Console.
return [
    'client_id' => 'YOUR_GOOGLE_CLIENT_ID',
    'client_secret' => 'YOUR_GOOGLE_CLIENT_SECRET',
    // Must exactly match the Authorized redirect URI in Google Cloud Console.
    'redirect_uri' => 'https://YOUR_HOST/YOUR_PROJECT_PATH/index.php?page=google-callback',
];

// No Google credentials? For a demo you can create google.php with only this line:
//   return ['demo_mode' => true];
// The Google buttons then open a built-in "Choose an account" page that simulates
// Google sign-in without contacting Google. Never turn this on for a real public site:
// it signs people in by email address alone (admin accounts are refused).
