<?php
/**
 * register.php
 * User registration. Validates input, checks duplicates, hashes password.
 */

require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) redirect('/dashboard.php');

$errors = [];
$values = ['name' => '', 'username' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $name     = trim($_POST['name']     ?? '');
    $username = strtolower(trim($_POST['username'] ?? ''));
    $email    = strtolower(trim($_POST['email']    ?? ''));
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    $values = compact('name', 'username', 'email');

    // ── Validation ────────────────────────────────────────────────────────────
    if (strlen($name) < 2 || strlen($name) > 100) {
        $errors['name'] = 'Full name must be 2–100 characters.';
    }

    $username_err = validate_username($username);
    if ($username_err) $errors['username'] = $username_err;

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Enter a valid email address.';
    }

    if (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters.';
    }

    if ($password !== $confirm) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }

    // ── Duplicate check ───────────────────────────────────────────────────────
    if (empty($errors)) {
        $dupes = check_duplicates($username, $email);
        if ($dupes['username']) $errors['username'] = 'That username is already taken.';
        if ($dupes['email'])    $errors['email']    = 'That email is already registered.';
    }

    // ── Create account ────────────────────────────────────────────────────────
    if (empty($errors)) {
        $db   = get_db();
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $db->prepare(
            'INSERT INTO users (username, name, email, password_hash) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$username, $name, $email, $hash]);

        $user_id = (int) $db->lastInsertId();
        session_regenerate_id(true);
        $_SESSION['user_id']   = $user_id;
        $_SESSION['username']  = $username;

        flash('success', 'Welcome, ' . $name . '! Your account is ready.');
        redirect('/dashboard.php');
    }
}

$page_title  = 'Create account';
$show_navbar = false;
require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-page">
  <div class="auth-box">
    <div class="auth-box__logo">
      <div class="logo-icon">💬</div>
      <h1>Create your account</h1>
      <p class="auth-box__sub">Start receiving anonymous feedback today.</p>
    </div>

    <?= render_flash() ?>

    <form method="POST" action="/register.php" id="register-form" novalidate>
      <?= csrf_field() ?>

      <div class="form-group">
        <label for="name">Full name</label>
        <input type="text" id="name" name="name"
               value="<?= e($values['name']) ?>"
               placeholder="Your real name" autocomplete="name" required>
        <?php if (!empty($errors['name'])): ?>
          <p class="field-error"><?= e($errors['name']) ?></p>
        <?php endif; ?>
      </div>

      <div class="form-group">
        <label for="username">Username</label>
        <input type="text" id="username" name="username"
               value="<?= e($values['username']) ?>"
               placeholder="e.g. navaneetha" autocomplete="username" required>
        <p class="field-hint">This becomes your public link: <strong>/f/username</strong></p>
        <?php if (!empty($errors['username'])): ?>
          <p class="field-error"><?= e($errors['username']) ?></p>
        <?php endif; ?>
      </div>

      <div class="form-group">
        <label for="email">Email address</label>
        <input type="email" id="email" name="email"
               value="<?= e($values['email']) ?>"
               placeholder="you@example.com" autocomplete="email" required>
        <?php if (!empty($errors['email'])): ?>
          <p class="field-error"><?= e($errors['email']) ?></p>
        <?php endif; ?>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password"
               placeholder="Min. 8 characters" autocomplete="new-password" required>
        <?php if (!empty($errors['password'])): ?>
          <p class="field-error"><?= e($errors['password']) ?></p>
        <?php endif; ?>
      </div>

      <div class="form-group">
        <label for="confirm_password">Confirm password</label>
        <input type="password" id="confirm_password" name="confirm_password"
               placeholder="Repeat password" autocomplete="new-password" required>
        <?php if (!empty($errors['confirm_password'])): ?>
          <p class="field-error"><?= e($errors['confirm_password']) ?></p>
        <?php endif; ?>
      </div>

      <button type="submit" class="btn btn--primary btn--full" id="btn-register">
        Create account
      </button>
    </form>

    <p class="text-center text-sm mt-4">
      Already have an account? <a href="/login.php">Log in</a>
    </p>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
