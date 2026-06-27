<?php
/**
 * pages/patient/my-appointments.php — Patient appointments list page
 *
 * Responsibilities:
 *   - Display the current patient's full appointment history (booked/completed/cancelled)
 *   - Support cancelling appointments with status='booked' (Appointment::cancel())
 *   - After cancellation, slot.status is set back to 'open' (done inside a PDO transaction)
 *
 * Flow (PRG):
 *   GET  → render the appointment list (filterable by status)
 *   POST → action='cancel' → Appointment::cancel() → setFlash() → redirect
 *
 * Fields read/written:
 *   appointments — id, status(booked|completed|cancelled) → changed to 'cancelled' on cancellation
 *   slots        — slot_date(DATE:YYYY-MM-DD), start_time(TIME:HH:MM), status → changed back to 'open' on cancellation
 */

session_start();
require_once '../../classes/Database.php';
require_once '../../classes/Auth.php';
require_once '../../classes/Patient.php';
require_once '../../classes/Appointment.php';
require_once '../../includes/flash.php';
require_once '../../includes/csrf.php';

Auth::requireRole('patient');

$patientModel     = new Patient();
$appointmentModel = new Appointment();
$pageTitle        = 'My Appointments';

$patient = $patientModel->findByUserId(Auth::userId());

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'cancel') {
    if (!verifyCsrf()) {
        setFlash('error', 'Security token mismatch. Please try again.');
        header('Location: my-appointments.php');
        exit;
    }

    try {
        $appointmentModel->cancel((int)$_POST['appointment_id']);
        setFlash('success', 'Appointment cancelled. The slot is now available again.');
    } catch (Exception $e){
        setFlash('error', $e->getMessage());
    }
    
    header('Location: my-appointments.php');
    exit;
}

$appts = $appointmentModel->findByPatient($patient['id']);

require_once '../../includes/header.php';
?>
<h1>My Appointments</h1>
<table class="data-table">
    <thead><tr><th>Date</th><th>Time</th><th>Doctor</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
        <?php foreach ($appts as $appt): ?>
        <tr>
            <td><?= htmlspecialchars($appt['slot_date']) ?></td>
            <td><?= htmlspecialchars($appt['start_time']) ?></td>
            <td><?= htmlspecialchars($appt['doctor_name'] ?? '') ?></td>
            <td><?= htmlspecialchars($appt['status']) ?></td>
            <td>
                <?php if ($appt['status'] === 'booked'): ?>
                <form method="POST">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="cancel">
                    <input type="hidden" name="appointment_id" value="<?= $appt['id'] ?>">
                    <button type="submit" onclick="return confirm('Cancel this appointment?')">Cancel</button>
                </form>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php require_once '../../includes/footer.php'; ?>
