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
require_once '../../classes/Database.php';
require_once '../../classes/Auth.php';
require_once '../../classes/Patient.php';
require_once '../../classes/Doctor.php';
require_once '../../classes/Slot.php';
require_once '../../classes/Appointment.php';
require_once '../../includes/flash.php';
require_once '../../includes/csrf.php';

Auth::requireRole('receptionist');

$patientModel     = new Patient();
$doctorModel      = new Doctor();
$slotModel        = new Slot();
$appointmentModel = new Appointment();
$pageTitle        = 'Book for Patient';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verifyCsrf()) {

        setFlash('error','Security token mismatch.');

        header('Location: book-for-patient.php');

        exit;
    }

    try{

        $appointmentModel->book(

            (int)$_POST['slot_id'],

            (int)$_POST['patient_id'],

            $_SESSION['user_id']

        );

        setFlash('success','Appointment booked successfully.');

        header('Location: manage-appointments.php');

        exit;

    }catch(Exception $e){

        setFlash('error',$e->getMessage());

    }

}

$doctors     = $doctorModel->findAll();
$patientId   = (int)($_GET['patient_id'] ?? 0);
$doctorId    = (int)($_GET['doctor_id']  ?? 0);
$filterDate  = $_GET['date'] ?? date('Y-m-d');
$openSlots   = $doctorId ? $slotModel->findOpenByDoctor($doctorId, $filterDate) : [];

require_once '../../includes/header.php';
?>
<h1>Book Appointment for Patient</h1>

<div class="info-card">

    <form method="GET">

        <input type="hidden" name="patient_id" value="<?= $patientId ?>">

        <div class="form-group">

            <label>Doctor</label>

            <select name="doctor_id" required>

                <option value="">Select Doctor</option>

                <?php foreach($doctors as $doctor): ?>

                    <option

                        value="<?= $doctor['id'] ?>"

                        <?= $doctorId==$doctor['id']?'selected':'' ?>>

                        <?= htmlspecialchars($doctor['full_name']) ?>

                    </option>

                <?php endforeach; ?>

            </select>

        </div>

        <div class="form-group">

            <label>Date</label>

            <input type="date" name="date" value="<?= htmlspecialchars($filterDate) ?>">

        </div>

        <button class="btn">Search Slots</button>

    </form>

</div>

<?php if($doctorId): ?>

<form method="POST">

    <?= csrfField() ?>

    <input type="hidden" name="patient_id" value="<?= $patientId ?>">

    <table class="data-table">

        <thead>

            <tr>

                <th></th>

                <th>Date</th>

                <th>Time</th>

            </tr>

        </thead>

        <tbody>

        <?php if(empty($openSlots)): ?>

        <tr>

            <td colspan="3">No available slots.</td>

        </tr>

        <?php else: ?>

        <?php foreach($openSlots as $slot): ?>

        <tr>

            <td>

            <input type="radio" name="slot_id" value="<?= $slot['id'] ?>" required>

            </td>

            <td>

            <?= htmlspecialchars($slot['slot_date']) ?>

            </td>

            <td>

            <?= htmlspecialchars(substr($slot['start_time'],0,5)) ?>-<?= htmlspecialchars(substr($slot['end_time'],0,5)) ?>

            </td>

        </tr>

        <?php endforeach; ?>

        <?php endif; ?>

        </tbody>

    </table>

    <br>

    <button class="btn">Confirm Booking</button>

</form>

<?php endif; ?>