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
<br>
<div class="dashboard-grid">
    <div class="info-card">
        <h3>Patient Information</h3>
        <p>Blood Type: <?= htmlspecialchars($patient['blood_type'] ?? 'N/A') ?></p>
        <p>Allergies: <?= htmlspecialchars($patient['allergies'] ?? 'None') ?></p>
    </div>

    <div class="info-card">
        <h3>Appointment Summary</h3>
        <h1><?= count($upcomingAppts) ?></h1>
        <p>Upcoming Appointment(s)</p>
    </div>
</div>

<br>
<h2>Upcoming Appointments</h2>
<?php if (empty($upcomingAppts)): ?>
    <p>No appointments scheduled for today .</p>
<?php else: ?>

<table class="data-table">
    <thead>
        <tr>
            <th>Date</th>
            <th>Time</th>
            <th>Doctor</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($upcomingAppts as $appt): ?>
            <tr>
                <td><?= htmlspecialchars(substr($appt['start_time'],0,5)) ?> - <?= htmlspecialchars(substr($appt['end_time'],0,5)) ?></td>
                <td> <?= htmlspecialchars($appt['doctor_name']) ?></td>
                <td> <?= htmlspecialchars(ucfirst($appt['status'])) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
<hr>

<br>
<h2>Quick Actions</h2>
<div class="quick-links">
    <a href="profile.php" class="btn">👤 My Profile</a>
    <a href="find-doctor.php" class="btn">🔍 Find a Doctor</a>
    <a href="my-appointments.php" class="btn">📅 My Appointments</a>
    <a href="my-records.php" class="btn">📋 My Medical Records</a>
</div>
<?php require_once '../../includes/footer.php'; ?>
