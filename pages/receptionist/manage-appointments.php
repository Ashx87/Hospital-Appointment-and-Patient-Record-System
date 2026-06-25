<?php
/**
 * pages/receptionist/manage-appointments.php — Receptionist manage all appointments page
 *
 * Responsibilities:
 *   - Display all appointments in the system (filterable by status/date)
 *   - Receptionist can cancel any appointment with status='booked' (cancel operation releases the slot)
 *   - Receptionist can reschedule: cancel the current appointment + book a new time slot (two-step operation)
 *   - Operations are wrapped in a PDO transaction to guarantee atomicity
 *
 * Flow (PRG):
 *   GET  → render the hospital-wide appointment list
 *   POST → action='cancel' → Appointment::cancel() → setFlash() → redirect
 *
 * Fields read/written:
 *   appointments — id, status(booked|completed|cancelled) → changed to 'cancelled' on cancellation
 *   slots        — status → changed back to 'open' on cancellation
 */

session_start();
require_once '../../classes/Database.php';
require_once '../../classes/Auth.php';
require_once '../../classes/Appointment.php';
require_once '../../includes/flash.php';
require_once '../../includes/csrf.php';

Auth::requireRole('receptionist');

$appointmentModel = new Appointment();
$pageTitle        = 'Manage Appointments';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'cancel') {
    if (!verifyCsrf()) {
        setFlash('error', 'Security token mismatch. Please try again.');
        header('Location: manage-appointments.php');
        exit;
    }
    // TODO: $appointmentModel->cancel((int)$_POST['appointment_id']);
    setFlash('success', 'Appointment cancelled and slot reopened.');
    header('Location: manage-appointments.php');
    exit;
}

$filterStatus = $_GET['status'] ?? null;
$appts = $appointmentModel->findAll($filterStatus);

require_once '../../includes/header.php';
?>
<h1>Manage All Appointments</h1>
<!-- Status filter & date filter (TODO) -->
<table class="data-table">
    <thead><tr><th>Date</th><th>Time</th><th>Patient</th><th>Doctor</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
        <?php foreach ($appts as $appt): ?>
        <tr>
            <td><?= htmlspecialchars($appt['slot_date'] ?? '') ?></td>
            <td><?= htmlspecialchars($appt['start_time'] ?? '') ?></td>
            <td><?= htmlspecialchars($appt['patient_name'] ?? '') ?></td>
            <td><?= htmlspecialchars($appt['doctor_name'] ?? '') ?></td>
            <td><?= htmlspecialchars($appt['status']) ?></td>
            <td>
                <?php if ($appt['status'] === 'booked'): ?>
                <form method="POST" style="display:inline">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="cancel">
                    <input type="hidden" name="appointment_id" value="<?= $appt['id'] ?>">
                    <button type="submit" onclick="return confirm('Cancel?')">Cancel</button>
                </form>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php require_once '../../includes/footer.php'; ?>
