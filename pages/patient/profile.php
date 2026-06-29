<?php
/**
 * pages/patient/profile.php
 *
 * Patient profile view and edit page
 *
 * Responsibilities:
 * - Display patient's personal information
 * - Allow patient to update:
 *   DOB
 *   Gender
 *   Blood Type
 *   Allergies
 *   Address
 */

session_start();

require_once '../../classes/Database.php';
require_once '../../classes/Auth.php';
require_once '../../classes/Patient.php';
require_once '../../includes/flash.php';
require_once '../../includes/csrf.php';

function patientIcon(string $name): string
{
    $icons = [
        'profile' => '<path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8"/><path d="M4 20c0-4 4-6 8-6s8 2 8 6"/>',
        'save' => '<path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><path d="M17 21v-8H7v8"/><path d="M7 3v5h8"/>'
    ];
    $inner = $icons[$name] ?? '';
    return '<svg class="patient-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" '
            .'stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
            .$inner.'</svg>';
}

Auth::requireRole('patient');

$patientModel = new Patient();
$pageTitle = 'My Profile';

$patient = $patientModel->findByUserId(Auth::userId());

if (!$patient) {
    header('Location: ../../error.php?code=403&msg=Patient+profile+not+found');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'date_of_birth' => $_POST['date_of_birth'] ?? null,
        'gender' => $_POST['gender'] ?? null,
        'blood_type' => $_POST['blood_type'] ?? null,
        'allergies' => $_POST['allergies'] ?? null,
        'address' => $_POST['address'] ?? null
    ];

    $patientModel->update($patient['id'], $data);

    setFlash('success', 'Profile updated successfully.');
    header('Location: profile.php');
    exit;
}
require_once '../../includes/header.php';
?>

<h1 class="patient-page-title"><?= patientIcon('profile') ?>My Profile</h1>
<p class="form-hint">Update your personal information. Ensure your details are accurate for future appointments.</p>
<div class="patient-profile">
    <div class="info-card">
        <div class="patient-form">
            <form method="POST">
                <?= csrfField() ?>
                <div class="form-group">
                    <label>Name</label>
                    <input 
                        type="text"
                        value="<?= htmlspecialchars($patient['full_name']) ?>"
                        disabled>
                </div>

                <div class="form-group">
                    <label for="date_of_birth">Date of Birth</label>
                    <input
                        type="date"
                        id="date_of_birth"
                        name="date_of_birth"
                        max="<?= date('Y-m-d') ?>"
                        value="<?= htmlspecialchars($patient['date_of_birth'] ?? '') ?>"
                        required>
                </div>

                <div class="form-group">
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender" required>
                        <option value="">Select Gender</option>
                        <option value="male" <?= ($patient['gender'] ?? '')=="male"?'selected':'' ?>>Male</option>
                        <option value="female" <?= ($patient['gender'] ?? '')=="female"?'selected':'' ?>>Female</option>
                        <option value="other" <?= ($patient['gender'] ?? '')=="other"?'selected':'' ?>>Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="blood_type">Blood Type</label>
                    <select id="blood_type" name="blood_type">
                        <option value="">Select Blood Type</option>
                        <option value="A+" <?= ($patient['blood_type'] ?? '')=="A+"?'selected':'' ?>>A+</option>
                        <option value="A-" <?= ($patient['blood_type'] ?? '')=="A-"?'selected':'' ?>>A-</option>
                        <option value="B+" <?= ($patient['blood_type'] ?? '')=="B+"?'selected':'' ?>>B+</option>
                        <option value="B-" <?= ($patient['blood_type'] ?? '')=="B-"?'selected':'' ?>>B-</option>
                        <option value="AB+" <?= ($patient['blood_type'] ?? '')=="AB+"?'selected':'' ?>>AB+</option>
                        <option value="AB-" <?= ($patient['blood_type'] ?? '')=="AB-"?'selected':'' ?>>AB-</option>
                        <option value="O+" <?= ($patient['blood_type'] ?? '')=="O+"?'selected':'' ?>>O+</option>
                        <option value="O-" <?= ($patient['blood_type'] ?? '')=="O-"?'selected':'' ?>>O-</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="allergies">Allergies</label>
                    <textarea id="allergies" name="allergies"><?= htmlspecialchars($patient['allergies'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address"><?= htmlspecialchars($patient['address'] ?? '') ?></textarea>
                </div>

                <button 
                    type="submit" 
                    class="btn"
                    onclick="return confirm('Are you sure you want to save these changes?');">
                    <?= patientIcon('save') ?>
                    Save Changes
                </button>
            </form>
        </div>
    </div>
</div>
<?php require_once '../../includes/footer.php'; ?>