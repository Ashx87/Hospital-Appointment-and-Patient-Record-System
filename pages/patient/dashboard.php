<?php
/**
 * pages/patient/dashboard.php — Patient dashboard home page
 *
 * Responsibilities:
 *   - Verify the current user has the patient role (Auth::requireRole)
 *   - Display a summary of the patient's personal info (blood type, allergies)
 *   - Display upcoming appointments (status='booked')
 *   - Provide quick-access links: search for a doctor, view appointment records, view visit history
 *
 * Data sources (read-only):
 *   patients     — id, user_id, blood_type, allergies
 *   appointments — id, status(booked|completed|cancelled)
 *   slots        — slot_date(DATE:YYYY-MM-DD), start_time(TIME:HH:MM)
 */

session_start();
require_once '../../classes/Database.php';
require_once '../../classes/Auth.php';
require_once '../../classes/Patient.php';
require_once '../../classes/Appointment.php';
require_once '../../includes/flash.php';

Auth::requireRole('patient');

$patientModel     = new Patient();
$appointmentModel = new Appointment();
$pageTitle        = 'My Dashboard';

$patient      = $patientModel->findByUserId(Auth::userId());
$upcomingAppts = $appointmentModel->findByPatient($patient['id']);

require_once '../../includes/header.php';
?>
<h1>Welcome, <?= htmlspecialchars($_SESSION['name']) ?></h1>
<div class="info-card">
    <p>Blood Type: <?= htmlspecialchars($patient['blood_type'] ?? 'N/A') ?></p>
    <p>Allergies: <?= htmlspecialchars($patient['allergies'] ?? 'None') ?></p>
</div>
<h2>Upcoming Appointments</h2>
<!-- TODO: render upcoming appointments list -->
<div class="quick-links">
    <a href="profile.php" class="btn">My Profile</a>
    <a href="find-doctor.php" class="btn">Find a Doctor</a>
    <a href="my-appointments.php" class="btn">My Appointments</a>
    <a href="my-records.php" class="btn">My Medical Records</a>
</div>
<?php require_once '../../includes/footer.php'; ?>
