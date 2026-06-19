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

session_start();
session_destroy();

header('Location: index.php');
exit;
