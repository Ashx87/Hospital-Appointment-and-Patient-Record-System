<?php
/**
 * pages/doctor/profile.php
 *
 * Doctor profile view and edit page
 *
 * Responsibilities:
 * - Allows doctor to update:
 *   Specialization
 *   Department
 *   Bio
 */

session_start();

require_once '../../classes/Database.php';
require_once '../../classes/Auth.php';
require_once '../../classes/Doctor.php';
require_once '../../includes/flash.php';
require_once '../../includes/csrf.php';

function doctorIcon(string $name): string
{
    $icons = [
        'profile' => '<path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8"/><path d="M4 20c0-4 4-6 8-6s8 2 8 6"/>',
        'save' => '<path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><path d="M17 21v-8H7v8"/><path d="M7 3v5h8"/>'
    ];
    $inner = $icons[$name] ?? '';
    return '<svg class="doctor-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" '
            .'stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
            .$inner.'</svg>';
}

Auth::requireRole('doctor');

$doctorModel = new Doctor();
$pageTitle = 'My Profile';

$doctor = $doctorModel->findByUserId(Auth::userId());

if (!$doctor) {
    header('Location: ../../error.php?code=403&msg=Doctor+profile+not+found');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'specialization' => $_POST['specialization'] ?? null,
        'department' => $_POST['department'] ?? null,
        'bio' => $_POST['bio'] ?? null
    ];

    $doctorModel->update($doctor['id'], $data);

    setFlash('success', 'Profile updated successfully.');
    header('Location: profile.php');
    exit;
}
require_once '../../includes/header.php';
?>

<h1 class="doctor-page-title"><?= doctorIcon('profile') ?>My Profile</h1>
<p class="form-hint">Update your personal information. Ensure your details are accurate.</p>
<div class="doctor-profile">
    <div class="info-card">
        <div class="doctor-form">
            <form method="POST">
                <?= csrfField() ?>
                <div class="form-group">
                    <label>Name</label>
                    <input 
                        type="text"
                        value="<?= htmlspecialchars($doctor['full_name']) ?>"
                        disabled>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input
                        type="text"
                        value="<?= htmlspecialchars($doctor['email']) ?>"
                        disabled>
                </div>

                <div class="form-group">
                    <label for="specialization">Specialization</label>
                    <input
                        type="text"
                        id="specialization"
                        name="specialization"
                        value="<?= htmlspecialchars($doctor['specialization'] ?? '') ?>"
                        required>
                </div>

                <div class="form-group">
                    <label for="department">Department</label>
                    <input
                        type="text"
                        id="department"
                        name="department"
                        value="<?= htmlspecialchars($doctor['department'] ?? '') ?>"
                        required>
                </div>

                <div class="form-group">
                    <label for="bio">Bio</label>
                    <textarea
                        id="bio"
                        name="bio"
                        rows="5"><?= htmlspecialchars($doctor['bio'] ?? '') ?>
                    </textarea>
                </div>

                <button
                    type="submit"
                    class="btn"
                    onclick="return confirm('Are you sure you want to save these changes?');">
                    <?= doctorIcon('save') ?>
                    Save Changes
                </button>
            </form>
        </div>
    </div>
</div>
<?php require_once '../../includes/footer.php'; ?>