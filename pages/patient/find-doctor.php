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

function patientIcon(string $name): string
{
    $icons = [
        'filter' => '<polygon points="22 3 2 3 10 12.5 10 19 14 21 14 12.5 22 3"/>',
        'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
        'doctor' => '<path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8"/><path d="M4 20c0-4 4-6 8-6s8 2 8 6"/>',
    ];
    $inner = $icons[$name] ?? '';
    return '<svg class="patient-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">'
            .$inner.'</svg>';
}

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
<h1 class="patient-page-title"><?= patientIcon('doctor') ?> Find a Doctor</h1>
<p class="form-hint">Find your preferred doctor and book an appointment.</p>

<div class="doctor-filter-box">
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

        <button type="submit" class="btn doctor-filter-btn"><?= patientIcon('filter') ?>Filter</button>
    </form>
</div>

<h2 class="doctor-result-title">Available Doctors (<?= count($doctors) ?>)</h2>

<div class="doctor-grid">
    <?php foreach ($doctors as $doc): ?>
        <div class="doctor-card">
            <h3><?= htmlspecialchars($doc['full_name']) ?></h3>
            <p><?= htmlspecialchars($doc['department']) ?> — <?= htmlspecialchars($doc['specialization']) ?></p>
            <p class="doctor-bio"><?= htmlspecialchars($doc['bio'] ?? '') ?></p>
            <a href="book.php?doctor_id=<?= $doc['id'] ?>" class="btn doctor-book-btn"><?= patientIcon('calendar') ?>
                Book Appointment</a>
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
