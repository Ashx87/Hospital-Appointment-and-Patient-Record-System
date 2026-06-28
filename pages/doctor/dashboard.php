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
require_once '../../classes/Database.php';
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
<br>
<div class="dashboard-grid">
    <div class="info-card">
        <h3>Doctor Information</h3>
        <p>Department: <?= htmlspecialchars($doctor['department'] ?? 'N/A') ?></p>
        <p>Specialization: <?= htmlspecialchars($doctor['specialization'] ?? 'N/A') ?></p>
    </div>

    <div class="info-card">
        <h3>Today's Schedule</h3>
        <h1><?= count($todayAppts) ?></h1>
        <p>Scheduled Appointment(s)</p>
        <p>Date: <?= date('l, d F Y') ?></p>
    </div>
</div>

<br>
<h2>Today's Appointments (<?= date('Y-m-d') ?>)</h2>
<p><strong>Total Appointments Today:</strong> <?= count($todayAppts) ?></p>
<?php if (empty($todayAppts)): ?>
    <p>No appointments scheduled for today.</p>
<?php else: ?>

<table class="data-table">
    <thead>
        <tr>
            <th>Time</th>
            <th>Patient</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($todayAppts as $appt): ?>
            <tr>
                <td><?= htmlspecialchars(substr($appt['start_time'],0,5)) ?> - <?= htmlspecialchars(substr($appt['end_time'],0,5)) ?></td>
                <td><?= htmlspecialchars($appt['patient_name']) ?></td>
                <td><?= htmlspecialchars(ucfirst($appt['status'])) ?></td>
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
    <a href="my-slots.php" class="btn">🕒 Manage My Slots</a>
    <a href="my-appointments.php" class="btn">📅 All Appointments</a>
</div>

<?php require_once '../../includes/footer.php'; ?>
