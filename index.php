<?php
/**
 * index.php — System Entry / Login Page
 *
 * Responsibilities:
 *   - Unauthenticated users see the login form
 *   - On POST: verify credentials, write to $_SESSION, redirect to role-specific dashboard
 *   - Logged-in users are redirected directly to their dashboard (prevents duplicate login)
 *
 * Flow (PRG pattern):
 *   GET  → Render login form
 *   POST → Validate → Success: header(Location: pages/{role}/dashboard.php)
 *                  → Failure: flash error message, redirect back to GET
 */

session_start();
require_once 'config/Database.php';
require_once 'classes/Auth.php';
require_once 'includes/flash.php';

// If already logged in, redirect to role-specific dashboard
if (Auth::isLoggedIn()) {
    $role = $_SESSION['role'];
    header("Location: pages/{$role}/dashboard.php");
    exit;
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // TODO: Validate email / password, call Auth::login()
    // Success → redirect; Failure → flash error and return
}

require_once 'includes/header.php';
?>

<main class="login-page">
    <h1>Hospital Appointment System</h1>
    <!-- TODO: Login form HTML -->
</main>

<?php require_once 'includes/footer.php'; ?>
