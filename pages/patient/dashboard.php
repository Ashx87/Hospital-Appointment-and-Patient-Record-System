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
$upcomingAppts = $appointmentModel->findUpcomingByPatient($patient['id']);

require_once '../../includes/header.php';
?>

<div class="patient-dashboard">
    <br><img src = "../../assets/images/welcome.png" alt="" class="dashboard-icon">
    <h1>Welcome, <?= htmlspecialchars($_SESSION['name']) ?></h1><br>

    <div class="dashboard-grid">
        <div class="patient-detail">
            <span>Blood Type</span>
            <strong><?= htmlspecialchars($patient['blood_type'] ?? 'N/A') ?></strong>
        </div>
        <div class="patient-detail">
            <span>Allergies</span>
            <strong><?= htmlspecialchars($patient['allergies'] ?? 'None') ?></strong>
        </div>
    </div>

    <div class="info-card appointment-summary-card">
        <div class= "icon-box">
            <img src = "../../assets/images/calendar.png" alt="" class="summary-icon">
        </div>
        <h1><?= count($upcomingAppts) ?></h1>
        <p>Upcoming Appointment(s)</p>
    </div>

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
                        <td><?= htmlspecialchars($appt['slot_date']) ?></td>
                        <td><?= htmlspecialchars(substr($appt['start_time'],0,5)) ?> - <?= htmlspecialchars(substr($appt['end_time'],0,5)) ?></td>
                        <td><?= htmlspecialchars($appt['doctor_name']) ?></td>
                        <td><span class="status-badge status-<?= htmlspecialchars($appt['status']) ?>"><?= ucfirst(htmlspecialchars($appt['status'])) ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <br><br><hr>
    <div class="quick-action-container">
        <h2>Quick Actions</h2>

        <div class="quick-links">
            <a href="profile.php" class="quick-action-card">
                <img src = "../../assets/images/profile.png" alt="" class="dashboard-icon">
                <div>
                    <strong>My Profile</strong>
                    <span>Manage personal information</span>
                </div>
            </a>
            <a href="find-doctor.php" class="quick-action-card">
                <img src = "../../assets/images/search.png" alt="" class="dashboard-icon">
                <div>
                    <strong>Find a Doctor</strong>
                    <span>Search available doctors</span>
                </div>
            </a>
            <a href="my-appointments.php" class="quick-action-card">
                <img src = "../../assets/images/calendar.png" alt="" class="dashboard-icon">
                <div>
                    <strong>My Appointments</strong>
                    <span>View appointments</span>
                </div>
            </a>
            <a href="my-records.php" class="quick-action-card">
                <img src = "../../assets/images/record.png" alt="" class="dashboard-icon">
                <div>
                    <strong>Medical Records</strong>
                    <span>View consultation history</span>
                </div>
            </a>
        </div>
    </div>
</div>
<?php require_once '../../includes/footer.php'; ?>