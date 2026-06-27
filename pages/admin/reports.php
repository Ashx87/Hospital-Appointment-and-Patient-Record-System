<?php
/**
 * pages/admin/reports.php — System reports page (Admin only)
 *
 * Responsibilities:
 *   - Display operational statistics reports needed by management (read-only)
 *   - Report contents (as required by the design spec):
 *     1. Today's total appointments (broken down by booked/completed/cancelled status)
 *     2. Department appointment volume ranking (busiest departments) — over the date range
 *     3. Appointment count per doctor — over the date range
 *     4. Total patient count in the system
 *
 * Data sources (aggregate read-only queries):
 *   appointments — id, status(booked|completed|cancelled)
 *   slots        — slot_date(DATE), doctor_id
 *   doctors      — id, department(VARCHAR)
 *   users        — full_name (doctor name)
 *   patients     — id
 *
 * GET parameters ?from=YYYY-MM-DD&to=YYYY-MM-DD filter the department and per-doctor
 * tables by slot_date. The "Today's Appointments" section always reflects today.
 * Invalid or reversed ranges fall back to the defaults with a flash notice.
 */

session_start();
require_once '../../classes/Database.php';
require_once '../../classes/Auth.php';
require_once '../../includes/flash.php';

Auth::requireRole('admin');

$pdo       = Database::getInstance();
$pageTitle = 'Reports';

/** Validate a YYYY-MM-DD string; return it if valid, else null. */
function validDate(?string $value): ?string
{
    if (empty($value)) {
        return null;
    }
    $d = DateTime::createFromFormat('Y-m-d', $value);
    return ($d && $d->format('Y-m-d') === $value) ? $value : null;
}

$defaultFrom = date('Y-m-01');  // first day of the current month
$defaultTo   = date('Y-m-d');   // today

$from = validDate($_GET['from'] ?? null);
$to   = validDate($_GET['to'] ?? null);

// Fall back to defaults on any invalid/missing/reversed range, and tell the user
if (($from === null || $to === null) && (isset($_GET['from']) || isset($_GET['to']))) {
    setFlash('error', 'Invalid date range. Showing the default range instead.');
}
$from = $from ?? $defaultFrom;
$to   = $to ?? $defaultTo;
if ($from > $to) {
    setFlash('error', 'Start date was after end date; the dates have been swapped.');
    [$from, $to] = [$to, $from];
}

// --- Run the aggregate queries (each degrades gracefully on error) ---
$todayStats   = [];  // status => count for today
$deptRanking  = [];  // [department, total]
$doctorStats  = [];  // [full_name, department, total]
$patientCount = 0;

try {
    $stmt = $pdo->query(
        "SELECT a.status, COUNT(*) AS total
         FROM appointments a
         JOIN slots s ON s.id = a.slot_id
         WHERE s.slot_date = CURDATE()
         GROUP BY a.status"
    );
    foreach ($stmt->fetchAll() as $row) {
        $todayStats[$row['status']] = (int) $row['total'];
    }

    $stmt = $pdo->prepare(
        "SELECT d.department, COUNT(*) AS total
         FROM appointments a
         JOIN slots s   ON s.id = a.slot_id
         JOIN doctors d ON d.id = s.doctor_id
         WHERE s.slot_date BETWEEN ? AND ?
           AND a.status <> 'cancelled'
           AND d.department IS NOT NULL AND d.department <> ''
         GROUP BY d.department
         ORDER BY total DESC"
    );
    $stmt->execute([$from, $to]);
    $deptRanking = $stmt->fetchAll();

    $stmt = $pdo->prepare(
        "SELECT u.full_name, d.department, COUNT(*) AS total
         FROM appointments a
         JOIN slots s   ON s.id = a.slot_id
         JOIN doctors d ON d.id = s.doctor_id
         JOIN users u   ON u.id = d.user_id
         WHERE s.slot_date BETWEEN ? AND ?
           AND a.status <> 'cancelled'
         GROUP BY d.id, u.full_name, d.department
         ORDER BY total DESC"
    );
    $stmt->execute([$from, $to]);
    $doctorStats = $stmt->fetchAll();

    $patientCount = (int) $pdo->query('SELECT COUNT(*) FROM patients')->fetchColumn();
} catch (PDOException $e) {
    error_log('reports.php query error: ' . $e->getMessage());
    setFlash('error', 'Some report data could not be loaded. Please try again.');
}

