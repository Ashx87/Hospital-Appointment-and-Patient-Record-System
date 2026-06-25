<?php
/**
 * pages/patient/book.php — Patient book time slot page
 *
 * Responsibilities:
 *   - Accepts GET parameter ?doctor_id=X, displays open time slots for that doctor on a given date
 *   - Patient selects a time slot and submits a booking
 *   - On submission, within a PDO transaction: INSERT appointments + UPDATE slot.status='booked'
 *
 * Flow (PRG):
 *   GET  → display doctor info + date picker + list of open time slots
 *   POST → Appointment::book(slotId, patientId, bookedBy) → setFlash() → redirect
 *
 * Fields read/written:
 *   slots        — id, slot_date(DATE:YYYY-MM-DD), start_time(TIME:HH:MM), end_time(TIME:HH:MM), status → changed to 'booked'
 *   appointments — id, slot_id(FK→slots,UNIQUE), patient_id(FK→patients), booked_by(FK→users), status='booked', created_at(DATETIME)
 */

session_start();
require_once '../../classes/Database.php';
require_once '../../classes/Auth.php';
require_once '../../classes/Patient.php';
require_once '../../classes/Doctor.php';
require_once '../../classes/Slot.php';
require_once '../../classes/Appointment.php';
require_once '../../includes/flash.php';
require_once '../../includes/csrf.php';

Auth::requireRole('patient');

$patientModel     = new Patient();
$doctorModel      = new Doctor();
$slotModel        = new Slot();
$appointmentModel = new Appointment();
$pageTitle        = 'Book Appointment';

$doctorId = (int)($_GET['doctor_id'] ?? $_POST['doctor_id'] ?? 0);
$doctor   = $doctorModel->findById($doctorId);

if (!$doctor) {
    header('Location: find-doctor.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf()) {
        setFlash('error', 'Security token mismatch. Please try again.');
        header('Location: find-doctor.php');
        exit;
    }
    $slotId  = (int)$_POST['slot_id'];
    $patient = $patientModel->findByUserId(Auth::userId());
    if (!$patient) {
        header('Location: ../../error.php?code=403&msg=Patient+profile+not+found');
        exit;
    }
    // TODO: $appointmentModel->book($slotId, $patient['id'], Auth::userId());
    setFlash('success', 'Appointment booked successfully!');
    header('Location: my-appointments.php');
    exit;
}

$filterDate = $_GET['date'] ?? date('Y-m-d');
$openSlots  = $slotModel->findOpenByDoctor($doctorId, $filterDate);

require_once '../../includes/header.php';
?>
<h1>Book Appointment with Dr. <?= htmlspecialchars($doctor['full_name']) ?></h1>
<p><?= htmlspecialchars($doctor['department']) ?> — <?= htmlspecialchars($doctor['specialization']) ?></p>
<!-- Date picker (TODO) -->
<form method="POST">
    <?= csrfField() ?>
    <input type="hidden" name="doctor_id" value="<?= $doctorId ?>">
    <!-- TODO: render list of open time slots, radio buttons to select slot_id -->
    <button type="submit" class="btn">Confirm Booking</button>
</form>
<?php require_once '../../includes/footer.php'; ?>
