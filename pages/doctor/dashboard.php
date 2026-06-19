<?php
/**
 * pages/doctor/dashboard.php — Doctor dashboard home page
 *
 * Responsibilities:
 *   - Verify the current user has the doctor role (Auth::requireRole)
 *   - Display a summary of the doctor's personal info (department, specialization)
 *   - Display today's pending appointment count and the upcoming appointments list
 *   - Provide quick-access links: manage time slots, view appointments
 *
 * Data sources (read-only queries):
 *   doctors      — id, user_id, specialization, department
 *   appointments — status(booked|completed|cancelled)
 *   slots        — slot_date(DATE), start_time(TIME)
 */

session_start();
require_once '../../config/Database.php';
require_once '../../classes/Auth.php';
require_once '../../classes/Doctor.php';
require_once '../../classes/Appointment.php';
require_once '../../includes/flash.php';

Auth::requireRole('doctor');

$doctorModel      = new Doctor();
$appointmentModel = new Appointment();
$pageTitle        = 'Doctor Dashboard';

$doctor     = $doctorModel->findByUserId(Auth::userId());
$todayAppts = $appointmentModel->findByDoctor($doctor['id'], date('Y-m-d'));

require_once '../../includes/header.php';
?>

<h1>Welcome, Dr. <?= htmlspecialchars($_SESSION['name']) ?></h1>

<div class="info-card">
    <p>Department: <?= htmlspecialchars($doctor['department'] ?? 'N/A') ?></p>
    <p>Specialization: <?= htmlspecialchars($doctor['specialization'] ?? 'N/A') ?></p>
</div>

<h2>Today's Appointments (<?= date('Y-m-d') ?>)</h2>
<!-- TODO: render today's appointment list -->

<div class="quick-links">
    <a href="my-slots.php" class="btn">Manage My Slots</a>
    <a href="my-appointments.php" class="btn">All Appointments</a>
</div>

<?php require_once '../../includes/footer.php'; ?>
