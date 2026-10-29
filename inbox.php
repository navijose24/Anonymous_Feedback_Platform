<?php
/**
 * inbox.php
 * Private inbox: list all feedback with filters, mark read/unread, save, delete.
 */

require_once __DIR__ . '/includes/auth.php';
require_login();

$uid = current_user_id();
$db  = get_db();

$filter   = $_GET['filter']   ?? 'all';
$category = $_GET['category'] ?? '';
$single_id = isset($_GET['id']) ? (int) $_GET['id'] : null;

// ── Single message view: mark as read ─────────────────────────────────────────
if ($single_id !== null) {
    $stmt = $db->prepare(
        'UPDATE feedback SET is_read = 1 WHERE id = ? AND receiver_id = ?'
    );
    $stmt->execute([$single_id, $uid]);
}

// ── Build query ───────────────────────────────────────────────────────────────
$where  = ['receiver_id = ?'];
$params = [$uid];

if ($filter === 'saved') {
    $where[] = 'is_saved = 1';
} elseif ($filter === 'unread') {
    $where[] = 'is_read = 0';
}

$allowed_cats = ['Appreciation', 'Constructive Criticism', 'Suggestion', 'Improvement'];
if ($category && in_array($category, $allowed_cats, true)) {
    $where[]  = 'category = ?';
    $params[] = $category;
}

$sql = 'SELECT id, category, message, is_read, is_saved, created_at
        FROM feedback
        WHERE ' . implode(' AND ', $where) . '
        ORDER BY created_at DESC';

$stmt = $db->prepare($sql);
$stmt->execute($params);
$messages = $stmt->fetchAll();

// ── If single message: fetch it for display ───────────────────────────────────
$single_msg = null;
if ($single_id !== null) {
    $stmt = $db->prepare(
        'SELECT id, category, message, is_read, is_saved, created_at
         FROM feedback
         WHERE id = ? AND receiver_id = ?'
    );
    $stmt->execute([$single_id, $uid]);
    $single_msg = $stmt->fetch() ?: null;
}

$page_title   = 'Inbox';
$current_page = ($filter === 'saved') ? 'saved' : 'inbox';

function nav_link(string $href, string $icon, string $label, string $page, string $current): string {
    $active = ($page === $current) ? ' active' : '';
    return "<a href=\"{$href}\" class=\"{$active}\">{$icon} {$label}</a>";
}