$statusLabels = ['booked' => 'Booked', 'completed' => 'Completed', 'cancelled' => 'Cancelled'];

// Largest department total — used to scale the CSS bar widths (read-only, derived).
$deptMax = !empty($deptRanking) ? max(array_map(fn($r) => (int) $r['total'], $deptRanking)) : 0;

// Colour cue per appointment status so the KPI cards read at a glance.
$statusCardClass = ['booked' => 'stat-card--teal', 'completed' => 'stat-card--green', 'cancelled' => 'stat-card--amber'];

$adminNav     = 'reports';
$pageSubtitle = 'Operational statistics and appointment trends';
require_once '../../includes/admin_header.php';
?>

<form method="GET" class="filter-bar report-filter">
    <div class="form-group">
        <label for="from">From</label>
        <input type="date" name="from" id="from" value="<?= htmlspecialchars($from) ?>">
    </div>
    <div class="form-group">
        <label for="to">To</label>
        <input type="date" name="to" id="to" value="<?= htmlspecialchars($to) ?>">
    </div>
    <button type="submit" class="btn"><?= adminIcon('reports') ?> Apply</button>
</form>

<section>
    <div class="admin-section-head"><h2>Today's appointments</h2></div>
    <div class="stats-grid">
        <?php foreach ($statusLabels as $key => $label): ?>
        <div class="info-card stat-card <?= $statusCardClass[$key] ?? '' ?>">
            <span class="stat-card__icon"><?= adminIcon('calendar') ?></span>
            <span class="stat-card__body">
                <span class="stat-card__value"><?= (int) ($todayStats[$key] ?? 0) ?></span>
                <span class="stat-card__label"><?= $label ?></span>
            </span>
        </div>
        <?php endforeach; ?>
        <div class="info-card stat-card">
            <span class="stat-card__icon"><?= adminIcon('patients') ?></span>
            <span class="stat-card__body">
                <span class="stat-card__value"><?= $patientCount ?></span>
                <span class="stat-card__label">Total Patients</span>
            </span>
        </div>
    </div>
</section>

<section class="admin-card">
    <div class="admin-section-head">
        <h2>Appointments by department</h2>
        <span class="muted"><?= htmlspecialchars($from) ?> → <?= htmlspecialchars($to) ?></span>
    </div>
    <?php if (empty($deptRanking)): ?>
        <p class="form-hint">No appointments in this range.</p>
    <?php else: ?>
        <div class="admin-barchart" role="img"
             aria-label="Appointment count per department for the selected range">
            <?php foreach ($deptRanking as $row): ?>
                <?php $pct = $deptMax > 0 ? round((int) $row['total'] / $deptMax * 100) : 0; ?>
                <div class="admin-bar">
                    <span class="admin-bar__label"><?= htmlspecialchars($row['department']) ?></span>
                    <span class="admin-bar__track">
                        <span class="admin-bar__fill" style="width: <?= $pct ?>%"></span>
                    </span>
                    <span class="admin-bar__value"><?= (int) $row['total'] ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<section>
    <div class="admin-section-head">
        <h2>Appointments by doctor</h2>
        <span class="muted"><?= htmlspecialchars($from) ?> → <?= htmlspecialchars($to) ?></span>
    </div>
    <div class="admin-table-wrap">
    <table class="data-table">
        <thead><tr><th>Doctor</th><th>Department</th><th class="admin-num">Appointments</th></tr></thead>
        <tbody>
            <?php if (empty($doctorStats)): ?>
                <tr class="admin-empty"><td colspan="3">No appointments in this range.</td></tr>
            <?php else: ?>
                <?php foreach ($doctorStats as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['full_name']) ?></td>
                    <td><?= htmlspecialchars($row['department'] ?? '—') ?></td>
                    <td class="admin-num"><?= (int) $row['total'] ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    </div><!-- .admin-table-wrap -->
</section>

<?php require_once '../../includes/admin_footer.php'; ?>
