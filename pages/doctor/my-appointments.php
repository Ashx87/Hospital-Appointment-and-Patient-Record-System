<?php
/**
 * pages/doctor/my-appointments.php — Doctor appointments list page
 *
 * Responsibilities:
 *   - Doctor views all their appointments filtered by date (defaults to today)
 *   - Each appointment shows: patient name, time slot, status (booked|completed|cancelled)
 *   - Provides a link to write-note.php to write a visit note for a booked appointment
 *   - Read-only view (appointment status is changed to completed by write-note.php)
 *
 * Data sources (read-only JOIN queries):
 *   appointments — id, status, patient_id
 *   slots        — slot_date(DATE:YYYY-MM-DD), start_time(TIME:HH:MM)
 *   patients     — id
 *   users        — full_name (patient name)
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
$pageTitle        = 'My Appointments';

$doctor     = $doctorModel->findByUserId(Auth::userId());
$filterDate = $_GET['date'] ?? date('Y-m-d');
$appts      = $appointmentModel->findByDoctor($doctor['id'], $filterDate);

require_once '../../includes/header.php';
?>
<h1>My Appointments</h1>
<!-- Date filter form (TODO) -->
<table class="data-table">
    <thead><tr><th>Time</th><th>Patient</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
        <?php foreach ($appts as $appt): ?>
        <tr>
            <td><?= htmlspecialchars($appt['start_time']) ?></td>
            <td><?= htmlspecialchars($appt['patient_name'] ?? '') ?></td>
            <td><?= htmlspecialchars($appt['status']) ?></td>
            <td>
                <?php if ($appt['status'] === 'booked'): ?>
                    <a href="write-note.php?appointment_id=<?= $appt['id'] ?>">Write Note</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php require_once '../../includes/footer.php'; ?>
