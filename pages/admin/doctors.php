<?php
/**
 * pages/admin/doctors.php — Department and doctor management page (Admin only)
 *
 * Responsibilities:
 *   - Display all doctors (including department, specialization, and account status)
 *   - Create new doctor accounts (simultaneously creates users + doctors records;
 *     a PDO transaction guarantees atomicity — no orphan user without a profile)
 *   - Edit a doctor's department/specialization/bio
 *   - Deactivate/reactivate a doctor account (not deleted; historical records remain)
 *
 * Flow (PRG pattern):
 *   GET  → render the doctor list + create form
 *   POST → validate → Doctor::create()/update() / User::toggleStatus() → setFlash() → redirect
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
$userModel   = new User();
$pageTitle   = 'Manage Doctors';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $errors = validateUser($_POST);
        if (empty($_POST['password'])) {
            $errors[] = 'Password is required when creating a doctor.';
        }
        if (empty(trim($_POST['department'] ?? ''))) {
            $errors[] = 'Department is required.';
        }
        if (empty($errors) && $userModel->findByEmail(trim($_POST['email'])) !== null) {
            $errors[] = 'That email address is already registered.';
        }

        if (empty($errors)) {
            // Atomic create: users row + doctors row must both succeed or neither does
            $pdo = Database::getInstance();
            $pdo->beginTransaction();
            try {
                $userId = $userModel->create([
                    'role'      => 'doctor',
                    'email'     => $_POST['email'],
                    'password'  => $_POST['password'],
                    'full_name' => $_POST['full_name'],
                    'phone'     => $_POST['phone'] ?? '',
                ]);
                $doctorModel->create($userId, [
                    'specialization' => $_POST['specialization'] ?? '',
                    'department'     => $_POST['department'] ?? '',
                    'bio'            => $_POST['bio'] ?? '',
                ]);
                $pdo->commit();
                setFlash('success', 'Doctor created successfully.');
            } catch (Exception $e) {
                $pdo->rollBack();
                error_log('doctors.php create error: ' . $e->getMessage());
                setFlash('error', 'Could not create the doctor. Please try again.');
            }
        } else {
            setFlash('error', implode(' ', $errors));
        }

    } elseif ($action === 'update') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0 && !empty(trim($_POST['department'] ?? ''))) {
            try {
                $doctorModel->update($id, [
                    'specialization' => $_POST['specialization'] ?? '',
                    'department'     => $_POST['department'] ?? '',
                    'bio'            => $_POST['bio'] ?? '',
                ]);
                setFlash('success', 'Doctor profile updated.');
            } catch (PDOException $e) {
                error_log('doctors.php update error: ' . $e->getMessage());
                setFlash('error', 'Could not update the doctor.');
            }
        } else {
            setFlash('error', 'Department is required.');
        }

    } elseif ($action === 'toggle_status') {
        // Status lives on the users row, so toggle by user_id
        $userId = (int) ($_POST['user_id'] ?? 0);
        if ($userId > 0) {
            try {
                $userModel->toggleStatus($userId);
                setFlash('success', 'Doctor account status updated.');
            } catch (PDOException $e) {
                error_log('doctors.php toggle error: ' . $e->getMessage());
                setFlash('error', 'Could not update account status.');
            }
        }
    }

    header('Location: doctors.php');
    exit;
}

$doctors     = $doctorModel->findAllForAdmin();
$departments = $doctorModel->getDepartments();

require_once '../../includes/header.php';
?>

<h1>Manage Doctors</h1>

<section class="info-card">
    <h2>Add New Doctor</h2>
    <form method="POST" id="create-doctor-form" novalidate>
        <input type="hidden" name="action" value="create">
        <div class="form-group">
            <label for="full_name">Full Name</label>
            <input type="text" name="full_name" id="full_name" data-required maxlength="100">
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" data-required maxlength="255">
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" data-required data-min-length="8" autocomplete="new-password">
        </div>
        <div class="form-group">
            <label for="phone">Phone (optional)</label>
            <input type="text" name="phone" id="phone" maxlength="20">
        </div>
        <div class="form-group">
            <label for="department">Department</label>
            <input type="text" name="department" id="department" data-required list="department-list" maxlength="100">
            <datalist id="department-list">
                <?php foreach ($departments as $dept): ?>
                    <option value="<?= htmlspecialchars($dept) ?>"></option>
                <?php endforeach; ?>
            </datalist>
        </div>
        <div class="form-group">
            <label for="specialization">Specialization (optional)</label>
            <input type="text" name="specialization" id="specialization" maxlength="100">
        </div>
        <div class="form-group">
            <label for="bio">Bio (optional)</label>
            <textarea name="bio" id="bio" rows="3"></textarea>
        </div>
        <button type="submit" class="btn">Create Doctor</button>
    </form>
</section>

<section class="report-section">
    <table class="data-table">
        <thead>
            <tr>
                <th>Name</th><th>Department</th><th>Specialization</th><th>Status</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($doctors)): ?>
                <tr><td colspan="5">No doctors found.</td></tr>
            <?php else: ?>
                <?php foreach ($doctors as $doc): ?>
                <tr>
                    <td><?= htmlspecialchars($doc['full_name']) ?></td>
                    <td><?= htmlspecialchars($doc['department'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($doc['specialization'] ?? '—') ?></td>
                    <td>
                        <span class="badge badge--<?= $doc['status'] === 'active' ? 'active' : 'inactive' ?>">
                            <?= htmlspecialchars($doc['status']) ?>
                        </span>
                    </td>
                    <td class="actions">
                        <details class="inline-edit">
                            <summary class="btn btn--small">Edit</summary>
                            <form method="POST" class="edit-form">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="id" value="<?= (int) $doc['id'] ?>">
                                <div class="form-group">
                                    <label>Department</label>
                                    <input type="text" name="department" value="<?= htmlspecialchars($doc['department'] ?? '') ?>" required list="department-list" maxlength="100">
                                </div>
                                <div class="form-group">
                                    <label>Specialization</label>
                                    <input type="text" name="specialization" value="<?= htmlspecialchars($doc['specialization'] ?? '') ?>" maxlength="100">
                                </div>
                                <div class="form-group">
                                    <label>Bio</label>
                                    <textarea name="bio" rows="3"><?= htmlspecialchars($doc['bio'] ?? '') ?></textarea>
                                </div>
                                <button type="submit" class="btn btn--small">Save</button>
                            </form>
                        </details>
                        <form method="POST" class="inline-form">
                            <input type="hidden" name="action" value="toggle_status">
                            <input type="hidden" name="user_id" value="<?= (int) $doc['user_id'] ?>">
                            <button type="submit" class="btn btn--small">
                                <?= $doc['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</section>

<?php require_once '../../includes/footer.php'; ?>
