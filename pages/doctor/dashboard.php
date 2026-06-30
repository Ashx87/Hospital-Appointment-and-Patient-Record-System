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

function doctorIcon(string $name): string
{
    $icons = [
        'wave' => '<path d="M7 11V5a2 2 0 0 1 4 0v5"/><path d="M11 10V3a2 2 0 0 1 4 0v8"/><path d="M15 10V5a2 2 0 0 1 4 0v7c0 5-3 8-8 8-3 0-5-2-7-5l-2-3a2 2 0 0 1 3-3l2 2"/>',
        'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
        'user' => '<path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/>',
        'clock' => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>'    ];
    $inner = $icons[$name] ?? '';
    return '<svg class="doctor-icon" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">'
    .$inner.'</svg>';
}

Auth::requireRole('doctor');

$doctorModel      = new Doctor();
$appointmentModel = new Appointment();
$pageTitle        = 'Doctor Dashboard';

$doctor     = $doctorModel->findByUserId(Auth::userId());
$todayAppts = $appointmentModel->findByDoctor($doctor['id'], date('Y-m-d'));

require_once '../../includes/header.php';
?>

<div class="doctor-dashboard">
    <h1 class="doctor-page-title"><?= doctorIcon('wave') ?>
        Welcome, <?= htmlspecialchars($_SESSION['name']) ?>
    </h1>

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
        <?= doctorIcon('calendar') ?>
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
            <a href="profile.php" class="quick-action-card"><?= doctorIcon('user') ?>
                <div>
                    <strong>My Profile</strong>
                    <span>Manage personal information</span>
                </div> 
            </a>
            <a href="my-slots.php" class="quick-action-card"><?= doctorIcon('clock') ?>
                <div>
                    <strong>Manage My Slots</strong>
                    <span>Manage available schedule</span>
                </div>
            </a>
            <a href="my-appointments.php" class="quick-action-card"><?= doctorIcon('calendar') ?>
                <div>
                    <strong>All Appointments</strong>
                    <span>View appointment records</span>
                </div>
            </a>
        </div>
    </div>
</div>
<?php require_once '../../includes/footer.php'; ?>