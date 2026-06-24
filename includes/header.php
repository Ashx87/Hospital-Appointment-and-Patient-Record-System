<?php
/**
 * includes/header.php — Shared page header template
 *
 * Responsibilities:
 *   - Output the HTML <head> shared by all pages (CSS link, meta tags)
 *   - Render the top navigation bar: show the current logged-in user's name and provide a logout link
 *   - Display navigation menu items corresponding to the user's role via $_SESSION['role']
 *   - Call displayFlash() to show one-time success/error messages
 *
 * Required via require_once in all pages; used together with includes/footer.php.
 * session_start() must have been called before including this file (each page is responsible for that at the top).
 */

if (session_status()===PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/flash.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?=$pageTitle??'Hospital Appointment System'?></title>
    <link rel="stylesheet" href="<?=BASE_URL?>assets/css/style.css">
</head>

<body>
<header>
    <nav>
        <a href="<?=BASE_URL?>index.php" class="logo">HospitalCare</a>
        <?php if(isset($_SESSION['user_id'])):?>
            <ul>
                <?php if($_SESSION['role']==='admin'): ?>
                    <li><a href="<?=BASE_URL?>pages/admin/dashboard.php">Dashboard</a></li>
                    <li><a href="<?=BASE_URL?>pages/admin/users.php">Users</a></li>
                    <li><a href="<?=BASE_URL?>pages/admin/doctors.php">Doctors</a></li>
                    <li><a href="<?=BASE_URL?>pages/admin/reports.php">Reports</a></li>
                <?php elseif($_SESSION['role']==='doctor'): ?>
                    <li><a href="<?=BASE_URL?>pages/doctor/dashboard.php">Dashboard</a></li>
                    <li><a href="<?=BASE_URL?>pages/doctor/my-slots.php">My Slots</a></li>
                    <li><a href="<?=BASE_URL?>pages/doctor/my-appointments.php">Appointments</a></li>
                <?php elseif($_SESSION['role']==='patient'): ?>
                    <li><a href="<?=BASE_URL?>pages/patient/dashboard.php">Dashboard</a></li>
                    <li><a href="<?=BASE_URL?>pages/patient/find-doctor.php">Find Doctor</a></li>
                    <li><a href="<?=BASE_URL?>pages/patient/my-appointments.php">My Appointments</a></li>
                    <li><a href="<?=BASE_URL?>pages/patient/my-records.php">My Records</a></li>
                <?php elseif($_SESSION['role']==='receptionist'): ?>
                    <li><a href="<?=BASE_URL?>pages/receptionist/dashboard.php">Dashboard</a></li>
                    <li><a href="<?=BASE_URL?>pages/receptionist/register-patient.php">Register Patient</a></li>
                    <li><a href="<?=BASE_URL?>pages/receptionist/manage-appointments.php">Appointments</a></li>
                <?php else:?>
                    <li><a href="<?=BASE_url?>index.php">Home</a></li>
                <?php endif;?>
            </ul>
    
            <div class="user-info">
                //use htmlspecialchars() prevent special chars doesnt break the HTML
                <span>Hello, <?= htmlspecialchars($_SESSION['name']) ?></span> |
                <a href="<?= BASE_URL ?>logout.php">Logout</a>
            </div>
        <?php endif;?>
    </nav>
</header>

<main class="container">
    <?php displayFlash();?>
