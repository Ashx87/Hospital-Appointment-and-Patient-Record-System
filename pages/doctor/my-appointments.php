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

$doctor = $doctorModel->findByUserId(Auth::userId());

// Guard: the logged-in user must have a doctor profile
if (!$doctor) {
    header('Location: ../../error.php?code=403&msg=Doctor+profile+not+found');
    exit;
}

$filterDate = $_GET['date'] ?? date('Y-m-d');
$appts      = $appointmentModel->findByDoctor($doctor['id'], $filterDate);

require_once '../../includes/header.php';
?>
<h1>My Appointments</h1>

<p class="form-hint">View appointments by date and write visit notes for booked appointments.</p>

<div class="info-card">
    <form method="GET" class="filter-bar">
        <div class="form-group">
            <label>Date</label>
            <input type="date" name="date" value="<?= htmlspecialchars($filterDate) ?>">
        </div>

        <button type="submit" class="btn">Filter</button>

        <a href="my-appointments.php" class="btn btn--small">Today</a>
    </form>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th>Time</th>
            <th>Patient</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>

    <tbody>
        <?php if (empty($appts)): ?>
            <tr>
                <td colspan="4">No appointments found for this date.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($appts as $appt): ?>
                <tr>
                    <td>
                        <?= htmlspecialchars(substr($appt['start_time'], 0, 5)) ?>
                        <?php if (!empty($appt['end_time'])): ?>
                            -
                            <?= htmlspecialchars(substr($appt['end_time'], 0, 5)) ?>
                        <?php endif; ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($appt['patient_name'] ?? '') ?>
                    </td>

                    <td>
                        <span class="badge badge--<?= htmlspecialchars($appt['status']) ?>">
                            <?= htmlspecialchars(ucfirst($appt['status'])) ?>
                        </span>
                    </td>

                    <td>
                        <?php if ($appt['status'] === 'booked'): ?>
                            <a href="write-note.php?appointment_id=<?= (int)$appt['id'] ?>" class="btn btn--small">
                                Write Note
                            </a>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<?php require_once '../../includes/footer.php'; ?>
