<?php
/**
 * pages/receptionist/dashboard.php
 *
 * Receptionist Dashboard
 */

session_start();

require_once '../../classes/Database.php';
require_once '../../classes/Auth.php';
require_once '../../includes/flash.php';

Auth::requireRole('receptionist');

$pageTitle = 'Receptionist Dashboard';

$pdo = Database::getInstance();

/**
 * Helper
 */
function scalarStat(PDO $pdo, string $sql, $fallback = 0)
{
    try {
        $value = $pdo->query($sql)->fetchColumn();
        return $value !== false ? $value : $fallback;
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return $fallback;
    }
}

/*
|--------------------------------------------------------------------------
| Dashboard Statistics
|--------------------------------------------------------------------------
*/

$todayAppointments = scalarStat(
    $pdo,
    "SELECT COUNT(*)
     FROM appointments a
     JOIN slots s ON s.id = a.slot_id
     WHERE s.slot_date = CURDATE()
       AND a.status <> 'cancelled'"
);

$pendingAppointments = scalarStat(
    $pdo,
    "SELECT COUNT(*)
     FROM appointments
     WHERE status='booked'"
);

$totalPatients = scalarStat(
    $pdo,
    "SELECT COUNT(*) FROM patients"
);

/*
|--------------------------------------------------------------------------
| Today's Appointment List
|--------------------------------------------------------------------------
*/

try{

    $stmt = $pdo->prepare("
        SELECT

            puser.full_name AS patient_name,

            duser.full_name AS doctor_name,

            s.slot_date,

            s.start_time,

            a.status

        FROM appointments a

        JOIN slots s
            ON s.id = a.slot_id

        JOIN patients p
            ON p.id = a.patient_id

        JOIN users puser
            ON puser.id = p.user_id

        JOIN doctors d
            ON d.id = s.doctor_id

        JOIN users duser
            ON duser.id = d.user_id

        WHERE s.slot_date = CURDATE()

        ORDER BY s.start_time ASC

        LIMIT 10
    ");

    $stmt->execute();

    $todayList = $stmt->fetchAll();

}catch(PDOException $e){

    error_log($e->getMessage());

    $todayList = [];

}

require_once '../../includes/header.php';
?>

<h1>Receptionist Dashboard</h1>

<p>
Welcome,
<strong><?= htmlspecialchars($_SESSION['name']) ?></strong>
</p>

<div class="stats-grid">

    <div class="info-card stat-card">
        <div class="stat-card__value">
            <?= $todayAppointments ?>
        </div>

        <div class="stat-card__label">
            Today's Appointments
        </div>
    </div>

    <div class="info-card stat-card">
        <div class="stat-card__value">
            <?= $pendingAppointments ?>
        </div>

        <div class="stat-card__label">
            Pending Appointments
        </div>
    </div>

    <div class="info-card stat-card">
        <div class="stat-card__value">
            <?= $totalPatients ?>
        </div>

        <div class="stat-card__label">
            Total Patients
        </div>
    </div>

</div>

<h2>Quick Actions</h2>

<div class="quick-links">

    <a href="register-patient.php" class="btn">
        Register Walk-in Patient
    </a>

    <a href="book-for-patient.php" class="btn">
        Book Appointment
    </a>

    <a href="manage-appointments.php" class="btn">
        Manage Appointments
    </a>

</div>

<h2>Today's Appointments</h2>

<table class="data-table">

    <thead>

    <tr>

        <th>Time</th>

        <th>Patient</th>

        <th>Doctor</th>

        <th>Status</th>

    </tr>

    </thead>

    <tbody>

<?php if(empty($todayList)): ?>

<tr>

<td colspan="4">
No appointments for today.
</td>

</tr>

<?php else: ?>

<?php foreach($todayList as $row): ?>

<tr>

<td><?= htmlspecialchars(substr($row['start_time'],0,5)) ?></td>

<td><?= htmlspecialchars($row['patient_name']) ?></td>

<td><?= htmlspecialchars($row['doctor_name']) ?></td>

<td><?= htmlspecialchars(ucfirst($row['status'])) ?></td>

</tr>

<?php endforeach; ?>

<?php endif; ?>

    </tbody>

</table>

<?php require_once '../../includes/footer.php'; ?>