<?php
/**
 * logout.php — Logout Handler
 *
 * Responsibilities:
 *   - Destroy the current session (clears all login state and flash messages)
 *   - Redirect back to the index.php login page
 *
 * No HTML rendering needed — pure redirect logic.
 * Triggered by the "Logout" link in the includes/header.php navigation bar.
 */

require_once 'config/config.php';   // defines BASE_URL for the redirect

session_start();

// Clear all session data, then expire the session cookie, then destroy the session
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}
session_destroy();

header('Location: ' . BASE_URL . 'index.php');
exit;
