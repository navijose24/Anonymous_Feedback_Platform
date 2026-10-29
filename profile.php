<?php
/**
 * profile.php
 * Shows account info, feedback link, and lets user change their display name.
 */

require_once __DIR__ . '/includes/auth.php';
require_login();

$user = current_user();
$uid  = current_user_id();
$db   = get_db();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim($_POST['name'] ?? '');

    if (strlen($name) < 2 || strlen($name) > 100) {
        $errors['name'] = 'Name must be 2–100 characters.';
    }

    if (empty($errors)) {
        $db->prepare('UPDATE users SET name = ? WHERE id = ?')->execute([$name, $uid]);
        $user['name'] = $name; // update in memory too
        $success = true;
        flash('success', 'Profile updated.');
    }
}

$feedback_link = (isset($_SERVER['HTTPS']) ? 'https' : 'http')
    . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')
    . '/f.php?u=' . urlencode($user['username']);

$page_title   = 'Profile';
$current_page = 'profile';

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
  <title>Profile — AnonFeedback</title>
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
      <h1>👤 Profile</h1>
      <p>Manage your account and feedback link.</p>
    </div>

    <div style="display:grid; grid-template-columns: 1fr 1fr; gap: var(--space-6); max-width: 800px;">

      <!-- Edit profile -->
      <div class="card">
        <h2 style="font-size:var(--text-base);font-weight:600;margin-bottom:var(--space-5);">Account details</h2>
        <form method="POST" action="/profile.php" id="profile-form">
          <?= csrf_field() ?>

          <div class="form-group">
            <label for="name">Display name</label>
            <input type="text" id="name" name="name" value="<?= e($user['name']) ?>" required>
            <?php if (!empty($errors['name'])): ?>
              <p class="field-error"><?= e($errors['name']) ?></p>
            <?php endif; ?>
          </div>

          <div class="form-group">
            <label>Username <span class="text-muted">(cannot be changed)</span></label>
            <input type="text" value="<?= e($user['username']) ?>" disabled>
          </div>

          <div class="form-group">
            <label>Email <span class="text-muted">(cannot be changed)</span></label>
            <input type="email" value="<?= e($user['email']) ?>" disabled>
          </div>

          <div class="form-group">
            <label>Member since</label>
            <input type="text" value="<?= e(date('F j, Y', strtotime($user['created_at']))) ?>" disabled>
          </div>

          <button type="submit" class="btn btn--primary btn--sm" id="btn-save-profile">Save changes</button>
        </form>
      </div>

      <!-- Feedback link -->
      <div class="card">
        <h2 style="font-size:var(--text-base);font-weight:600;margin-bottom:var(--space-5);">Your feedback link</h2>
        <div class="copy-box">
          <input type="text" id="feedback-link-input" value="<?= e($feedback_link) ?>" readonly>
          <button class="btn btn--primary btn--sm" onclick="copyFeedbackLink()">Copy</button>
        </div>
        <p class="field-hint mt-2">Share this link anywhere. No account required to send feedback.</p>
        <a href="<?= e($feedback_link) ?>" target="_blank" class="btn btn--secondary btn--sm" style="margin-top:var(--space-4);">
          Preview page ↗
        </a>
      </div>

    </div>

  </main>
</div>

<script src="/assets/js/app.js"></script>
</body>
</html>
