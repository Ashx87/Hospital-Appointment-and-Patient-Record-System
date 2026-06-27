<?php
/**
 * classes/Doctor.php — Data access class for the doctors table
 *
 * Responsibilities:
 *   - Encapsulate CRUD operations on the doctors table
 *   - doctors is a 1:1 extension of users (stores specialization and department info)
 *   - Provides queries for filtering doctors by department, specialization or doctor name (used by the patient search page)
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

require_once __DIR__ . '/Database.php';

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
        try {
            $stmt = $this->pdo->prepare(
                'SELECT d.*, u.full_name, u.status, u.email, u.phone
                 FROM doctors d
                 JOIN users u ON u.id = d.user_id
                 WHERE d.user_id = ? LIMIT 1'
            );
            $stmt->execute([$userId]);
            $row = $stmt->fetch();
            return $row !== false ? $row : null;
        } catch (PDOException $e) {
            error_log('Doctor::findByUserId error: ' . $e->getMessage());        
            return null;
        }
    }

    /** Find a doctor by doctor.id */
    public function findById(int $id): ?array
    {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT d.*, u.full_name, u.status, u.email, u.phone
                 FROM doctors d JOIN users u ON u.id = d.user_id
                 WHERE d.id = ? LIMIT 1'
            );
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            return $row !== false ? $row : null;
        } catch (PDOException $e) {
            error_log('Doctor::findById error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * List all available doctors (used for patient search).
     * Returns only ACTIVE doctors; supports filtering by department, specialization, full_name LIKE.
     */
    public function findAll(?string $department = null, ?string $specialization = null, ?string $name = null): array
    {
        try {
            $sql    = 'SELECT d.*, u.full_name FROM doctors d
                       JOIN users u ON u.id = d.user_id
                       WHERE u.status = \'active\'';
            $params = [];
            if ($department !== null && $department !== '') {
                $sql .= ' AND d.department = ?';
                $params[]  = $department;
            }

            if (!empty($specialization)) {
                $sql .= " AND d.specialization = ?";
                $params[] = $specialization;
            }

            if ($name !== null && $name !== '') {
                $sql .= ' AND u.full_name LIKE ?';
                $params[]  = '%' . $name . '%';
            }

            $sql .= ' ORDER BY u.full_name';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('Doctor::findAll error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * List ALL doctors (including inactive) with their account status.
     * Used by the Admin management page, which must see and toggle every doctor.
     */
    public function findAllForAdmin(): array
    {
        try {
            $stmt = $this->pdo->query(
                'SELECT d.*, u.full_name, u.email, u.phone, u.status
                 FROM doctors d JOIN users u ON u.id = d.user_id
                 ORDER BY u.full_name'
            );
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('Doctor::findAllForAdmin error: ' . $e->getMessage());
            return [];
        }
    }

    /** Get all department names (used for the dropdown filter) */
    public function getDepartments(): array
    {
        try {
            $stmt = $this->pdo->query(
                "SELECT DISTINCT department FROM doctors
                 WHERE department IS NOT NULL AND department <> ''
                 ORDER BY department"
            );
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            error_log('Doctor::getDepartments error: ' . $e->getMessage());
            return [];
        }
    }

    /** Get all specialization names (used for the dropdown filter) */
    public function getSpecializations(): array
    {
        try {
            $stmt = $this->pdo->query("
                SELECT DISTINCT specialization
                FROM doctors
                WHERE specialization IS NOT NULL
                AND specialization <> ''
                ORDER BY specialization
            ");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch(PDOException $e) {
            error_log("Doctor::getSpecializations ".$e->getMessage());
            return [];
        }
    }

    /** Update a doctor profile (specialization, department, bio) */
    public function update(int $id, array $data): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE doctors SET specialization = ?, department = ?, bio = ? WHERE id = ?'
        );
        $stmt->execute([
            !empty($data['specialization']) ? trim($data['specialization']) : null,
            !empty($data['department']) ? trim($data['department']) : null,
            !empty($data['bio']) ? trim($data['bio']) : null,
            $id,
        ]);
    }

    /**
     * Create a new doctor profile row (the users row must be created first).
     * Returns the new doctors.id. Throws PDOException on failure so the caller's
     * surrounding transaction can roll back. Expected $data keys: specialization,
     * department, bio (all optional).
     */
    public function create(int $userId, array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO doctors (user_id, specialization, department, bio)
             VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([
            $userId,
            !empty($data['specialization']) ? trim($data['specialization']) : null,
            !empty($data['department']) ? trim($data['department']) : null,
            !empty($data['bio']) ? trim($data['bio']) : null,
        ]);
        return (int) $this->pdo->lastInsertId();
    }
}
