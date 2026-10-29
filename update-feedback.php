<?php
/**
 * update-feedback.php
 * Handles all inbox actions: mark_read, mark_unread, save, unsave, delete.
 * POST only. Requires authentication. Verifies ownership before every mutation.
 */

require_once __DIR__ . '/includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

verify_csrf();

$uid        = current_user_id();
$id         = isset($_POST['id'])     ? (int) $_POST['id']     : 0;
$action     = $_POST['action']     ?? '';
$redirect   = $_POST['redirect']   ?? '/inbox.php';

// Sanitize redirect — only allow relative paths starting with /
if (!preg_match('#^/[a-zA-Z0-9/_\-?=&]*$#', $redirect)) {
    $redirect = '/inbox.php';
}

if (!$id) {
    flash('error', 'Invalid request.');
    redirect($redirect);
}

$db = get_db();

// ── Ownership check — always verify receiver_id = current user ────────────────
$stmt = $db->prepare('SELECT id FROM feedback WHERE id = ? AND receiver_id = ?');
$stmt->execute([$id, $uid]);
if (!$stmt->fetch()) {
    http_response_code(403);
    flash('error', 'You do not have permission to modify that message.');
    redirect($redirect);
}

// ── Dispatch action ───────────────────────────────────────────────────────────
switch ($action) {
    case 'mark_read':
        $db->prepare('UPDATE feedback SET is_read = 1 WHERE id = ? AND receiver_id = ?')
           ->execute([$id, $uid]);
        flash('success', 'Marked as read.');
        break;

    case 'mark_unread':
        $db->prepare('UPDATE feedback SET is_read = 0 WHERE id = ? AND receiver_id = ?')
           ->execute([$id, $uid]);
        flash('success', 'Marked as unread.');
        break;

    case 'save':
        $db->prepare('UPDATE feedback SET is_saved = 1 WHERE id = ? AND receiver_id = ?')
           ->execute([$id, $uid]);
        flash('success', 'Message saved.');
        break;

    case 'unsave':
        $db->prepare('UPDATE feedback SET is_saved = 0 WHERE id = ? AND receiver_id = ?')
           ->execute([$id, $uid]);
        flash('success', 'Message unsaved.');
        break;

    case 'delete':
        $db->prepare('DELETE FROM feedback WHERE id = ? AND receiver_id = ?')
           ->execute([$id, $uid]);
        flash('success', 'Message deleted.');
        $redirect = '/inbox.php'; // always go back to list after delete
        break;

    default:
        flash('error', 'Unknown action.');
}

redirect($redirect);
