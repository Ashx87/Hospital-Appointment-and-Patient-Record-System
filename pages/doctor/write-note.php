<?php
/**
 * pages/doctor/write-note.php — Doctor write visit note page
 *
 * Responsibilities:
 *   - Doctor writes a visit note (diagnosis + notes) for a booked appointment
 *   - Also issues prescriptions (medicine_name, dosage, instructions), multiple allowed
 *   - On submission: create visit_note → create prescriptions → mark appointment as completed
 *   - All three steps are performed inside a PDO transaction (atomicity guaranteed)
 *
 * Flow (PRG):
 *   GET  → render the visit note form (including patient information)
 *   POST → validateVisitNote() → transactional write → setFlash() → redirect to my-appointments.php
 *
 * Fields read/written:
 *   visit_notes   — id, appointment_id(FK), doctor_id(FK), diagnosis(TEXT), notes(TEXT), visited_at(DATETIME:NOW())
 *   prescriptions — id, visit_note_id(FK), medicine_name(VARCHAR), dosage(VARCHAR), instructions(TEXT)
 *   appointments  — status changed to 'completed'
 */

session_start();
require_once '../../classes/Database.php';
require_once '../../classes/Auth.php';
require_once '../../classes/Doctor.php';
require_once '../../classes/Appointment.php';
require_once '../../classes/VisitNote.php';
require_once '../../classes/Prescription.php';
require_once '../../includes/flash.php';
require_once '../../includes/validation.php';
require_once '../../includes/csrf.php';

Auth::requireRole('doctor');

$pdo              = Database::getInstance();
$doctorModel      = new Doctor();
$appointmentModel = new Appointment();
$visitNoteModel   = new VisitNote();
$prescModel       = new Prescription();
$pageTitle        = 'Write Visit Note';

$appointmentId = (int)($_GET['appointment_id'] ?? $_POST['appointment_id'] ?? 0);
$doctor        = $doctorModel->findByUserId(Auth::userId());

// Guard: the logged-in user must have a doctor profile before doing anything
if (!$doctor) {
    header('Location: ../../error.php?code=403&msg=Doctor+profile+not+found');
    exit;
}

$appointment = $appointmentModel->findById($appointmentId);

if (!$appointment || (int)$appointment['doctor_id'] !== (int)$doctor['id']) {
    header('Location: ../../error.php?code=403&msg=Access+Denied');
    exit;
}

if ($appointment['status'] !== 'booked') {
    setFlash('error', 'Only booked appointments can be completed.');
    header('Location: my-appointments.php');
    exit;
}

$existingNote = $visitNoteModel->findByAppointment($appointmentId);

if ($existingNote) {
    setFlash('error', 'This appointment already has a visit note.');
    header('Location: my-appointments.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verifyCsrf()) {
        setFlash('error', 'Security token mismatch. Please try again.');
        header('Location: my-appointments.php');
        exit;
    }

    $errors = validateVisitNote($_POST);

    $medicineNames = $_POST['medicine_name'] ?? [];
    $dosages       = $_POST['dosage'] ?? [];
    $instructions  = $_POST['instructions'] ?? [];

    $hasPrescription = false;

    foreach ($medicineNames as $name) {
        if (trim($name) !== '') {
            $hasPrescription = true;
            break;
        }
    }

    if (!$hasPrescription) {
        $errors[] = 'Please enter at least one prescription medicine.';
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            $visitNoteId = $visitNoteModel->create(
                $appointmentId,
                (int)$doctor['id'],
                $_POST
            );

            foreach ($medicineNames as $index => $medicineName) {
                if (trim($medicineName) === '') {
                    continue;
                }

                $prescModel->create($visitNoteId, [
                    'medicine_name' => $medicineName,
                    'dosage' => $dosages[$index] ?? '',
                    'instructions' => $instructions[$index] ?? ''
                ]);
            }

            $appointmentModel->markCompleted($appointmentId);

            $pdo->commit();

            setFlash('success', 'Visit note saved and appointment marked as completed.');
            header('Location: my-appointments.php');
            exit;

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            error_log('write-note.php error: '.$e->getMessage());
            setFlash('error', 'Failed to save visit note. Please try again.');
        }
    } else {
        setFlash('error', implode(' ', $errors));
    }
}

require_once '../../includes/header.php';
?>

<h1>Write Visit Note</h1>

<div class="info-card">
    <p><strong>Patient:</strong> <?= htmlspecialchars($appointment['patient_name'] ?? '') ?></p>
    <p><strong>Doctor:</strong> <?= htmlspecialchars($appointment['doctor_name'] ?? '') ?></p>
    <p><strong>Date:</strong> <?= htmlspecialchars($appointment['slot_date'] ?? '') ?></p>
    <p>
        <strong>Time:</strong>
        <?= htmlspecialchars(substr($appointment['start_time'], 0, 5)) ?>
        -
        <?= htmlspecialchars(substr($appointment['end_time'], 0, 5)) ?>
    </p>
</div>

<div class="info-card">
    <form method="POST">
        <?= csrfField() ?>

        <input type="hidden" name="appointment_id" value="<?= (int)$appointmentId ?>">

        <div class="form-group">
            <label>Diagnosis</label>
            <textarea
                name="diagnosis"
                rows="4"
                required><?= htmlspecialchars($_POST['diagnosis'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label>Visit Notes</label>
            <textarea
                name="visit_notes"
                rows="5"
                required><?= htmlspecialchars($_POST['visit_notes'] ?? '') ?></textarea>
        </div>

        <h2>Prescriptions</h2>
        <p class="form-hint">Enter one or more medicines for this visit.</p>

        <div class="info-card">
            <h3>Prescription 1</h3>

            <div class="form-group">
                <label>Medicine Name</label>
                <input
                    type="text"
                    name="medicine_name[]"
                    value="<?= htmlspecialchars($_POST['medicine_name'][0] ?? '') ?>"
                    required>
            </div>

            <div class="form-group">
                <label>Dosage</label>
                <input
                    type="text"
                    name="dosage[]"
                    value="<?= htmlspecialchars($_POST['dosage'][0] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Instructions</label>
                <textarea
                    name="instructions[]"
                    rows="3"><?= htmlspecialchars($_POST['instructions'][0] ?? '') ?></textarea>
            </div>
        </div>

        <div class="info-card">
            <h3>Prescription 2</h3>

            <div class="form-group">
                <label>Medicine Name</label>
                <input
                    type="text"
                    name="medicine_name[]"
                    value="<?= htmlspecialchars($_POST['medicine_name'][1] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Dosage</label>
                <input
                    type="text"
                    name="dosage[]"
                    value="<?= htmlspecialchars($_POST['dosage'][1] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Instructions</label>
                <textarea
                    name="instructions[]"
                    rows="3"><?= htmlspecialchars($_POST['instructions'][1] ?? '') ?></textarea>
            </div>
        </div>

        <div class="info-card">
            <h3>Prescription 3</h3>

            <div class="form-group">
                <label>Medicine Name</label>
                <input
                    type="text"
                    name="medicine_name[]"
                    value="<?= htmlspecialchars($_POST['medicine_name'][2] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Dosage</label>
                <input
                    type="text"
                    name="dosage[]"
                    value="<?= htmlspecialchars($_POST['dosage'][2] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Instructions</label>
                <textarea
                    name="instructions[]"
                    rows="3"><?= htmlspecialchars($_POST['instructions'][2] ?? '') ?></textarea>
            </div>
        </div>

        <button type="submit" class="btn">
            Save & Complete Appointment
        </button>

        <a href="my-appointments.php" class="btn btn--small">
            Back
        </a>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>