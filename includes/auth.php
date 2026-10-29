<?php
/**
 * includes/auth.php
 * Session bootstrap. Include this at the top of every page.
 */

// Secure session configuration
ini_set('session.cookie_httponly', '1');
ini_set('session.use_strict_mode', '1');
// session.cookie_secure requires HTTPS; set to 1 on production
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? '1' : '0');
ini_set('session.cookie_samesite', 'Lax');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/functions.php';
