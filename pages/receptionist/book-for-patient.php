<?php
/**
 * pages/receptionist/book-for-patient.php — Receptionist book on behalf of patient page
 *
 * Responsibilities:
 *   - Receptionist selects a registered patient, then selects a doctor and time slot to complete the booking on their behalf
 *   - The booked_by field records the receptionist's user_id (distinguishing from patient self-booking)
 *   - Within a PDO transaction: INSERT appointments + UPDATE slot.status='booked'
 *
 * Flow (PRG):
 *   GET  → display patient search + doctor selection + time slot list
 *   POST → Appointment::book(slotId, patientId, receptionistUserId) → setFlash() → redirect
 *
 * Fields read/written:
 *   appointments — id, slot_id(FK→slots,UNIQUE), patient_id(FK→patients), booked_by(FK→users = receptionist.user_id), status='booked', created_at(DATETIME)
 *   slots        — status → changed to 'booked'
 */

session_start();
require_once '../../config/Database.php';
require_once '../../classes/Auth.php';
require_once '../../classes/Patient.php';
require_once '../../classes/Doctor.php';
require_once '../../classes/Slot.php';
require_once '../../classes/Appointment.php';
require_once '../../includes/flash.php';

Auth::requireRole('receptionist');

$patientModel     = new Patient();
$doctorModel      = new Doctor();
$slotModel        = new Slot();
$appointmentModel = new Appointment();
$pageTitle        = 'Book for Patient';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $slotId    = (int)$_POST['slot_id'];
    $patientId = (int)$_POST['patient_id'];
    // TODO: $appointmentModel->book($slotId, $patientId, Auth::userId());
    setFlash('success', 'Appointment booked on behalf of patient.');
    header('Location: manage-appointments.php');
    exit;
}

$doctors     = $doctorModel->findAll();
$patientId   = (int)($_GET['patient_id'] ?? 0);
$doctorId    = (int)($_GET['doctor_id']  ?? 0);
$filterDate  = $_GET['date'] ?? date('Y-m-d');
$openSlots   = $doctorId ? $slotModel->findOpenByDoctor($doctorId, $filterDate) : [];

require_once '../../includes/header.php';
?>
<h1>Book Appointment for Patient</h1>
<!-- Step 1: search for patient (patient_id) (TODO) -->
<!-- Step 2: select doctor (doctor_id) (TODO) -->
<!-- Step 3: select date and time slot (TODO) -->
<form method="POST">
    <input type="hidden" name="patient_id" value="<?= $patientId ?>">
    <!-- TODO: slot choice radio list -->
    <button type="submit" class="btn">Confirm Booking</button>
</form>
<?php require_once '../../includes/footer.php'; ?>
