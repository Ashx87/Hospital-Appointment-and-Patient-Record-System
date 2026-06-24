<?php
/**
 * index.php — System Entry / Login Page
 *
 * GET  → Show login form (with flash message if redirected back after failure)
 * POST → Validate input → Auth::login() → redirect to dashboard on success
 *                                        → flash error + redirect back on failure
 */

session_start();
require_once 'config/config.php';
require_once 'classes/Database.php';
require_once 'classes/Auth.php';
require_once 'includes/flash.php';

// Already logged in — send straight to their dashboard
if (Auth::isLoggedIn()) {
    header('Location: ' . BASE_URL . 'pages/' . $_SESSION['role'] . '/dashboard.php');
    exit;
}

// ── POST: Process login form submission ──────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        setFlash('error', 'Email and password are required.');
        header('Location: ' . BASE_URL . 'index.php');
        exit;
    }

    if (Auth::login($email, $password)) {
        header('Location: ' . BASE_URL . 'pages/' . $_SESSION['role'] . '/dashboard.php');
        exit;
    }

    setFlash('error', 'Invalid email or password, or your account has been deactivated.');
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

// ── GET: Render login form ───────────────────────────────────────────────────
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Hospital Appointment System</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>

<main class="login-page">
    <?php displayFlash(); ?>

    <h1>Hospital Appointment System</h1>
    <p class="login-subtitle">Sign in to your account</p>

    <form method="POST" action="<?= BASE_URL ?>index.php" novalidate>
        <div class="form-group">
            <label for="email">Email Address</label>
            <input
                type="email"
                id="email"
                name="email"
                placeholder="you@example.com"
                required
                autocomplete="email"
            >
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                placeholder="••••••••"
                required
                autocomplete="current-password"
            >
        </div>

        <button type="submit" class="btn">Sign In</button>
    </form>
</main>

<script src="<?= BASE_URL ?>assets/js/app.js"></script>
</body>
</html>
