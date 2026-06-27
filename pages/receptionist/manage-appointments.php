<?php
/**
 * pages/receptionist/manage-appointments.php
 *
 * Receptionist appointment management page.
 */

session_start();

require_once '../../classes/Auth.php';
require_once '../../classes/Appointment.php';
require_once '../../includes/flash.php';
require_once '../../includes/csrf.php';

Auth::requireRole('receptionist');

$appointmentModel = new Appointment();
$pageTitle = 'Manage Appointments';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf()) {
        setFlash('error', 'Security token mismatch. Please try again.');
        header('Location: manage-appointments.php');
        exit;
    }

    $action = $_POST['action'] ?? '';
    $appointmentId = (int)($_POST['appointment_id'] ?? 0);

    if ($action === 'cancel' && $appointmentId > 0) {
        try {
            $appointmentModel->cancel($appointmentId);
            setFlash('success', 'Appointment cancelled successfully.');
        } catch (Exception $e) {
            setFlash('error', $e->getMessage());
        }
    }

    header('Location: manage-appointments.php');
    exit;
}

$status = $_GET['status'] ?? '';
$appointments = $appointmentModel->findAll($status);

require_once '../../includes/header.php';
?>

<h1>Manage Appointments</h1>

<p class="form-hint">
View all appointments and cancel bookings when needed.
</p>

<div class="info-card">

    <form method="GET" class="filter-bar">

        <div class="form-group">

            <label>Status</label>

            <select name="status">

                <option value="">All</option>

                <option value="booked" <?= $status === 'booked' ? 'selected' : '' ?>>Booked</option>

                <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>Completed</option>

                <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>

            </select>

        </div>

        <button type="submit" class="btn">Filter</button>

        <a href="manage-appointments.php" class="btn btn--small">Reset</a>

    </form>

</div>

<table class="data-table">

    <thead>

        <tr>

            <th>Date</th>

            <th>Time</th>

            <th>Patient</th>

            <th>Doctor</th>

            <th>Booked By</th>

            <th>Status</th>

            <th>Action</th>

        </tr>

    </thead>

    <tbody>

        <?php if (empty($appointments)): ?>

            <tr>

                <td colspan="7">No appointments found.</td>

            </tr>

        <?php else: ?>

            <?php foreach ($appointments as $row): ?>

                <tr>

                    <td><?= htmlspecialchars($row['slot_date']) ?></td>

                    <td>

                        <?= htmlspecialchars(substr($row['start_time'], 0, 5)) ?>-<?= htmlspecialchars(substr($row['end_time'], 0, 5)) ?>

                    </td>

                    <td><?= htmlspecialchars($row['patient_name']) ?></td>
                    
                    <td><?= htmlspecialchars($row['doctor_name']) ?></td>

                    <td><?= htmlspecialchars($row['booked_by_name']) ?></td>

                    <td>

                        <span class="badge badge--<?= htmlspecialchars($row['status']) ?>">

                            <?= htmlspecialchars(ucfirst($row['status'])) ?>

                        </span>

                    </td>

                    <td>

                        <?php if ($row['status'] === 'booked'): ?>

                            <form method="POST" class="inline-form" onsubmit="return confirm('Cancel this appointment?');">

                                <?= csrfField() ?>

                                <input type="hidden" name="action" value="cancel">

                                <input type="hidden" name="appointment_id" value="<?= (int)$row['id'] ?>">

                                <button type="submit" class="btn btn--small btn--danger">Cancel</button>

                            </form>

                            <a class="btn btn--small" href="book-for-patient.php?patient_id=<?= (int)$row['patient_id'] ?>">Reschedule</a>

                        <?php else: ?>—<?php endif; ?>

                    </td>

                </tr>

            <?php endforeach; ?>

        <?php endif; ?>

    </tbody>
    
</table>

<?php require_once '../../includes/footer.php'; ?>