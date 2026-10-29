<?php
/**
 * index.php
 * Public landing page. Redirects logged-in users to dashboard.
 */

require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    redirect('/dashboard.php');
}

$page_title = 'Home';
require_once __DIR__ . '/includes/header.php';
?>

<main>
  <!-- Hero -->
  <section class="hero">
    <div class="container container--mid">
      <div class="hero__badge">100% Anonymous</div>
      <h1 class="hero__title">
        Say what you really think.<br>
        <span class="accent">Without revealing who you are.</span>
      </h1>
      <p class="hero__tagline">
        Get honest feedback from anyone. No accounts, no names, no filters.
        Just real thoughts — delivered straight to your private inbox.
      </p>
      <div class="hero__actions">
        <a href="/register.php" class="btn btn--primary" id="cta-register">Create your link — it's free</a>
        <a href="/login.php"    class="btn btn--secondary" id="cta-login">Log in</a>
      </div>
    </div>
  </section>

  <!-- Feature highlights -->
  <section style="padding-block: var(--space-10); border-top: 1px solid var(--border);">
    <div class="container">
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px,1fr)); gap: var(--space-6);">

        <div class="card">
          <div style="font-size:1.75rem; margin-bottom: var(--space-3);">🔗</div>
          <h3 style="font-size: var(--text-base); font-weight:600; margin-bottom: var(--space-2);">Your unique link</h3>
          <p style="font-size: var(--text-sm); color: var(--text-secondary);">
            Get a personal URL like <code style="color:var(--accent-light)">/f/yourname</code> to share anywhere.
          </p>
        </div>

        <div class="card">
          <div style="font-size:1.75rem; margin-bottom: var(--space-3);">🎭</div>
          <h3 style="font-size: var(--text-base); font-weight:600; margin-bottom: var(--space-2);">No account needed to send</h3>
          <p style="font-size: var(--text-sm); color: var(--text-secondary);">
            Anyone can send you a message — no sign-up, no email, no name required.
          </p>
        </div>

        <div class="card">
          <div style="font-size:1.75rem; margin-bottom: var(--space-3);">📬</div>
          <h3 style="font-size: var(--text-base); font-weight:600; margin-bottom: var(--space-2);">Private dashboard</h3>
          <p style="font-size: var(--text-sm); color: var(--text-secondary);">
            Read, save, and organize all your feedback in one clean inbox.
          </p>
        </div>

        <div class="card">
          <div style="font-size:1.75rem; margin-bottom: var(--space-3);">🔒</div>
          <h3 style="font-size: var(--text-base); font-weight:600; margin-bottom: var(--space-2);">Security-first</h3>
          <p style="font-size: var(--text-sm); color: var(--text-secondary);">
            Hashed passwords, CSRF protection, prepared statements, and session hardening throughout.
          </p>
        </div>

      </div>
    </div>
  </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