$badge_map = [
    'Appreciation'           => 'badge--appreciation',
    'Constructive Criticism' => 'badge--criticism',
    'Suggestion'             => 'badge--suggestion',
    'Improvement'            => 'badge--improvement',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inbox — AnonFeedback</title>
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<div class="dash-layout">

  <aside class="dash-sidebar" aria-label="Sidebar navigation">
    <div class="dash-sidebar__brand">💬 <span>Anon</span>Feedback</div>
    <nav class="dash-nav">
      <div class="dash-nav__section">Main</div>
      <?= nav_link('/dashboard.php', '📊', 'Dashboard', 'dashboard', $current_page) ?>
      <?= nav_link('/inbox.php', '📬', 'Inbox', 'inbox', $current_page) ?>
      <?= nav_link('/inbox.php?filter=saved', '🔖', 'Saved', 'saved', $current_page) ?>
      <div class="dash-nav__section">Account</div>
      <?= nav_link('/profile.php', '👤', 'Profile', 'profile', $current_page) ?>
      <a href="/logout.php" style="color: var(--error);">🚪 Log out</a>
    </nav>
  </aside>

  <main class="dash-main">

    <?= render_flash() ?>

    <div class="dash-header">
      <h1><?= $filter === 'saved' ? '🔖 Saved' : '📬 Inbox' ?></h1>
      <p><?= count($messages) ?> message<?= count($messages) !== 1 ? 's' : '' ?>
        <?= $filter === 'unread' ? ' unread' : ($filter === 'saved' ? ' saved' : '') ?>
      </p>
    </div>

    <!-- Filters -->
    <div style="display:flex;gap:var(--space-2);flex-wrap:wrap;margin-bottom:var(--space-6);">
      <a href="/inbox.php"                    class="btn btn--sm <?= $filter === 'all'    ? 'btn--primary' : 'btn--secondary' ?>">All</a>
      <a href="/inbox.php?filter=unread"      class="btn btn--sm <?= $filter === 'unread' ? 'btn--primary' : 'btn--secondary' ?>">Unread</a>
      <a href="/inbox.php?filter=saved"       class="btn btn--sm <?= $filter === 'saved'  ? 'btn--primary' : 'btn--secondary' ?>">Saved</a>
      <?php foreach ($allowed_cats as $cat): ?>
        <a href="/inbox.php?category=<?= urlencode($cat) ?>"
           class="btn btn--sm <?= $category === $cat ? 'btn--primary' : 'btn--secondary' ?>">
          <?= e($cat) ?>
        </a>
      <?php endforeach; ?>
    </div>

    <!-- Single message expanded view -->
    <?php if ($single_msg): ?>
      <div class="card mb-6">
        <div class="feedback-item__meta">
          <span class="badge <?= $badge_map[$single_msg['category']] ?? '' ?>"><?= e($single_msg['category']) ?></span>
          <span class="feedback-item__date"><?= e(date('F j, Y g:i A', strtotime($single_msg['created_at']))) ?></span>
        </div>
        <div class="feedback-item__message" style="font-size:var(--text-base); margin-top: var(--space-4);">
          <?= nl2br(e($single_msg['message'])) ?>
        </div>
        <div class="feedback-item__actions" style="margin-top: var(--space-5);">
          <form method="POST" action="/update-feedback.php" style="display:inline;">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $single_msg['id'] ?>">
            <input type="hidden" name="action" value="<?= $single_msg['is_saved'] ? 'unsave' : 'save' ?>">
            <button class="btn btn--secondary btn--sm">
              <?= $single_msg['is_saved'] ? '🔖 Unsave' : '🔖 Save' ?>
            </button>
          </form>
          <form method="POST" action="/update-feedback.php" style="display:inline;">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $single_msg['id'] ?>">
            <input type="hidden" name="action" value="<?= $single_msg['is_read'] ? 'mark_unread' : 'mark_read' ?>">
            <button class="btn btn--secondary btn--sm">
              <?= $single_msg['is_read'] ? '✉️ Mark unread' : '✅ Mark read' ?>
            </button>
          </form>
          <form method="POST" action="/update-feedback.php" style="display:inline;"
                onsubmit="return confirm('Delete this message?')">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $single_msg['id'] ?>">
            <input type="hidden" name="action" value="delete">
            <button class="btn btn--danger btn--sm">🗑 Delete</button>
          </form>
          <a href="/inbox.php" class="btn btn--secondary btn--sm">← Back to inbox</a>
        </div>
      </div>
    <?php endif; ?>

    <!-- Message list -->
    <?php if (empty($messages)): ?>
      <div class="empty-state">
        <div class="empty-state__icon">📭</div>
        <h3>Nothing here yet</h3>
        <p><?= $filter === 'saved' ? 'Save messages to find them here.' : 'Share your feedback link to start receiving messages.' ?></p>
      </div>
    <?php else: ?>
      <div class="feedback-list">
        <?php foreach ($messages as $fb): ?>
          <div class="feedback-item <?= $fb['is_read'] ? '' : 'unread' ?>" id="msg-<?= $fb['id'] ?>">
            <div class="feedback-item__meta">
              <span class="badge <?= $badge_map[$fb['category']] ?? '' ?>"><?= e($fb['category']) ?></span>
              <?php if (!$fb['is_read']): ?>
                <span class="badge badge--unread">New</span>
              <?php endif; ?>
              <?php if ($fb['is_saved']): ?>
                <span class="badge" style="background:rgba(210,153,34,0.15);color:#d29922;">🔖 Saved</span>
              <?php endif; ?>
              <span class="feedback-item__date"><?= e(date('M j, Y', strtotime($fb['created_at']))) ?></span>
            </div>
            <div class="feedback-item__message">
              <?= e(mb_substr($fb['message'], 0, 300)) ?><?= mb_strlen($fb['message']) > 300 ? '…' : '' ?>
            </div>
            <div class="feedback-item__actions">
              <a href="/inbox.php?id=<?= $fb['id'] ?>" class="btn btn--secondary btn--sm">Read</a>

              <form method="POST" action="/update-feedback.php" style="display:inline;">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= $fb['id'] ?>">
                <input type="hidden" name="action" value="<?= $fb['is_saved'] ? 'unsave' : 'save' ?>">
                <input type="hidden" name="redirect" value="/inbox.php?filter=<?= e($filter) ?>">
                <button class="btn btn--secondary btn--sm"><?= $fb['is_saved'] ? '🔖 Unsave' : '🔖 Save' ?></button>
              </form>

              <form method="POST" action="/update-feedback.php" style="display:inline;">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= $fb['id'] ?>">
                <input type="hidden" name="action" value="<?= $fb['is_read'] ? 'mark_unread' : 'mark_read' ?>">
                <input type="hidden" name="redirect" value="/inbox.php?filter=<?= e($filter) ?>">
                <button class="btn btn--secondary btn--sm"><?= $fb['is_read'] ? '✉️ Unread' : '✅ Read' ?></button>
              </form>

              <form method="POST" action="/update-feedback.php" style="display:inline;"
                    onsubmit="return confirm('Delete this message?')">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= $fb['id'] ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="redirect" value="/inbox.php?filter=<?= e($filter) ?>">
                <button class="btn btn--danger btn--sm">🗑</button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </main>
</div>

<script src="/assets/js/app.js"></script>
</body>
</html>
