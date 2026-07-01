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

<h1 class="doctor-page-title">
    <img src = "../../assets/images/profile.png" alt="" class="header-icon">My Profile</h1>
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
                    <img src = "../../assets/images/save.png" alt="" class="button-icon">
                    Save Changes
                </button>
            </form>
        </div>
    </div>
</div>
<?php require_once '../../includes/footer.php'; ?>