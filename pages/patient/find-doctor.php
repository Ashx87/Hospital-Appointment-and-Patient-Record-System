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
require_once '../../classes/Database.php';
require_once '../../classes/Auth.php';
require_once '../../classes/Doctor.php';
require_once '../../includes/flash.php';

Auth::requireRole('patient');

$doctorModel = new Doctor();
$pageTitle   = 'Find a Doctor';

$filterDept = $_GET['department'] ?? null;
$filterSpec = $_GET['specialization'] ?? null;
$filterName = $_GET['name']       ?? null;

$doctors     = $doctorModel->findAll($filterDept, $filterSpec, $filterName);
$departments = $doctorModel->getDepartments();
$specializations = $doctorModel->getSpecializations();

require_once '../../includes/header.php';
?>
<h1>Find a Doctor</h1>
<form method="GET" class="filter-form">
    <input
        type="text"
        id="doctorSearch"
        name="name"
        placeholder="Search doctor..."
        value="<?= htmlspecialchars($filterName ?? '') ?>">

    <select name="department">
        <option value="">All Departments</option>
        <?php foreach($departments as $dept): ?>
            <option
                value="<?= htmlspecialchars($dept) ?>"
                <?= $filterDept==$dept?'selected':'' ?>>
                <?= htmlspecialchars($dept) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <select name="specialization">
        <option value="">All Specializations</option>
        <?php foreach($specializations as $spec): ?>
            <option
                value="<?= htmlspecialchars($spec) ?>"
                <?= $filterSpec==$spec?'selected':'' ?>>
                <?= htmlspecialchars($spec) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn">Filter</button>
</form>

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

<script>
    document.getElementById("doctorSearch").addEventListener("keyup", function(){
        let keyword = this.value.toLowerCase();
        document.querySelectorAll(".doctor-card").forEach(function(card){
            let text = card.innerText.toLowerCase();
            if(text.includes(keyword))
                card.style.display="";
            else
                card.style.display="none";
        });
    });
</script>
<?php require_once '../../includes/footer.php'; ?>
