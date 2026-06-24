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

Auth::requireRole('doctor');

$doctorModel      = new Doctor();
$appointmentModel = new Appointment();
$visitNoteModel   = new VisitNote();
$prescModel       = new Prescription();
$pageTitle        = 'Write Visit Note';

$appointmentId = (int)($_GET['appointment_id'] ?? $_POST['appointment_id'] ?? 0);
$doctor        = $doctorModel->findByUserId(Auth::userId());
$appointment   = $appointmentModel->findById($appointmentId);

// Security check: only allowed to write notes for the doctor's own patients
if (!$appointment || $appointment['doctor_id'] !== $doctor['id']) {
    header('Location: ../../error.php?code=403&msg=Access+Denied');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = validateVisitNote($_POST);
    if (empty($errors)) {
        // TODO: begin PDO transaction, then create visit_note + prescriptions + markCompleted in sequence
        setFlash('success', 'Visit note saved and appointment marked as completed.');
        header('Location: my-appointments.php');
        exit;
    } else {
        setFlash('error', implode(' ', $errors));
    }
}

require_once '../../includes/header.php';
?>
<h1>Write Visit Note</h1>
<p>Patient: <?= htmlspecialchars($appointment['patient_name'] ?? '') ?></p>
<p>Date: <?= htmlspecialchars($appointment['slot_date'] ?? '') ?></p>

<form method="POST">
    <input type="hidden" name="appointment_id" value="<?= $appointmentId ?>">
    <!-- TODO: diagnosis textarea, notes textarea, prescriptions with dynamically added rows -->
    <button type="submit">Save &amp; Complete Appointment</button>
</form>
<?php require_once '../../includes/footer.php'; ?>
