<?php
/**
 * pages/receptionist/dashboard.php — Receptionist dashboard home page
 *
 * Responsibilities:
 *   - Verify the current user has the receptionist role (Auth::requireRole)
 *   - Display a today's appointment overview for the whole hospital (to help the receptionist understand the day's situation)
 *   - Provide quick-access links: register a walk-in patient, book on behalf of a patient, manage all appointments
 *
 * Data sources (read-only):
 *   appointments — status(booked|completed|cancelled), created_at
 *   slots        — slot_date(DATE:YYYY-MM-DD), start_time(TIME:HH:MM)
 *   users        — full_name (patient name, doctor name)
 */

session_start();
require_once '../../classes/Database.php';
require_once '../../classes/Auth.php';
require_once '../../classes/Appointment.php';
require_once '../../includes/flash.php';

Auth::requireRole('receptionist');

$appointmentModel = new Appointment();
$pageTitle        = 'Receptionist Dashboard';

// Today's hospital-wide appointment list
$todayAppts = $appointmentModel->findAll('booked');  // TODO: add date=today filter

require_once '../../includes/header.php';
?>
<h1>Receptionist Dashboard</h1>
<h2>Today's Overview</h2>
<!-- TODO: render today's appointment count -->
<div class="quick-links">
    <a href="register-patient.php" class="btn">Register Walk-in Patient</a>
    <a href="book-for-patient.php" class="btn">Book for Patient</a>
    <a href="manage-appointments.php" class="btn">Manage Appointments</a>
</div>
<?php require_once '../../includes/footer.php'; ?>
