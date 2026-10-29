<?php
/**
 * includes/functions.php
 * Shared utility functions used across the entire application.
 */

require_once __DIR__ . '/../config/database.php';

// ─── Output ───────────────────────────────────────────────────────────────────

/** Escape a value for safe HTML output */
function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// ─── CSRF ─────────────────────────────────────────────────────────────────────

/** Return the current session CSRF token, creating one if absent */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Render a hidden CSRF input field */
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/** Verify CSRF token from POST; die on failure */
function verify_csrf(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals(csrf_token(), $token)) {
        http_response_code(403);
        exit('Invalid security token. Please go back and try again.');
    }
}

// ─── Auth helpers ─────────────────────────────────────────────────────────────

/** True if a user is logged in */
function is_logged_in(): bool {
    return !empty($_SESSION['user_id']);
}

/** Redirect to login if not authenticated */
function require_login(): void {
    if (!is_logged_in()) {
        header('Location: /login.php');
        exit;
    }
}

/** Return current user's id (int) or null */
function current_user_id(): ?int {
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

/** Fetch current user row from DB, or null */
function current_user(): ?array {
    $id = current_user_id();
    if ($id === null) return null;
    $db  = get_db();
    $stmt = $db->prepare('SELECT id, username, name, email, created_at FROM users WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

// ─── Validation ───────────────────────────────────────────────────────────────

/**
 * Validate a username: 3-40 chars, alphanumeric + underscores/hyphens only.
 * Returns error string or empty string if valid.
 */
function validate_username(string $u): string {
    if (strlen($u) < 3 || strlen($u) > 40) {
        return 'Username must be 3–40 characters.';
    }
    if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $u)) {
        return 'Username may only contain letters, numbers, underscores, and hyphens.';
    }
    return '';
}

/**
 * Check DB for duplicate username or email.
 * Returns ['username' => bool, 'email' => bool].
 */
function check_duplicates(string $username, string $email): array {
    $db   = get_db();
    $stmt = $db->prepare('SELECT username, email FROM users WHERE username = ? OR email = ?');
    $stmt->execute([$username, $email]);
    $result = ['username' => false, 'email' => false];
    foreach ($stmt->fetchAll() as $row) {
        if ($row['username'] === $username) $result['username'] = true;
        if ($row['email']    === $email)    $result['email']    = true;
    }
    return $result;
}

// ─── Flash messages ───────────────────────────────────────────────────────────

/** Store a flash message (shown once on next page) */
function flash(string $type, string $message): void {
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

/** Render and clear all flash messages */
function render_flash(): string {
    if (empty($_SESSION['flash'])) return '';
    $html = '';
    foreach ($_SESSION['flash'] as $f) {
        $cls  = e($f['type']); // 'success' | 'error' | 'info'
        $msg  = e($f['message']);
        $html .= "<div class=\"alert alert--{$cls}\" role=\"alert\">{$msg}</div>\n";
    }
    unset($_SESSION['flash']);
    return $html;
}

// ─── Redirect helper ──────────────────────────────────────────────────────────

function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

// ─── Spam / rate-limit ────────────────────────────────────────────────────────

/**
 * Lightweight session-based rate limit.
 * Returns true if the action is allowed, false if rate-limited.
 * ponytail: session-only, resets on browser close; fine for a college project.
 *           Upgrade path: store attempts in DB/Redis for persistent limits.
 *
 * @param string $action  unique key (e.g. 'submit_feedback')
 * @param int    $max     max allowed in window
 * @param int    $window  seconds
 */
function rate_limit(string $action, int $max = 5, int $window = 300): bool {
    $key = 'rl_' . $action;
    $now = time();

    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['count' => 0, 'start' => $now];
    }

    if ($now - $_SESSION[$key]['start'] > $window) {
        $_SESSION[$key] = ['count' => 0, 'start' => $now];
    }

    if ($_SESSION[$key]['count'] >= $max) {
        return false;
    }

    $_SESSION[$key]['count']++;
    return true;
}
