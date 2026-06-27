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
            try {
                $slotModel->create($doctor['id'], $_POST);
                setFlash('success', 'Slot created successfully.');
            } catch (Exception $e) {
                setFlash('error', 'Failed to create slot.');
            }
        } else {
            setFlash('error', implode(' ', $errors));
        }

    } elseif ($action === 'delete') {
        try {
            $slotModel->delete((int)$_POST['slot_id']);
            setFlash('success', 'Slot deleted.');
        } catch (Exception $e) {
            setFlash('error', $e->getMessage());
        }

    } elseif ($action === 'block') {
        try {
            $slotModel->updateStatus((int)$_POST['slot_id'], 'blocked');
            setFlash('success', 'Slot blocked.');
        } catch (Exception $e) {
            setFlash('error', 'Failed to block slot.');
        }
    }
    header('Location: my-slots.php');
    exit;
}

$filterDate = $_GET['date'] ?? null;
$slots = $slotModel->findByDoctor($doctor['id'], $filterDate);

require_once '../../includes/header.php';
?>
<h1>My Time Slots</h1>

<div class="info-card">
    <h2>Create New Slot</h2>

    <form method="POST">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="create">

        <div class="form-group">
            <label>Date</label>
            <input type="date" name="slot_date" required>
        </div>

        <div class="form-group">
            <label>Start Time</label>
            <input type="time" name="start_time" required>
        </div>

        <div class="form-group">
            <label>End Time</label>
            <input type="time" name="end_time" required>
        </div>

        <button type="submit" class="btn">Create Slot</button>
    </form>
</div>

<div class="info-card">
    <h2>Filter Slots</h2>

    <form method="GET" class="filter-bar">
        <div class="form-group">
            <label>Date</label>
            <input type="date" name="date" value="<?= htmlspecialchars($filterDate ?? '') ?>">
        </div>

        <button type="submit" class="btn">Filter</button>
        <a href="my-slots.php" class="btn btn--small">Reset</a>
    </form>
</div>

<table class="data-table">
    <thead><tr><th>Date</th><th>Start</th><th>End</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
        <?php foreach ($slots as $slot): ?>
        <tr>
            <td><?= htmlspecialchars($slot['slot_date']) ?></td>
            <td><?= htmlspecialchars($slot['start_time']) ?></td>
            <td><?= htmlspecialchars($slot['end_time']) ?></td>
            <td><?= htmlspecialchars($slot['status']) ?></td>
            <td>
                <?php if ($slot['status'] === 'open'): ?>
                    <form method="POST" class="inline-form" onsubmit="return confirm('Block this slot?');">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="block">
                        <input type="hidden" name="slot_id" value="<?= (int)$slot['id'] ?>">
                        <button type="submit" class="btn btn--small">Block</button>
                    </form>

                    <form method="POST" class="inline-form" onsubmit="return confirm('Delete this slot?');">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="slot_id" value="<?= (int)$slot['id'] ?>">
                        <button type="submit" class="btn btn--small btn--danger">Delete</button>
                    </form>
                <?php else: ?>
                    —
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php require_once '../../includes/footer.php'; ?>
