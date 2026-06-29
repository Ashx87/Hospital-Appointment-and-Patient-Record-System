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

function dashboardIcon(string $name): string
{
    $icons = [
        'wave' => '<path d="M7 11V5a2 2 0 0 1 4 0v5"/><path d="M11 10V3a2 2 0 0 1 4 0v8"/><path d="M15 10V5a2 2 0 0 1 4 0v7c0 5-3 8-8 8-3 0-5-2-7-5l-2-3a2 2 0 0 1 3-3l2 2"/>',
        'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
        'user' => '<path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/>',
        'search' => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
        'clipboard' => '<rect x="5" y="3" width="14" height="18" rx="2"/><path d="M9 3h6v4H9z"/><path d="M9 12h6M9 16h6"/>'
    ];
    $inner = $icons[$name] ?? '';
    return '<svg class="patient-icon" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">'
    .$inner.'</svg>';
}

Auth::requireRole('patient');

$patientModel     = new Patient();
$appointmentModel = new Appointment();
$pageTitle        = 'My Dashboard';

$patient      = $patientModel->findByUserId(Auth::userId());
$upcomingAppts = $appointmentModel->findUpcomingByPatient($patient['id']);

require_once '../../includes/header.php';
?>

<div class="patient-dashboard">
    <h1 class="patient-page-title"><?= dashboardIcon('wave') ?>
        Welcome, <?= htmlspecialchars($_SESSION['name']) ?>
    </h1>

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
        <?= dashboardIcon('calendar') ?>
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

    <div class="quick-action-container">
        <h2>Quick Actions</h2>

        <div class="quick-links">
            <a href="profile.php" class="quick-action-card"><?= dashboardIcon('user') ?>
                <div>
                    <strong>My Profile</strong>
                    <span>Manage personal information</span>
                </div>
            </a>
            <a href="find-doctor.php" class="quick-action-card"><?= dashboardIcon('search') ?>
                <div>
                    <strong>Find a Doctor</strong>
                    <span>Search available doctors</span>
                </div>
            </a>
            <a href="my-appointments.php" class="quick-action-card"><?= dashboardIcon('calendar') ?>
                <div>
                    <strong>My Appointments</strong>
                    <span>View appointments</span>
                </div>
            </a>
            <a href="my-records.php" class="quick-action-card"><?= dashboardIcon('clipboard') ?>
                <div>
                    <strong>Medical Records</strong>
                    <span>View consultation history</span>
                </div>
            </a>
        </div>
    </div>
</div>
<?php require_once '../../includes/footer.php'; ?>