<?php
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// url('tutor-profile', ['id' => 2]) -> index.php?page=tutor-profile&id=2
function url(string $page, array $params = []): string
{
    return 'index.php?' . http_build_query(['page' => $page] + $params);
}

function redirect(string $page, array $params = []): void
{
    header('Location: ' . url($page, $params));
    exit;
}

// Notification links and "return to this page" values are stored as a page plus parameters,
// e.g. "discussions-thread&id=7". This splits that into url()'s two arguments. Only simple
// values are kept, and url() always builds an index.php link, so it can't point to another site.
function link_parts(string $link): array
{
    parse_str('page=' . preg_replace('/^page=/', '', $link), $query);
    $page = is_string($query['page'] ?? null) && preg_match('/^[a-z-]+$/', $query['page']) ? $query['page'] : 'dashboard';
    unset($query['page']);

    $params = [];
    foreach ($query as $key => $value) {
        if (is_string($value) && preg_match('/^[a-z_]+$/', $key) && preg_match('/^[\w-]{1,60}$/', $value)) {
            $params[$key] = $value;
        }
    }
    return [$page, $params];
}

function link_url(string $link): string
{
    return url(...link_parts($link));
}

function current_user(): ?array
{
    static $user = false;

    if ($user === false) {
        $user = null;
        if (isset($_SESSION['user_id'])) {
            $user = (new User())->findById((int) $_SESSION['user_id']);
            if ($user === null || $user['account_status'] !== 'active') {
                unset($_SESSION['user_id']);
                $user = null;
            }
        }
    }

    return $user;
}

// message shown once on the next page
function flash(string $key, string $message): void
{
    $_SESSION['flash'][$key] = $message;
}

function get_flash(string $key): ?string
{
    $message = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $message;
}

function input(string $key): string
{
    return trim((string) ($_POST[$key] ?? ''));
}

function initials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name));
    $letters = mb_substr($parts[0], 0, 1);
    if (count($parts) > 1) {
        $letters .= mb_substr(end($parts), 0, 1);
    }
    return mb_strtoupper($letters);
}

function role_label(string $role): string
{
    return ['tutee' => 'Student / Tutee', 'tutor' => 'Tutor', 'admin' => 'Admin'][$role] ?? $role;
}

function time_ago(string $datetime): string
{
    $seconds = time() - strtotime($datetime);

    if ($seconds < 60) {
        return 'Just now';
    }
    if ($seconds < 3600) {
        return floor($seconds / 60) . 'm ago';
    }
    if ($seconds < 86400) {
        return floor($seconds / 3600) . 'h ago';
    }
    if ($seconds < 172800) {
        return 'Yesterday';
    }
    if ($seconds < 604800) {
        return floor($seconds / 86400) . 'd ago';
    }
    return date('M j', strtotime($datetime));
}

// "Tue, Dec 1, 2026 · 2:00 PM – 3:00 PM"
function schedule_label(string $date, string $start, string $end): string
{
    return date('D, M j, Y', strtotime($date)) . ' · '
        . date('g:i A', strtotime($start)) . ' – ' . date('g:i A', strtotime($end));
}
