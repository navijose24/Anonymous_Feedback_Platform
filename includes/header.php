<?php
/**
 * includes/header.php
 * Shared HTML head + navbar. Call at top of every public page.
 *
 * Expected vars (set before including):
 *   $page_title  (string) — used in <title>
 *   $show_navbar (bool)   — defaults true; set false for auth pages
 */

$page_title  = $page_title  ?? 'Anonymous Feedback Platform';
$show_navbar = $show_navbar ?? true;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Share your unique feedback link. Receive anonymous messages from anyone — no accounts required.">
  <title><?= e($page_title) ?> — AnonFeedback</title>
  <link rel="stylesheet" href="/assets/css/style.css">
  <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>💬</text></svg>">
</head>
<body>
<?php if ($show_navbar): ?>
<nav class="navbar" role="navigation" aria-label="Main navigation">
  <div class="container navbar__inner">
    <a href="/index.php" class="navbar__logo" aria-label="AnonFeedback home">
      💬 <span>Anon</span>Feedback
    </a>
    <div class="navbar__links">
      <?php if (is_logged_in()): ?>
        <a href="/dashboard.php">Dashboard</a>
        <a href="/inbox.php">Inbox</a>
        <a href="/logout.php" class="btn btn--secondary btn--sm">Log out</a>
      <?php else: ?>
        <a href="/login.php">Log in</a>
        <a href="/register.php" class="btn btn--primary btn--sm">Get started</a>
      <?php endif; ?>
    </div>
  </div>
</nav>
<?php endif; ?>
