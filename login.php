<?php
/**
 * login.php
 * Authenticates existing users with password_verify().
 */

require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) redirect('/dashboard.php');

$errors = [];
$email  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $email    = strtolower(trim($_POST['email']    ?? ''));
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $errors['general'] = 'Please enter your email and password.';
    } else {
        $db   = get_db();
        $stmt = $db->prepare('SELECT id, username, name, password_hash FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']  = (int) $user['id'];
            $_SESSION['username'] = $user['username'];
            flash('success', 'Welcome back, ' . $user['name'] . '!');
            redirect('/dashboard.php');
        } else {
            // Generic message — don't reveal whether email exists
            $errors['general'] = 'Invalid email or password.';
        }
    }
}

$page_title  = 'Log in';
$show_navbar = false;
require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-page">
  <div class="auth-box">
    <div class="auth-box__logo">
      <div class="logo-icon">💬</div>
      <h1>Welcome back</h1>
      <p class="auth-box__sub">Log in to your AnonFeedback inbox.</p>
    </div>

    <?= render_flash() ?>

    <?php if (!empty($errors['general'])): ?>
      <div class="alert alert--error"><?= e($errors['general']) ?></div>
    <?php endif; ?>

    <form method="POST" action="/login.php" id="login-form" novalidate>
      <?= csrf_field() ?>

      <div class="form-group">
        <label for="email">Email address</label>
        <input type="email" id="email" name="email"
               value="<?= e($email) ?>"
               placeholder="you@example.com" autocomplete="email" required>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password"
               placeholder="Your password" autocomplete="current-password" required>
      </div>

      <button type="submit" class="btn btn--primary btn--full" id="btn-login">
        Log in
      </button>
    </form>

    <p class="text-center text-sm mt-4">
      Don't have an account? <a href="/register.php">Create one free</a>
    </p>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
