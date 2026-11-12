<?php
//small functions used everywhere

// Output 

// Escapes text before it is printed in HTML, so user input shows as plain text
// instead of running as code (protects against XSS).
//   e('<script>alert(1)</script>')  ->  &lt;script&gt;alert(1)&lt;/script&gt;
// ENT_QUOTES also escapes ' and ", so it is safe inside attributes like value="...".
// Views should print every database or user value through e(), e.g. e($tutor['full_name']).
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// Links 

// Builds a link to one of our pages. Every page goes through index.php (the front
// controller), and the router reads the page name from ?page=.
//   url('find-tutor')                 ->  index.php?page=find-tutor
//   url('tutor-profile', ['id' => 2]) ->  index.php?page=tutor-profile&id=2
// http_build_query() URL-encodes the values, so spaces and symbols are safe.
function url(string $page, array $params = []): string
{
    return 'index.php?' . http_build_query(['page' => $page] + $params);
}

// Sends the browser to another page and stops the script.
// Used at the end of every POST handler (Post/Redirect/Get), so refreshing the
// next page doesn't submit the form again.
//   redirect('my-bookings');
//   redirect('discussions-thread', ['id' => 7]);
// exit matters: without it, the code after the redirect would keep running.
function redirect(string $page, array $params = []): void
{
    header('Location: ' . url($page, $params));
    exit;
}

// Splits a stored link into the two arguments url() and redirect() expect.
// Notification links and "return to this page" values are saved as text, e.g.
//   "discussions-thread&id=7"  ->  ['discussions-thread', ['id' => '7']]
//   "page=resources"           ->  ['resources', []]
// Only lowercase page names and short simple values (letters, digits, _ and -) are
// kept; anything else is dropped, and a bad page name falls back to the dashboard.
// Because the result always goes through url(), it can only point to our own pages.
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

// Same as link_parts(), but returns the finished link for use in an href.
//   link_url('discussions-thread&id=7')  ->  index.php?page=discussions-thread&id=7
// The ... spreads the [page, params] array into url()'s two arguments.
function link_url(string $link): string
{
    return url(...link_parts($link));
}


// Login

// Returns the logged-in user's row (user_id, full_name, email, role, account_status, ...)
// or null if nobody is logged in.
// - Login only stores $_SESSION['user_id']; the rest is loaded from the database here,
//   so a change (like an admin deactivating the account) takes effect on the next click.
// - A deactivated or deleted account is logged out by removing user_id from the session.
// - static keeps the result for the rest of this request, so the users table is
//   queried at most once per page even though many files call this.
function current_user(): ?array
{
    static $user = false;   // false = not looked up yet; null = looked up, nobody logged in

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

// Messages 

// Saves a message to show once, on the next page. Used right before redirect(),
// because anything printed on this request would be lost by the redirect.
//   flash('success', 'Booking cancelled.');
//   flash('error', 'Please choose a schedule.');
// $key is 'success' (green) or 'error' (red); layouts/app.php prints both.
function flash(string $key, string $message): void
{
    $_SESSION['flash'][$key] = $message;
}

// Reads a flash message and deletes it, so it only appears once.
// Returns null if there is none. Called by the layout on every page.
function get_flash(string $key): ?string
{
    $message = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $message;
}


// Form input 

// Reads one field from a submitted (POST) form as a trimmed string.
//   input('reason')  ->  'Spam'   ('' if the field was not sent)
// Always returns a string, so checks like $reason === '' or mb_strlen($reason)
// work without extra isset() checks. Numbers are cast where they are used: (int) input('id').
// It does not escape anything: values go into SQL as ? parameters and are escaped
// with e() when printed.
function input(string $key): string
{
    return trim((string) ($_POST[$key] ?? ''));
}


//  Formatting (used by views) 

// Two-letter avatar text from a name: first letter of the first and last word.
//   initials('Juan Miguel Rivera')  ->  'JR'
//   initials('Admin')               ->  'A'
// mb_ functions handle letters like Ñ correctly.
function initials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name));
    $letters = mb_substr($parts[0], 0, 1);
    if (count($parts) > 1) {
        $letters .= mb_substr(end($parts), 0, 1);
    }
    return mb_strtoupper($letters);
}

// Display name for a role stored in users.role.
//   role_label('tutee')  ->  'Student / Tutee'
// Unknown values are returned unchanged.
function role_label(string $role): string
{
    return ['tutee' => 'Student / Tutee', 'tutor' => 'Tutor', 'admin' => 'Admin'][$role] ?? $role;
}

// Short "how long ago" text for a DATETIME from the database, e.g. for notifications.
//   under 1 minute -> 'Just now'     under 1 hour -> '5m ago'    under 1 day -> '3h ago'
//   1-2 days       -> 'Yesterday'    under 1 week -> '4d ago'    older       -> 'Oct 12'
// The numbers are seconds: 3600 = 1 hour, 86400 = 1 day, 604800 = 1 week.
// Depends on the timezone set in bootstrap.php (Asia/Manila) matching the database.
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

// Readable label for a tutoring slot, from its DATE and two TIME columns.
//   schedule_label('2026-12-01', '14:00:00', '15:00:00')
//     ->  'Tue, Dec 1, 2026 · 2:00 PM – 3:00 PM'
// Used on Tutor Profile, Book a Consultation and My Bookings.
function schedule_label(string $date, string $start, string $end): string
{
    return date('D, M j, Y', strtotime($date)) . ' · '
        . date('g:i A', strtotime($start)) . ' – ' . date('g:i A', strtotime($end));
}
