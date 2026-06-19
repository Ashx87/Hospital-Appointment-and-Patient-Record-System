<?php
/**
 * pages/receptionist/register-patient.php — Walk-in patient registration page
 *
 * Responsibilities:
 *   - Receptionist creates an account and fills in the basic profile for a new walk-in patient
 *   - Simultaneously inserts records into both users + patients within a PDO transaction (atomicity guaranteed)
 *   - On success, redirects to book-for-patient.php to book a time slot for the patient
 *
 * Flow (PRG):
 *   GET  → render the patient registration form
 *   POST → validateUser() → transaction: User::create() + Patient::create() → setFlash() → redirect
 *
 * Fields read/written:
 *   users    — role='patient', email(VARCHAR:UNIQUE), password_hash, full_name, phone, status='active', created_at(DATETIME)
 *   patients — user_id(FK→users,UNIQUE), date_of_birth(DATE:YYYY-MM-DD), gender(male|female|other), blood_type(VARCHAR), allergies(TEXT), address(TEXT)
 */

session_start();
require_once '../../config/Database.php';
require_once '../../classes/Auth.php';
require_once '../../classes/User.php';
require_once '../../classes/Patient.php';
require_once '../../includes/flash.php';
require_once '../../includes/validation.php';

Auth::requireRole('receptionist');

$userModel    = new User();
$patientModel = new Patient();
$pageTitle    = 'Register Walk-in Patient';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = validateUser($_POST);
    if (empty($errors)) {
        // TODO: PDO transaction → User::create() → Patient::create(userId, $_POST)
        setFlash('success', 'Patient registered. You can now book an appointment.');
        header('Location: book-for-patient.php?patient_id=NEW_ID');
        exit;
    } else {
        setFlash('error', implode(' ', $errors));
        header('Location: register-patient.php');
        exit;
    }
}

require_once '../../includes/header.php';
?>
<h1>Register Walk-in Patient</h1>
<form method="POST">
    <!-- TODO: patient basic info form fields (full_name, email, temp password, DOB, gender, blood_type, allergies, address) -->
    <button type="submit" class="btn">Register Patient</button>
</form>
<?php require_once '../../includes/footer.php'; ?>
