<?php
/**
 * error.php — Generic Error / 404 Page
 *
 * Responsibilities:
 *   - Display a user-friendly error message without exposing server internals
 *   - Accepts URL parameters ?code=404&msg=... to specify the error type
 *   - All pages redirect here when an unrecoverable error occurs
 *
 * Usage example (from other pages):
 *   header('Location: /error.php?code=403&msg=Access+Denied');
 */

session_start();
require_once 'config/config.php';   // defines BASE_URL, used by header.php (this path never loads Database.php)
require_once 'includes/header.php';

// XSS protection: escape URL parameters with htmlspecialchars
$code = htmlspecialchars($_GET['code'] ?? '500');
$msg  = htmlspecialchars($_GET['msg']  ?? 'An unexpected error occurred.');
?>

<main class="error-page">
    <h1>Error <?= $code ?></h1>
    <p><?= $msg ?></p>
    <a href="index.php">Return to Home</a>
</main>

<?php require_once 'includes/footer.php'; ?>
