<?php
/**
 * f.php
 * Public feedback page: /f.php?u=username  OR with mod_rewrite: /f/username
 * Anyone can view this page and submit feedback — no login required.
 */

require_once __DIR__ . '/includes/auth.php';

$username = strtolower(trim($_GET['u'] ?? ''));

if (!$username || !preg_match('/^[a-zA-Z0-9_\-]+$/', $username)) {
    http_response_code(404);
    exit('Page not found.');
}

$db   = get_db();
$stmt = $db->prepare('SELECT id, name, username FROM users WHERE username = ? LIMIT 1');
$stmt->execute([$username]);
$recipient = $stmt->fetch();

if (!$recipient) {
    http_response_code(404);
    $page_title = 'User not found';
    require_once __DIR__ . '/includes/header.php';
    echo '<div class="container" style="padding-top:4rem; text-align:center;">';
    echo '<h1>😕 User not found</h1>';
    echo '<p style="color:var(--text-secondary); margin-top:1rem;">That feedback link doesn\'t exist.</p>';
    echo '</div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$submitted = false;
$errors    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    // Rate limit: max 5 submissions per 10 minutes per session
    if (!rate_limit('submit_feedback_' . $recipient['id'], 5, 600)) {
        $errors['rate'] = 'You\'ve sent too many messages recently. Please wait a few minutes.';
    } else {
        $message  = trim($_POST['message']  ?? '');
        $category = $_POST['category'] ?? 'Appreciation';

        $allowed_cats = ['Appreciation', 'Constructive Criticism', 'Suggestion', 'Improvement'];

        if (strlen($message) < 5) {
            $errors['message'] = 'Message must be at least 5 characters.';
        } elseif (strlen($message) > 2000) {
            $errors['message'] = 'Message is too long (max 2000 characters).';
        }

        if (!in_array($category, $allowed_cats, true)) {
            $category = 'Appreciation'; // silently correct invalid value
        }

        if (empty($errors)) {
            $stmt = $db->prepare(
                'INSERT INTO feedback (receiver_id, category, message) VALUES (?, ?, ?)'
            );
            $stmt->execute([$recipient['id'], $category, $message]);
            $submitted = true;
        }
    }
}

$page_title = 'Send anonymous feedback to ' . $recipient['name'];
require_once __DIR__ . '/includes/header.php';
?>

<main>
  <div class="feedback-page">

    <!-- Recipient avatar -->
    <div class="feedback-page__avatar" aria-hidden="true">
      <?= mb_strtoupper(mb_substr($recipient['name'], 0, 1)) ?>
    </div>

    <h1>Send anonymous feedback to <?= e($recipient['name']) ?></h1>
    <p class="feedback-page__anon-notice">
      🎭 Your name and identity will <strong>not</strong> be shared. No account needed.
    </p>

    <?php if (!empty($errors['rate'])): ?>
      <div class="alert alert--warning"><?= e($errors['rate']) ?></div>
    <?php endif; ?>

    <?php if ($submitted): ?>
      <div class="alert alert--success" id="success-msg">
        ✅ Your feedback was sent! Thank you for being honest.
      </div>
      <p class="text-center">
        <a href="/f.php?u=<?= e($username) ?>" class="btn btn--secondary btn--sm">Send another</a>
      </p>
    <?php else: ?>

    <form method="POST" action="/f.php?u=<?= e($username) ?>" id="feedback-form" novalidate>
      <?= csrf_field() ?>

      <div class="form-group">
        <label for="category">Category</label>
        <select id="category" name="category">
          <option value="Appreciation">💚 Appreciation</option>
          <option value="Constructive Criticism">🔴 Constructive Criticism</option>
          <option value="Suggestion">🔵 Suggestion</option>
          <option value="Improvement">🟡 Improvement</option>
        </select>
      </div>

      <div class="form-group">
        <label for="message">Your message</label>
        <textarea
          id="message"
          name="message"
          placeholder="Write your honest, anonymous feedback here..."
          maxlength="2000"
          required
        ><?= isset($message) ? e($message) : '' ?></textarea>
        <p class="field-hint" id="char-count">0 / 2000 characters</p>
        <?php if (!empty($errors['message'])): ?>
          <p class="field-error"><?= e($errors['message']) ?></p>
        <?php endif; ?>
      </div>

      <div class="alert alert--info" style="margin-bottom: var(--space-5);">
        ℹ️ This message is anonymous. We do not store your name, email, or account. Your hosting provider may retain server logs.
      </div>

      <button type="submit" class="btn btn--primary btn--full" id="btn-submit-feedback">
        Send feedback anonymously
      </button>
    </form>

    <?php endif; ?>
  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
