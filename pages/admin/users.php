<?php
/**
 * pages/admin/users.php — User management page (Admin only)
 *
 * Responsibilities:
 *   - Display the full user list (filterable by role: admin/doctor/patient/receptionist)
 *   - Support creating new users (Admin manually adds admin/receptionist accounts)
 *   - Support editing basic info (full_name, phone)
 *   - Support activating/deactivating accounts (toggleStatus), preserving data integrity
 *   - Support deleting an account (hard delete); gracefully refuses when the user is
 *     referenced by other records (foreign-key constraint) and suggests deactivating
 *
 * Design note — why create is limited to admin/receptionist:
 *   doctor and patient rows require a matching profile row (doctors/patients table).
 *   Doctors are created on doctors.php inside a transaction; patients are registered
 *   by the receptionist. Creating those roles here would leave orphan accounts with
 *   no profile, so this form only offers the two roles that have no extension table.
 *
 * Flow (PRG pattern):
 *   GET  → render the user list table + create/edit forms
 *   POST → validate → call User methods → setFlash() → redirect back to GET
 *
 * Fields read/written (users table):
 *   id, role, email, password_hash, full_name, phone,
 *   status(active|inactive), created_at
 */

session_start();
require_once '../../classes/Database.php';
require_once '../../classes/Auth.php';
require_once '../../classes/User.php';
require_once '../../includes/flash.php';
require_once '../../includes/validation.php';

Auth::requireRole('admin');

$userModel = new User();
$pageTitle = 'Manage Users';

// Roles that can be created from this page (no extension profile table required)
const CREATABLE_ROLES = ['admin', 'receptionist'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $errors = validateUser($_POST);

        // A password is mandatory when creating a brand-new account
        if (empty($_POST['password'])) {
            $errors[] = 'Password is required when creating a user.';
        }
        // Guard the role at the server boundary — never trust the submitted value
        $role = $_POST['role'] ?? '';
        if (!in_array($role, CREATABLE_ROLES, true)) {
            $errors[] = 'Role must be admin or receptionist.';
        }
        // Reject duplicate email up-front for a friendly message
        if (empty($errors) && $userModel->findByEmail(trim($_POST['email'])) !== null) {
            $errors[] = 'That email address is already registered.';
        }

        if (empty($errors)) {
            try {
                $userModel->create([
                    'role'      => $role,
                    'email'     => $_POST['email'],
                    'password'  => $_POST['password'],
                    'full_name' => $_POST['full_name'],
                    'phone'     => $_POST['phone'] ?? '',
                ]);
                setFlash('success', 'User created successfully.');
            } catch (PDOException $e) {
                error_log('users.php create error: ' . $e->getMessage());
                setFlash('error', 'Could not create the user. Please try again.');
            }
        } else {
            setFlash('error', implode(' ', $errors));
        }

    } elseif ($action === 'update') {
        $id     = (int) ($_POST['id'] ?? 0);
        $errors = [];
        if (empty(trim($_POST['full_name'] ?? ''))) {
            $errors[] = 'Full name is required.';
        }
        if (empty($errors) && $id > 0) {
            try {
                $userModel->update($id, [
                    'full_name' => $_POST['full_name'],
                    'phone'     => $_POST['phone'] ?? '',
                ]);
                setFlash('success', 'User updated.');
            } catch (PDOException $e) {
                error_log('users.php update error: ' . $e->getMessage());
                setFlash('error', 'Could not update the user. Please try again.');
            }
        } else {
            setFlash('error', $id > 0 ? implode(' ', $errors) : 'Invalid user.');
        }

    } elseif ($action === 'toggle_status') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $userModel->toggleStatus($id);
                setFlash('success', 'Account status updated.');
            } catch (PDOException $e) {
                error_log('users.php toggle error: ' . $e->getMessage());
                setFlash('error', 'Could not update account status.');
            }
        }

    } elseif ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id === Auth::userId()) {
            setFlash('error', 'You cannot delete your own account.');
        } elseif ($id > 0) {
            try {
                $userModel->delete($id);
                setFlash('success', 'User deleted.');
            } catch (PDOException $e) {
                // Foreign-key constraint (e.g. linked appointments) blocks the delete
                error_log('users.php delete error: ' . $e->getMessage());
                setFlash('error', 'This user has related records and cannot be deleted. Deactivate the account instead.');
            }
        }
    }

    header('Location: users.php');
    exit;
}

