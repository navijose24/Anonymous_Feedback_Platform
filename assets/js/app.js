/**
 * assets/js/app.js
 * Minimal client-side enhancements — no frameworks, vanilla JS only.
 */

'use strict';

// ─── Copy feedback link ───────────────────────────────────────────────────────
function copyFeedbackLink() {
  const input = document.getElementById('feedback-link-input');
  const btn   = document.getElementById('copy-btn');
  if (!input) return;

  navigator.clipboard.writeText(input.value).then(() => {
    if (btn) {
      const orig = btn.textContent;
      btn.textContent = 'Copied!';
      btn.disabled    = true;
      setTimeout(() => { btn.textContent = orig; btn.disabled = false; }, 2000);
    }
  }).catch(() => {
    // Fallback for HTTP (localhost without HTTPS)
    input.select();
    document.execCommand('copy');
  });
}

// ─── Character counter for feedback textarea ──────────────────────────────────
(function () {
  const textarea  = document.getElementById('message');
  const counter   = document.getElementById('char-count');
  if (!textarea || !counter) return;

  function update() {
    const len = textarea.value.length;
    counter.textContent = len + ' / 2000 characters';
    counter.style.color = len > 1900 ? 'var(--error)' : '';
  }

  textarea.addEventListener('input', update);
  update();
})();

// ─── Auto-dismiss alerts after 5 seconds ─────────────────────────────────────
(function () {
  document.querySelectorAll('.alert--success').forEach(el => {
    setTimeout(() => {
      el.style.transition = 'opacity 0.5s';
      el.style.opacity    = '0';
      setTimeout(() => el.remove(), 500);
    }, 5000);
  });
})();

// ─── Confirm on delete forms (extra safety on top of onsubmit attr) ───────────
(function () {
  document.querySelectorAll('form[data-confirm]').forEach(form => {
    form.addEventListener('submit', e => {
      if (!confirm(form.dataset.confirm)) e.preventDefault();
    });
  });
})();
