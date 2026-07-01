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
    try {
        $appointmentModel->book($slotId, $patient['id'], Auth::userId());
        setFlash('success', 'Appointment booked successfully!');
    } catch(Exception $e){
        setFlash('error',$e->getMessage());
    }
    header('Location: my-appointments.php');
    exit;
}

$filterDate = $_GET['date'] ?? date('Y-m-d');
$openSlots  = $slotModel->findOpenByDoctor($doctorId, $filterDate);

require_once '../../includes/header.php';
?>
<h1 class="patient-page-title">
    <img src = "../../assets/images/calendar.png" alt="" class="header-icon">
    Book Appointment with Dr. <?= htmlspecialchars(str_replace('Dr. ', '', $doctor['full_name'])) ?>
</h1>
<p class="form-hint">Find your preferred appointment time.</p>

<div class="booking-doctor-card">
    <h3>Doctor Information</h3>
    <table class="booking-doctor-table">
        <tr>
            <td><strong>Name</strong></td>
            <td>Dr. <?= htmlspecialchars(str_replace('Dr. ', '', $doctor['full_name'])) ?></td>
        </tr>

        <tr>
            <td><strong>Department</strong></td>
            <td><?= htmlspecialchars($doctor['department']) ?></td>
        </tr>

        <tr>
            <td><strong>Specialization</strong></td>
            <td><?= htmlspecialchars($doctor['specialization']) ?></td>
        </tr>
    </table>
</div>

<div class="booking-section">
    <h2>Select Appointment Date</h2>
    <form method="GET" class="booking-date-form">
        <input type="date" name="date" value="<?= htmlspecialchars($filterDate) ?>" min="<?= date('Y-m-d')?>">
        <input type="hidden" name="doctor_id" value="<?= $doctorId ?>">
        <button type="submit" class="btn booking-check-btn">
            <img src = "../../assets/images/search2.png" alt="" class="button-icon">
            View Available Slots</button>
    </form>
</div>

<form method="POST">
    <?= csrfField() ?>
    <input type="hidden" name="doctor_id" value="<?= $doctorId ?>">
    <?php if(empty($openSlots)): ?>
        <div class="booking-empty">
            No appointment slots are available for the selected date.<br>
            Please choose another date.
        </div>
    <?php else: ?>
        <?php foreach($openSlots as $slot): ?>
            <div class="slot-card">
                <label>
                    <input type="radio" name="slot_id" value="<?= $slot['id'] ?>" required>
                    <span><?= htmlspecialchars($slot['start_time']) ?> - <?= htmlspecialchars($slot['end_time']) ?></span>
                </label>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <button type="submit" class="btn confirm-booking-btn">
        <img src = "../../assets/images/confirm.png" alt="" class="button-icon">
        Confirm Booking</button>
</form>

<?php require_once '../../includes/footer.php'; ?>