<?php
/**
 * classes/Doctor.php — Data access class for the doctors table
 *
 * Responsibilities:
 *   - Encapsulate CRUD operations on the doctors table
 *   - doctors is a 1:1 extension of users (stores specialization and department info)
 *   - Provides queries for filtering doctors by department/name (used by the patient search page)
 *
 * doctors table fields:
 *   id, user_id(FK→users, UNIQUE), specialization(VARCHAR),
 *   department(VARCHAR), bio(TEXT)
 *
 * Callers:
 *   pages/patient/find-doctor.php (patient searches/filters doctors)
 *   pages/doctor/dashboard.php (doctor views/edits their own profile)
 *   pages/admin/doctors.php (Admin manages departments and doctors)
 */

require_once __DIR__ . '/../config/Database.php';

class Doctor
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    /** Find a doctor profile by user_id */
    public function findByUserId(int $userId): ?array
    {
        // TODO: SELECT d.*, u.full_name, u.email, u.phone
        //       FROM doctors d JOIN users u ON u.id = d.user_id
        //       WHERE d.user_id = ?
        return null;
    }

    /** Find a doctor by doctor.id */
    public function findById(int $id): ?array
    {
        // TODO: SELECT d.*, u.full_name FROM doctors d
        //       JOIN users u ON u.id = d.user_id WHERE d.id = ?
        return null;
    }

    /**
     * List all available doctors (used for patient search)
     * Supports filtering by department or full_name LIKE
     */
    public function findAll(?string $department = null, ?string $name = null): array
    {
        // TODO: SELECT d.*, u.full_name FROM doctors d JOIN users u ON u.id = d.user_id
        //       WHERE u.status = 'active'
        //       [AND d.department = ?] [AND u.full_name LIKE ?]
        //       ORDER BY u.full_name
        return [];
    }

    /** Get all department names (used for the dropdown filter) */
    public function getDepartments(): array
    {
        // TODO: SELECT DISTINCT department FROM doctors ORDER BY department
        return [];
    }

    /** Doctor updates their own profile (specialization, department, bio) */
    public function update(int $id, array $data): void
    {
        // TODO: UPDATE doctors SET specialization=?, department=?, bio=? WHERE id = ?
    }

    /** Admin creates a new doctor (must create users record first, then doctors record, using a PDO transaction) */
    public function create(int $userId, array $data): int
    {
        // TODO: INSERT INTO doctors (user_id, specialization, department, bio)
        return 0;
    }
}
