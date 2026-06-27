<?php
/**
 * pages/receptionist/register-patient.php
 */

session_start();

require_once '../../classes/Database.php';
require_once '../../classes/Auth.php';
require_once '../../classes/User.php';
require_once '../../classes/Patient.php';
require_once '../../includes/flash.php';
require_once '../../includes/validation.php';
require_once '../../includes/csrf.php';

Auth::requireRole('receptionist');

$userModel = new User();
$patientModel = new Patient();

$pageTitle = 'Register Walk-in Patient';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verifyCsrf()) {
        setFlash('error', 'Security token mismatch. Please try again.');
        header('Location: register-patient.php');
        exit;
    }

    $errors = validateUser($_POST);

    if (isset($_POST['confirm_password']) &&
        $_POST['password'] !== $_POST['confirm_password']) {
        $errors[] = 'Passwords do not match.';
    }

    if (empty($errors)) {

        $pdo = Database::getInstance();

        try {

            $pdo->beginTransaction();

            $userData = $_POST;
            $userData['role'] = 'patient';

            $userId = $userModel->create($userData);

            $patientId = $patientModel->create($userId, $_POST);

            $pdo->commit();

            setFlash('success', 'Patient registered successfully.');

            header('Location: book-for-patient.php?patient_id=' . $patientId);
            exit;

        } catch (Exception $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            error_log($e->getMessage());

            setFlash('error', 'Registration failed. Please try again.');
        }

    } else {

        setFlash('error', implode('<br>', $errors));

    }
}

require_once '../../includes/header.php';
?>

<h1>Register Walk-in Patient</h1>

<p class="form-hint">Register a new walk-in patient before booking an appointment.</p>

<div class="info-card">

    <form method="POST">

        <?= csrfField() ?>

        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="full_name" required value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>Phone Number</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>

        <div class="form-group">
            <label>Confirm Password</label>
            <input type="password" name="confirm_password" required>
        </div>

        <div class="form-group">
            <label>Date of Birth</label>
            <input type="date" name="date_of_birth" required value="<?= htmlspecialchars($_POST['date_of_birth'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>Gender</label>
            <select name="gender" required>
                <option value="">Select</option>
                <option value="male"
                <?= (($_POST['gender'] ?? '') == 'male') ? 'selected' : '' ?>>
                Male
                </option>
                <option value="female"
                <?= (($_POST['gender'] ?? '') == 'female') ? 'selected' : '' ?>>
                Female
                </option>
                <option value="other"
                <?= (($_POST['gender'] ?? '') == 'other') ? 'selected' : '' ?>>
                Other
                </option>
            </select>
        </div>

        <div class="form-group">

            <label>Blood Type</label>
            <select name="blood_type">
                <?php
                $bloodTypes = [
                    'A+','A-',
                    'B+','B-',
                    'AB+','AB-',
                    'O+','O-'
                ];

                foreach($bloodTypes as $type):
                ?>

                <option
                value="<?= $type ?>"
                <?= (($_POST['blood_type'] ?? '') == $type) ? 'selected' : '' ?>>
                <?= $type ?>
                </option>

                <?php endforeach; ?>
            </select>

        </div>

        <div class="form-group">
            <label>Allergies</label>
            <textarea name="allergies" rows="3"><?= htmlspecialchars($_POST['allergies'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label>Address</label>
            <textarea name="address" rows="4"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="btn">Register Walk-in Patient</button>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>