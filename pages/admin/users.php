<?php
/**
 * pages/admin/users.php — User management page (Admin only)
 *
 * Responsibilities:
 *   - Display the full user list (filterable by role: admin/doctor/patient/receptionist)
 *   - Support creating new users (Admin manually adds accounts)
 *   - Support activating/deactivating accounts (toggleStatus), preserving data integrity without physical deletion
 *
 * Flow (PRG pattern):
 *   GET  → render the user list table
 *   POST → validate → call User methods → setFlash() → redirect back to GET
 *
 * Fields read/written (users table):
 *   id, role, email, password_hash, full_name, phone,
 *   status(active|inactive), created_at
 */

session_start();
require_once '../../config/Database.php';
require_once '../../classes/Auth.php';
require_once '../../classes/User.php';
require_once '../../includes/flash.php';
require_once '../../includes/validation.php';

Auth::requireRole('admin');

$userModel = new User();
$pageTitle  = 'Manage Users';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // TODO: dispatch based on $_POST['action']: create | toggle_status
    // validate → call User methods → setFlash() → header(Location) → exit
}

$filterRole = $_GET['role'] ?? null;
$users = $userModel->findAll($filterRole);

require_once '../../includes/header.php';
?>

<h1>Manage Users</h1>

<!-- Role filter & create user form (TODO) -->

<table class="data-table">
    <thead>
        <tr>
            <th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user): ?>
        <tr>
            <td><?= htmlspecialchars($user['full_name']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td><?= htmlspecialchars($user['role']) ?></td>
            <td><?= htmlspecialchars($user['status']) ?></td>
            <td><!-- TODO: activate/deactivate button --></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once '../../includes/footer.php'; ?>
