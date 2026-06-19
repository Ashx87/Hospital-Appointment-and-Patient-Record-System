<?php
/**
 * pages/patient/find-doctor.php — Patient search for doctor page
 *
 * Responsibilities:
 *   - Patient searches/filters the doctor list by department or doctor name
 *   - Displays each doctor's name, department, specialization, and bio
 *   - Clicking the "Book" button redirects to book.php?doctor_id=X to select a specific time slot
 *
 * Data sources (read-only queries):
 *   doctors — id, user_id, specialization, department, bio
 *   users   — full_name, status(active)
 *
 * GET parameters:
 *   ?department=Cardiology  filter by department
 *   ?name=Smith             search by name
 */

session_start();
require_once '../../config/Database.php';
require_once '../../classes/Auth.php';
require_once '../../classes/Doctor.php';
require_once '../../includes/flash.php';

Auth::requireRole('patient');

$doctorModel = new Doctor();
$pageTitle   = 'Find a Doctor';

$filterDept = $_GET['department'] ?? null;
$filterName = $_GET['name']       ?? null;

$doctors     = $doctorModel->findAll($filterDept, $filterName);
$departments = $doctorModel->getDepartments();

require_once '../../includes/header.php';
?>
<h1>Find a Doctor</h1>
<!-- Search filter form (department dropdown + name search box) (TODO) -->
<div class="doctor-grid">
    <?php foreach ($doctors as $doc): ?>
    <div class="doctor-card">
        <h3><?= htmlspecialchars($doc['full_name']) ?></h3>
        <p><?= htmlspecialchars($doc['department']) ?> — <?= htmlspecialchars($doc['specialization']) ?></p>
        <p><?= htmlspecialchars($doc['bio'] ?? '') ?></p>
        <a href="book.php?doctor_id=<?= $doc['id'] ?>" class="btn">Book Appointment</a>
    </div>
    <?php endforeach; ?>
</div>
<?php require_once '../../includes/footer.php'; ?>
