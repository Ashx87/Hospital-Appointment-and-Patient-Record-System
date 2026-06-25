<?php
/**
 * includes/csrf.php — CSRF (Cross-Site Request Forgery) protection helpers
 *
 * Responsibilities:
 *   - Issue a per-session random token (csrfToken) used to prove that a
 *     state-changing POST request originated from our own rendered form
 *   - csrfField(): emit the hidden <input> to drop inside every POST <form>
 *   - verifyCsrf(): called at the top of every POST handler before any write;
 *     compares the submitted token against the session token with hash_equals
 *     (constant-time comparison, immune to timing attacks)
 *
 * Session structure (no DB reads/writes — pure session operations):
 *   $_SESSION['csrf_token'] — 64-char hex string, generated once per session
 *
 * Usage:
 *   require_once '../../includes/csrf.php';   // after session_start()
 *   // In the POST handler, before touching the database:
 *   if (!verifyCsrf()) { setFlash('error', '...'); header('Location: ...'); exit; }
 *   // In the HTML form:
 *   <form method="POST"><?= csrfField() ?> ... </form>
 *
 * session_start() must have been called before any of these functions run
 * (every page does that at the top).
 */

/** Return the current session CSRF token, generating one on first use. */
function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Hidden form field carrying the CSRF token; drop this inside every POST form. */
function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrfToken()) . '">';
}

/**
 * Validate the token submitted with a POST request.
 * Returns true only when a session token exists and matches the submitted one.
 */
function verifyCsrf(): bool
{
    $submitted = $_POST['csrf_token'] ?? '';
    return !empty($_SESSION['csrf_token'])
        && is_string($submitted)
        && hash_equals($_SESSION['csrf_token'], $submitted);
}
