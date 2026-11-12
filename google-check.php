<?php
// TEMPORARY diagnostic for Google sign-in. Put this file next to index.php, open it in the
// browser, read the result, then DELETE IT. It never prints the client secret.
header('Content-Type: text/plain; charset=utf-8');

$file = __DIR__ . '/backend/config/google.php';
$cfg = is_file($file) ? require $file : [];
$id = (string) ($cfg['client_id'] ?? '');
$secret = (string) ($cfg['client_secret'] ?? '');
$redirect = (string) ($cfg['redirect_uri'] ?? '');

echo "PHP version:           " . PHP_VERSION . "\n";
echo "cURL extension loaded: " . (function_exists('curl_init') ? 'yes' : 'NO') . "\n";
echo "OpenSSL loaded:        " . (extension_loaded('openssl') ? 'yes' : 'NO') . "\n";
echo "google.php found:      " . (is_file($file) ? 'yes' : 'NO') . "\n";
echo "client_id looks right: " . (str_ends_with(trim($id), '.apps.googleusercontent.com') ? 'yes' : 'NO (should end in .apps.googleusercontent.com)') . "\n";
echo "client_id whitespace:  " . ($id !== trim($id) ? 'YES - has leading/trailing spaces' : 'none') . "\n";
echo "client_secret set:     " . ($secret !== '' ? 'yes' : 'NO') . "\n";
echo "secret whitespace:     " . ($secret !== trim($secret) ? 'YES - has leading/trailing spaces' : 'none') . "\n";
echo "redirect_uri:          " . ($redirect !== '' ? $redirect : '(missing)') . "\n\n";

function probe(string $label, string $url, ?array $post = null): void
{
    if (!function_exists('curl_init')) {
        echo "$label\n  cannot test: the cURL extension is not loaded\n\n";
        return;
    }
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_CONNECTTIMEOUT => 6,
    ]);
    if ($post !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    }
    $body = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $errno = curl_errno($ch);
    $err = curl_error($ch);
    curl_close($ch);

    echo "$label\n";
    echo "  HTTP status: $status\n";
    echo "  cURL error:  " . ($errno ? "[$errno] $err" : 'none') . "\n";
    $json = is_string($body) ? json_decode($body, true) : null;
    if (is_array($json) && isset($json['error'])) {
        $desc = isset($json['error_description']) ? ' - ' . $json['error_description'] : '';
        echo "  Google says: " . $json['error'] . $desc . "\n";
    }
    echo "\n";
}

// 1. Can this server reach Google at all (DNS, firewall, SSL)?
probe('1) Reach Google over HTTPS', 'https://accounts.google.com/.well-known/openid-configuration');

// 2. Same request the login makes, but with a fake code. Google's answer shows whether the
//    network, client ID, secret and redirect URI are accepted.
//    invalid_grant = good (only the fake code was rejected). invalid_client = wrong ID/secret.
probe('2) Token request with a FAKE code (same as the login does)', 'https://oauth2.googleapis.com/token', [
    'code'          => 'fake-code-for-testing',
    'client_id'     => $id,
    'client_secret' => $secret,
    'redirect_uri'  => $redirect,
    'grant_type'    => 'authorization_code',
]);

echo "Done. Delete google-check.php from the server now.\n";