$filterRole = $_GET['role'] ?? null;
$users      = $userModel->findAll($filterRole);

require_once '../../includes/header.php';
?>

<h1>Manage Users</h1>

<section class="info-card">
    <h2>Add New User</h2>
    <p class="form-hint">Only <strong>admin</strong> and <strong>receptionist</strong> accounts are created here. Add doctors on the <a href="doctors.php">Manage Doctors</a> page; patients are registered by the receptionist.</p>
    <form method="POST" id="create-user-form" novalidate>
        <input type="hidden" name="action" value="create">
        <div class="form-group">
            <label for="role">Role</label>
            <select name="role" id="role" required>
                <option value="admin">Admin</option>
                <option value="receptionist">Receptionist</option>
            </select>
        </div>
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
        <button type="submit" class="btn">Create User</button>
    </form>
</section>

<section class="report-section">
    <form method="GET" class="filter-bar">
        <label for="filter-role">Filter by role:</label>
        <select name="role" id="filter-role" onchange="this.form.submit()">
            <option value="">All roles</option>
            <?php foreach (['admin', 'doctor', 'patient', 'receptionist'] as $r): ?>
                <option value="<?= $r ?>" <?= $filterRole === $r ? 'selected' : '' ?>><?= ucfirst($r) ?></option>
            <?php endforeach; ?>
        </select>
        <noscript><button type="submit" class="btn">Filter</button></noscript>
    </form>

    <table class="data-table">
        <thead>
            <tr>
                <th>Name</th><th>Email</th><th>Role</th><th>Phone</th><th>Status</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($users)): ?>
                <tr><td colspan="6">No users found.</td></tr>
            <?php else: ?>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['full_name']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['role']) ?></td>
                    <td><?= htmlspecialchars($user['phone'] ?? '—') ?></td>
                    <td>
                        <span class="badge badge--<?= $user['status'] === 'active' ? 'active' : 'inactive' ?>">
                            <?= htmlspecialchars($user['status']) ?>
                        </span>
                    </td>
                    <td class="actions">
                        <!-- Toggle status -->
                        <form method="POST" class="inline-form">
                            <input type="hidden" name="action" value="toggle_status">
                            <input type="hidden" name="id" value="<?= (int) $user['id'] ?>">
                            <button type="submit" class="btn btn--small">
                                <?= $user['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                            </button>
                        </form>
                        <!-- Edit (toggles an inline form via details/summary, no JS required) -->
                        <details class="inline-edit">
                            <summary class="btn btn--small">Edit</summary>
                            <form method="POST" class="edit-form">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="id" value="<?= (int) $user['id'] ?>">
                                <div class="form-group">
                                    <label>Full Name</label>
                                    <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required maxlength="100">
                                </div>
                                <div class="form-group">
                                    <label>Phone</label>
                                    <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" maxlength="20">
                                </div>
                                <button type="submit" class="btn btn--small">Save</button>
                            </form>
                        </details>
                        <!-- Delete (hard delete; JS confirm + server-side FK safety) -->
                        <?php if ((int) $user['id'] !== Auth::userId()): ?>
                        <form method="POST" class="inline-form" data-confirm="Delete this user permanently? This cannot be undone.">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int) $user['id'] ?>">
                            <button type="submit" class="btn btn--danger btn--small">Delete</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</section>

<?php require_once '../../includes/footer.php'; ?>
