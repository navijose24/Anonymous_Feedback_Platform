<?php
/**
 * dashboard.php
 * Private dashboard: stats + category breakdown + quick link copy.
 * Requires authentication.
 */

require_once __DIR__ . '/includes/auth.php';
require_login();

$user = current_user();
$db   = get_db();
$uid  = current_user_id();

// ── Stats queries ─────────────────────────────────────────────────────────────
$stmt = $db->prepare('SELECT COUNT(*) FROM feedback WHERE receiver_id = ?');
$stmt->execute([$uid]);
$total = (int) $stmt->fetchColumn();

$stmt = $db->prepare('SELECT COUNT(*) FROM feedback WHERE receiver_id = ? AND is_read = 0');
$stmt->execute([$uid]);
$unread = (int) $stmt->fetchColumn();

$stmt = $db->prepare('SELECT COUNT(*) FROM feedback WHERE receiver_id = ? AND is_saved = 1');
$stmt->execute([$uid]);
$saved = (int) $stmt->fetchColumn();

// ── Category breakdown ────────────────────────────────────────────────────────
$stmt = $db->prepare(
    'SELECT category, COUNT(*) AS cnt
     FROM feedback
     WHERE receiver_id = ?
     GROUP BY category
     ORDER BY cnt DESC'
);
$stmt->execute([$uid]);
$cats = $stmt->fetchAll();

// ── Recent feedback (5) ───────────────────────────────────────────────────────
$stmt = $db->prepare(
    'SELECT id, category, message, is_read, is_saved, created_at
     FROM feedback
     WHERE receiver_id = ?
     ORDER BY created_at DESC
     LIMIT 5'
);
$stmt->execute([$uid]);
$recent = $stmt->fetchAll();

// ── Feedback link ─────────────────────────────────────────────────────────────
$feedback_link = (isset($_SERVER['HTTPS']) ? 'https' : 'http')
    . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')
    . '/f.php?u=' . urlencode($user['username']);

$page_title = 'Dashboard';
$current_page = 'dashboard';

// Sidebar helper
function nav_link(string $href, string $icon, string $label, string $page, string $current): string {
    $active = ($page === $current) ? ' active' : '';
    return "<a href=\"{$href}\" class=\"{$active}\">{$icon} {$label}</a>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard — AnonFeedback</title>
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<div class="dash-layout">

  <!-- Sidebar -->
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

  <!-- Main content -->
  <main class="dash-main">

    <?= render_flash() ?>

    <div class="dash-header">
      <h1>Dashboard</h1>
      <p>Welcome back, <?= e($user['name']) ?>! Here's your feedback overview.</p>
    </div>

    <!-- Stat Cards -->
    <div class="stat-grid">
      <div class="stat-card">
        <div class="stat-card__icon">📩</div>
        <div class="stat-card__label">Total messages</div>
        <div class="stat-card__value"><?= $total ?></div>
      </div>
      <div class="stat-card">
        <div class="stat-card__icon">🔵</div>
        <div class="stat-card__label">Unread</div>
        <div class="stat-card__value"><?= $unread ?></div>
      </div>
      <div class="stat-card">
        <div class="stat-card__icon">🔖</div>
        <div class="stat-card__label">Saved</div>
        <div class="stat-card__value"><?= $saved ?></div>
      </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-6); margin-bottom: var(--space-8);">

      <!-- Your link -->
      <div class="card">
        <h2 style="font-size:var(--text-base);font-weight:600;margin-bottom:var(--space-4);">🔗 Your feedback link</h2>
        <div class="copy-box">
          <input type="text" id="feedback-link-input" value="<?= e($feedback_link) ?>" readonly>
          <button class="btn btn--primary btn--sm" id="copy-btn" onclick="copyFeedbackLink()">Copy</button>
        </div>
        <p class="field-hint mt-2">Share this link to receive anonymous feedback.</p>
        <a href="<?= e($feedback_link) ?>" target="_blank" class="btn btn--secondary btn--sm" style="margin-top:var(--space-3);">
          Preview page ↗
        </a>
      </div>

      <!-- Category breakdown -->
      <div class="card">
        <h2 style="font-size:var(--text-base);font-weight:600;margin-bottom:var(--space-4);">📊 Category breakdown</h2>
        <?php if (empty($cats)): ?>
          <p class="text-muted text-sm">No feedback yet.</p>
        <?php else: ?>
          <div class="cat-list">
            <?php foreach ($cats as $cat): ?>
              <?php $pct = $total > 0 ? round(($cat['cnt'] / $total) * 100) : 0; ?>
              <div class="cat-row">
                <span class="cat-row__label"><?= e($cat['category']) ?></span>
                <div class="cat-bar-wrap">
                  <div class="cat-bar" style="width: <?= $pct ?>%"></div>
                </div>
                <span class="cat-row__count"><?= $cat['cnt'] ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

    </div>

    <!-- Recent messages -->
    <div>
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--space-4);">
        <h2 style="font-size:var(--text-base);font-weight:600;">📬 Recent messages</h2>
        <a href="/inbox.php" class="btn btn--secondary btn--sm">View all</a>
      </div>

      <?php if (empty($recent)): ?>
        <div class="empty-state">
          <div class="empty-state__icon">💌</div>
          <h3>No feedback yet</h3>
          <p>Share your link and wait for the honest thoughts to roll in.</p>
        </div>
      <?php else: ?>
        <div class="feedback-list">
          <?php foreach ($recent as $fb): ?>
            <?php
              $cat_slug = strtolower(str_replace(' ', '-', $fb['category']));
              $badge_map = [
                'appreciation'          => 'badge--appreciation',
                'constructive-criticism'=> 'badge--criticism',
                'suggestion'            => 'badge--suggestion',
                'improvement'           => 'badge--improvement',
              ];
              $badge_cls = $badge_map[$cat_slug] ?? 'badge--suggestion';
            ?>
            <div class="feedback-item <?= $fb['is_read'] ? '' : 'unread' ?>">
              <div class="feedback-item__meta">
                <span class="badge <?= $badge_cls ?>"><?= e($fb['category']) ?></span>
                <?php if (!$fb['is_read']): ?>
                  <span class="badge badge--unread">New</span>
                <?php endif; ?>
                <span class="feedback-item__date"><?= e(date('M j, Y', strtotime($fb['created_at']))) ?></span>
              </div>
              <div class="feedback-item__message">
                <?= e(mb_substr($fb['message'], 0, 200)) ?><?= mb_strlen($fb['message']) > 200 ? '…' : '' ?>
              </div>
              <div class="feedback-item__actions">
                <a href="/inbox.php?id=<?= $fb['id'] ?>" class="btn btn--secondary btn--sm">Read</a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

  </main>
</div>

<script src="/assets/js/app.js"></script>
</body>
</html>
