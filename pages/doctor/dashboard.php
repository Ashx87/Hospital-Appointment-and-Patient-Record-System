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

<div class="doctor-dashboard">
    <br><img src = "../../assets/images/welcome.png" alt="" class="dashboard-icon">
    <h1>Welcome, <?= htmlspecialchars($_SESSION['name']) ?></h1><br>

    <div class="doctor-dashboard-grid">
        <div class="doctor-detail">
            <span>Department</span>
            <strong><?= htmlspecialchars($doctor['department'] ?? 'N/A') ?></strong>
        </div>
        <div class="doctor-detail">
            <span>Specialization</span>
            <strong><?= htmlspecialchars($doctor['specialization'] ?? 'N/A') ?></strong>
        </div>
    </div>

    <div class="info-card doctor-summary-card">
        <div class= "icon-box">
            <img src = "../../assets/images/calendar.png" alt="" class="summary-icon">
        </div>
        <p class="schedule-date">Date: <?= date('l, d F Y') ?></p>
        <h1><?= count($todayAppts) ?></h1>
        <p>Scheduled Appointment(s)</p>
    </div>

    <h2>Today's Appointments (<?= date('Y-m-d') ?>)</h2>
    <p><strong>Total Appointments:</strong> <?= count($todayAppts) ?></p><br>
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
                        <td><span class="status-badge status-<?= htmlspecialchars($appt['status']) ?>"><?= ucfirst(htmlspecialchars($appt['status'])) ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <br><br><hr>
    <div class="doctor-quick-container">
        <h2>Quick Actions</h2>

        <div class="quick-links">
            <a href="profile.php" class="quick-action-card">
                <img src = "../../assets/images/profile.png" alt="" class="dashboard-icon">
                <div>
                    <strong>My Profile</strong>
                    <span>Manage personal information</span>
                </div> 
            </a>
            <a href="my-slots.php" class="quick-action-card">
                <img src = "../../assets/images/clock.png" alt="" class="dashboard-icon">
                <div>
                    <strong>Manage My Slots</strong>
                    <span>Manage available schedule</span>
                </div>
            </a>
            <a href="my-appointments.php" class="quick-action-card">
                <img src = "../../assets/images/calendar.png" alt="" class="dashboard-icon">
                <div>
                    <strong>All Appointments</strong>
                    <span>View appointment records</span>
                </div>
            </a>
        </div>
    </div>
</div>
<?php require_once '../../includes/footer.php'; ?>