<?php
/**
 * pages/doctor/my-slots.php — Doctor time slot management page
 *
 * Responsibilities:
 *   - Doctor views all their published time slots (filterable by date)
 *   - Create new open time slots (specify slot_date: YYYY-MM-DD, start_time/end_time: HH:MM)
 *   - Block a time slot (set status to blocked, not accepting bookings)
 *   - Delete only time slots with status='open'
 *
 * Flow (PRG):
 *   GET  → render the time slot list
 *   POST → validateSlot() → Slot::create/updateStatus/delete → setFlash() → redirect
 *
 * Fields read/written (slots table):
 *   id, doctor_id(FK→doctors), slot_date(DATE:YYYY-MM-DD),
 *   start_time(TIME:HH:MM), end_time(TIME:HH:MM), status(open|booked|blocked)
 */

session_start();
require_once '../../classes/Database.php';
require_once '../../classes/Auth.php';
require_once '../../classes/Doctor.php';
require_once '../../classes/Slot.php';
require_once '../../includes/flash.php';
require_once '../../includes/validation.php';
require_once '../../includes/csrf.php';

Auth::requireRole('doctor');

$doctorModel = new Doctor();
$slotModel   = new Slot();
$pageTitle   = 'My Slots';

$doctor = $doctorModel->findByUserId(Auth::userId());

// Guard: the logged-in user must have a doctor profile
if (!$doctor) {
    header('Location: ../../error.php?code=403&msg=Doctor+profile+not+found');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf()) {
        setFlash('error', 'Security token mismatch. Please try again.');
        header('Location: my-slots.php');
        exit;
    }
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $errors = validateSlot($_POST);
        if (empty($errors)) {
            // TODO: $slotModel->create($doctor['id'], $_POST);
            setFlash('success', 'Slot created successfully.');
        } else {
            setFlash('error', implode(' ', $errors));
        }
    } elseif ($action === 'delete') {
        // TODO: $slotModel->delete((int)$_POST['slot_id']);
        setFlash('success', 'Slot deleted.');
    } elseif ($action === 'block') {
        // TODO: $slotModel->updateStatus((int)$_POST['slot_id'], 'blocked');
        setFlash('success', 'Slot blocked.');
    }
    header('Location: my-slots.php');
    exit;
}

$filterDate = $_GET['date'] ?? null;
$slots = $slotModel->findByDoctor($doctor['id'], $filterDate);

require_once '../../includes/header.php';
?>
<h1>My Time Slots</h1>
<!-- Create time slot form & date filter (TODO) -->
<table class="data-table">
    <thead><tr><th>Date</th><th>Start</th><th>End</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
        <?php foreach ($slots as $slot): ?>
        <tr>
            <td><?= htmlspecialchars($slot['slot_date']) ?></td>
            <td><?= htmlspecialchars($slot['start_time']) ?></td>
            <td><?= htmlspecialchars($slot['end_time']) ?></td>
            <td><?= htmlspecialchars($slot['status']) ?></td>
            <td><!-- TODO: delete/block buttons --></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php require_once '../../includes/footer.php'; ?>
