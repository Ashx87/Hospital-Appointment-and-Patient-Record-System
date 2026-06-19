<?php
/**
 * pages/admin/reports.php — System reports page (Admin only)
 *
 * Responsibilities:
 *   - Display operational statistics reports needed by management (read-only, no write operations)
 *   - Report contents (as required by the design spec):
 *     1. Today's total appointments (broken down by booked/completed/cancelled status)
 *     2. Department appointment volume ranking (busiest departments)
 *     3. Appointment count statistics per doctor
 *     4. Total patient count in the system
 *
 * Data sources (aggregate read-only queries):
 *   appointments — id, status(booked|completed|cancelled), created_at
 *   slots        — slot_date(DATE), doctor_id
 *   doctors      — id, department(VARCHAR)
 *   patients     — id
 *
 * Supports GET parameters ?from=YYYY-MM-DD&to=YYYY-MM-DD to filter by date range
 */

session_start();
require_once '../../config/Database.php';
require_once '../../classes/Auth.php';
require_once '../../includes/flash.php';

Auth::requireRole('admin');

$pdo       = Database::getInstance();
$pageTitle = 'Reports';

$from = $_GET['from'] ?? date('Y-m-01');  // defaults to the first day of the current month
$to   = $_GET['to']   ?? date('Y-m-d');   // defaults to today

// TODO: run the various statistics queries
// $todayStats   = ... today's appointments grouped by status
// $deptRanking  = ... appointment volume by department GROUP BY department
// $doctorStats  = ... appointment count per doctor
// $patientCount = ... SELECT COUNT(*) FROM patients

require_once '../../includes/header.php';
?>

<h1>Reports</h1>

<!-- Date range filter (TODO) -->

<section class="report-section">
    <h2>Today's Appointments</h2>
    <!-- TODO: render today's appointment statistics cards -->
</section>

<section class="report-section">
    <h2>Appointments by Department</h2>
    <!-- TODO: render department ranking table -->
</section>

<section class="report-section">
    <h2>Appointments by Doctor</h2>
    <!-- TODO: render per-doctor appointment count table -->
</section>

<?php require_once '../../includes/footer.php'; ?>
