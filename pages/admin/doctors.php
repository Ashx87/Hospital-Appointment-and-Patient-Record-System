<?php
/**
 * pages/admin/doctors.php — Department and doctor management page (Admin only)
 *
 * Responsibilities:
 *   - Display all doctors (including department, specialization, and account status)
 *   - Support creating new doctor accounts (simultaneously creates users + doctors records; PDO transaction guarantees atomicity)
 *   - Support editing a doctor's department/specialization/bio
 *   - Support deactivating a doctor account (not deleted; historical appointment records remain intact)
 *
 * Flow (PRG pattern):
 *   GET  → render the doctor list
 *   POST → validate → call Doctor::create() / update() → setFlash() → redirect
 *
 * Fields read/written:
 *   doctors — id, user_id(FK→users), specialization(VARCHAR), department(VARCHAR), bio(TEXT)
 *   users   — id, role='doctor', email, password_hash, full_name, phone, status
 */

session_start();
require_once '../../config/Database.php';
require_once '../../classes/Auth.php';
require_once '../../classes/Doctor.php';
require_once '../../classes/User.php';
require_once '../../includes/flash.php';
require_once '../../includes/validation.php';

Auth::requireRole('admin');

$doctorModel = new Doctor();
$pageTitle   = 'Manage Doctors';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // TODO: dispatch based on $_POST['action']: create | update | toggle_status
}

$doctors     = $doctorModel->findAll();
$departments = $doctorModel->getDepartments();

require_once '../../includes/header.php';
?>

<h1>Manage Doctors</h1>

<!-- Create new doctor form (TODO) -->

<table class="data-table">
    <thead>
        <tr>
            <th>Name</th><th>Department</th><th>Specialization</th><th>Status</th><th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($doctors as $doc): ?>
        <tr>
            <td><?= htmlspecialchars($doc['full_name']) ?></td>
            <td><?= htmlspecialchars($doc['department']) ?></td>
            <td><?= htmlspecialchars($doc['specialization']) ?></td>
            <td><!-- TODO: status badge --></td>
            <td><!-- TODO: edit/deactivate buttons --></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once '../../includes/footer.php'; ?>
