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
require_once 'classes/Database.php';
require_once 'classes/Auth.php';
require_once 'includes/flash.php';

//If already logged in, redirect to role-specific dashboard
if (Auth::isLoggedIn()) {
    $role = $_SESSION['role'];
    header("Location: pages/{$role}/dashboard.php");
    exit;
}

//Login form
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $email=$_POST['email']??'';
    $password=$_POST['password']??'';

    if(Auth::login($email, $password)){
        $role=$_SESSION['role'];
        header("Location: pages/{$role}/dashboard.php");
        exit;
    }else{
        setFlash('error','Invalid email or password.');
        header('Location: index.php');
        exit;
    }
}

require_once 'includes/header.php';
?>

<main class="login-page">
    <h1>Hospital Appointment System</h1>
    <form method="POST" action="index.php">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" required>
            <button type="submit" class="btn">Login</button>
        </div>
    </form>
</main>

<?php require_once 'includes/footer.php'; ?>
