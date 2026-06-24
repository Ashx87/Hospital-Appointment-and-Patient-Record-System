<?php
/**
 * pages/admin/dashboard.php — Admin dashboard home page
 *
 * Responsibilities:
 *   - Verify the current user has the admin role (Auth::requireRole)
 *   - Display system overview statistics: today's total appointments, total registered
 *     patients, active doctor count, busiest department
 *   - Provide quick-access links to user management, doctor management, and reports
 *
 * Data sources (read-only queries, no write operations):
 *   - appointments JOIN slots: today's booked appointment count (slot_date = today)
 *   - patients table: total patient count
 *   - doctors JOIN users: count of doctors with status='active'
 *   - appointments + slots + doctors: grouped by department to find the busiest one
 *
 * Each query is wrapped in try/catch so a single failure degrades gracefully
 * (shows 0 / "—") instead of breaking the whole page.
 */

session_start();
require_once '../../classes/Database.php';
require_once '../../classes/Auth.php';
require_once '../../includes/flash.php';

Auth::requireRole('admin');

$pageTitle = 'Admin Dashboard';
$pdo       = Database::getInstance();

/** Run a scalar aggregate query, returning a safe fallback on error. */
function scalarStat(PDO $pdo, string $sql, $fallback = 0)
{
    try {
        $value = $pdo->query($sql)->fetchColumn();
        return $value !== false ? $value : $fallback;
    } catch (PDOException $e) {
        error_log('dashboard.php stat error: ' . $e->getMessage());
        return $fallback;
    }
}

$todayCount = scalarStat(
    $pdo,
    "SELECT COUNT(*) FROM appointments a
     JOIN slots s ON s.id = a.slot_id
     WHERE a.status = 'booked' AND s.slot_date = CURDATE()"
);

$totalPatients = scalarStat($pdo, 'SELECT COUNT(*) FROM patients');

$totalDoctors = scalarStat(
    $pdo,
    "SELECT COUNT(*) FROM doctors d
     JOIN users u ON u.id = d.user_id
     WHERE u.status = 'active'"
);

$busiestDept = scalarStat(
    $pdo,
    "SELECT d.department FROM appointments a
     JOIN slots s   ON s.id = a.slot_id
     JOIN doctors d ON d.id = s.doctor_id
     WHERE a.status <> 'cancelled' AND d.department IS NOT NULL AND d.department <> ''
     GROUP BY d.department
     ORDER BY COUNT(*) DESC
     LIMIT 1",
    null
);

require_once '../../includes/header.php';
?>

<h1>Admin Dashboard</h1>

<div class="stats-grid">
    <div class="info-card stat-card">
        <span class="stat-card__value"><?= (int) $todayCount ?></span>
        <span class="stat-card__label">Today's Appointments</span>
    </div>
    <div class="info-card stat-card">
        <span class="stat-card__value"><?= (int) $totalPatients ?></span>
        <span class="stat-card__label">Total Patients</span>
    </div>
    <div class="info-card stat-card">
        <span class="stat-card__value"><?= (int) $totalDoctors ?></span>
        <span class="stat-card__label">Active Doctors</span>
    </div>
    <div class="info-card stat-card">
        <span class="stat-card__value"><?= $busiestDept !== null ? htmlspecialchars($busiestDept) : '—' ?></span>
        <span class="stat-card__label">Busiest Department</span>
    </div>
</div>

<div class="quick-links">
    <a href="users.php" class="btn">Manage Users</a>
    <a href="doctors.php" class="btn">Manage Doctors</a>
    <a href="reports.php" class="btn">View Reports</a>
</div>

<?php require_once '../../includes/footer.php'; ?>
