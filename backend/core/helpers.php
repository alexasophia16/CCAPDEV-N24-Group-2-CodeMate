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
