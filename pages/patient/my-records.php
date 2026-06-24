<?php
/**
 * pages/patient/my-records.php — Patient visit records and prescription view page (read-only)
 *
 * Responsibilities:
 *   - Display all historical visit notes for the current patient (diagnosis, notes, visited_at)
 *   - Each record expands to show the prescriptions for that visit (medicine_name, dosage, instructions)
 *   - Completely read-only: patients cannot modify visit notes or prescriptions
 *
 * Data sources (read-only JOIN queries):
 *   visit_notes   — id, appointment_id, doctor_id, diagnosis(TEXT), notes(TEXT), visited_at(DATETIME:YYYY-MM-DD HH:MM:SS)
 *   prescriptions — id, visit_note_id, medicine_name(VARCHAR), dosage(VARCHAR), instructions(TEXT)
 *   appointments  — patient_id, slot_id
 *   slots         — slot_date(DATE:YYYY-MM-DD)
 *   users         — full_name (doctor name)
 */

session_start();
require_once '../../classes/Database.php';
require_once '../../classes/Auth.php';
require_once '../../classes/Patient.php';
require_once '../../classes/VisitNote.php';
require_once '../../classes/Prescription.php';
require_once '../../includes/flash.php';

Auth::requireRole('patient');

$patientModel   = new Patient();
$visitNoteModel = new VisitNote();
$prescModel     = new Prescription();
$pageTitle      = 'My Medical Records';

$patient    = $patientModel->findByUserId(Auth::userId());
$visitNotes = $visitNoteModel->findByPatient($patient['id']);

require_once '../../includes/header.php';
?>
<h1>My Medical Records</h1>
<?php if (empty($visitNotes)): ?>
    <p>No visit records found.</p>
<?php else: ?>
    <?php foreach ($visitNotes as $note): ?>
    <div class="record-card">
        <h3>Visit on <?= htmlspecialchars($note['slot_date'] ?? '') ?> — Dr. <?= htmlspecialchars($note['doctor_name'] ?? '') ?></h3>
        <p><strong>Diagnosis:</strong> <?= htmlspecialchars($note['diagnosis']) ?></p>
        <p><strong>Notes:</strong> <?= htmlspecialchars($note['notes'] ?? '') ?></p>
        <?php $prescriptions = $prescModel->findByVisitNote($note['id']); ?>
        <?php if (!empty($prescriptions)): ?>
        <h4>Prescriptions</h4>
        <ul>
            <?php foreach ($prescriptions as $rx): ?>
            <li><?= htmlspecialchars($rx['medicine_name']) ?> — <?= htmlspecialchars($rx['dosage']) ?>: <?= htmlspecialchars($rx['instructions'] ?? '') ?></li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
<?php endif; ?>
<?php require_once '../../includes/footer.php'; ?>
