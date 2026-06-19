<?php
/**
 * pages/admin/dashboard.php — Admin dashboard home page
 *
 * Responsibilities:
 *   - Verify the current user has the admin role (Auth::requireRole)
 *   - Display system overview statistics: today's total appointments, total registered patients, active doctor count, busiest department
 *   - Provide quick-access links to user management, department management, and the reports page
 *
 * Data sources (read-only queries, no write operations):
 *   - appointments table: today's appointment count (status='booked' AND slot_date=today)
 *   - patients table: total patient count
 *   - doctors table JOIN users: count of doctors with status='active'
 *   - appointments + slots + doctors: grouped by department to find the busiest department
 */

session_start();
require_once '../../config/Database.php';
require_once '../../classes/Auth.php';
require_once '../../includes/flash.php';

Auth::requireRole('admin');

$pageTitle = 'Admin Dashboard';

// TODO: query statistics data
// $pdo = Database::getInstance();
// $todayCount = ...
// $totalPatients = ...
// $totalDoctors = ...
// $busiestDept = ...

require_once '../../includes/header.php';
?>

<h1>Admin Dashboard</h1>

<div class="stats-grid">
    <!-- TODO: render statistics cards (today's appointments, total patients, total doctors, busiest department) -->
</div>

<div class="quick-links">
    <a href="users.php" class="btn">Manage Users</a>
    <a href="doctors.php" class="btn">Manage Doctors</a>
    <a href="reports.php" class="btn">View Reports</a>
</div>

<?php require_once '../../includes/footer.php'; ?>
