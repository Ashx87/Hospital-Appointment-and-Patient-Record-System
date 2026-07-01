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

$totalAppointments = count($appts);
$upcomingAppointments = count(array_filter($appts, function($a){
    return $a['status'] === 'booked';
}));

$completedAppointments = count(array_filter($appts, function($a){
    return $a['status'] === 'completed';
}));

require_once '../../includes/header.php';
?>

<h1 class="patient-page-title">
    <img src = "../../assets/images/clock.png" alt="" class="header-icon">My Appointments
</h1>
<p class="form-hint">View and manage your upcoming and previous appointments.</p>
<div class="appointment-summary">
    <div class="info-card">
        <h3>Total Appointments</h3>
        <h1><?= $totalAppointments ?></h1>
    </div>

    <div class="info-card">
        <h3>Upcoming</h3>
        <h1><?= $upcomingAppointments ?></h1>
    </div>

    <div class="info-card">
        <h3>Completed</h3>
        <h1><?= $completedAppointments ?></h1>
    </div>
</div>

<h2 class="appointment-history-title">Appointment History</h2>
<table class="data-table">
    <thead><tr><th>Date</th><th>Time</th><th>Doctor</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
        <?php foreach ($appts as $appt): ?>
        <tr>
            <td><?= htmlspecialchars($appt['slot_date']) ?></td>
            <td><?= htmlspecialchars($appt['start_time']) ?></td>
            <td><?= htmlspecialchars($appt['doctor_name'] ?? '') ?></td>
            <td>
                <span class="status-badge status-<?= htmlspecialchars($appt['status']) ?>">
                    <?= ucfirst(htmlspecialchars($appt['status'])) ?>
                </span>
            </td>
            <td>
                <?php if ($appt['status'] === 'booked'): ?>
                <form method="POST">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="cancel">
                    <input type="hidden" name="appointment_id" value="<?= $appt['id'] ?>">
                    <button type="submit" class="appointment-cancel-btn" onclick="return confirm('Cancel this appointment?')">
                        <img src="../../assets/images/cancel.png" alt="" class="button-icon">
                        Cancel
                    </button>
                </form>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php require_once '../../includes/footer.php'; ?>