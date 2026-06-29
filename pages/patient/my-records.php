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

function patientRecordIcon(string $name): string
{
    $icons = [
'record' => '
<rect x="4" y="3" width="16" height="19" rx="2"/>
<path d="M9 2h6v4H9z"/>
<path d="M12 10v6M9 13h6"/>
',        'details' => '<path d="M4 4h16v16H4z"/><path d="M8 8h8M8 12h8M8 16h5"/>'
    ];
    $inner = $icons[$name] ?? '';
    return '<svg class="patient-record-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" '
            .'stroke-width="2" stroke-linecap="round" stroke-linejoin="round">'
            .$inner.'</svg>';
}

Auth::requireRole('patient');

$patientModel   = new Patient();
$visitNoteModel = new VisitNote();
$prescModel     = new Prescription();
$pageTitle      = 'My Medical Records';

$patient = $patientModel->findByUserId(Auth::userId());

// Guard: the logged-in user must have a patient profile
if (!$patient) {
    header('Location: ../../error.php?code=403&msg=Patient+profile+not+found');
    exit;
}

$visitNotes = $visitNoteModel->findByPatient($patient['id']);

require_once '../../includes/header.php';
?>

<h1 class="patient-page-title"><?= patientRecordIcon('record') ?> My Medical Records</h1>
<p class="form-hint">View your previous consultation details, diagnosis, doctor's notes, and prescribed medications.</p>
<br>
<?php if (empty($visitNotes)): ?>
    <p class="patient-empty">No visit records found.</p>
<?php else: ?>
    <?php foreach ($visitNotes as $note): ?>
        <div class="patient-record-card">

            <div class="patient-record-header">
                <h3 class="patient-record-title"><?= patientRecordIcon('details') ?>
                    Consultation Details
                </h3>
            </div>

            <div class="patient-info-grid">
                <p><strong>Date</strong><br>
                    <?= htmlspecialchars($note['slot_date'] ?? '') ?>
                </p>

                <p><strong>Doctor</strong><br>
                    Dr. <?= htmlspecialchars($note['doctor_name'] ?? '') ?>
                </p>
            </div>

            <div class="patient-section">
                <h4>Diagnosis</h4>
                <div class="patient-detail-box"><?= htmlspecialchars($note['diagnosis']) ?></div>
            </div>

            <div class="patient-section">
                <h4>Doctor's Notes</h4>
                <div class="patient-detail-box"><?= htmlspecialchars($note['notes'] ?? 'No notes provided.') ?></div>
            </div>

        <?php $prescriptions = $prescModel->findByVisitNote($note['id']); ?>
        <?php if (!empty($prescriptions)): ?>

            <div class="patient-section">
                <h4>Prescription</h4>

                <div class="patient-prescription">
                    <?php foreach ($prescriptions as $rx): ?>
                        <div class="patient-medicine">
                            <strong><?= htmlspecialchars($rx['medicine_name']) ?></strong>
                            <br>
                            <span>Dosage: <?= htmlspecialchars($rx['dosage']) ?></span>
                            <br>
                            <span>Instructions: <?= htmlspecialchars($rx['instructions'] ?? 'Follow doctor instructions.') ?></span>
                        </div>
                    <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php require_once '../../includes/footer.php'; ?>