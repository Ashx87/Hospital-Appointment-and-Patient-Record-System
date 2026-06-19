<?php
/**
 * classes/Patient.php — Data access class for the patients table
 *
 * Responsibilities:
 *   - Encapsulate CRUD operations on the patients table
 *   - patients is a 1:1 extension of users (stores medical profile information)
 *   - Creating a new patient requires inserting records in both users and patients (PDO transaction guarantees atomicity)
 *
 * patients table fields:
 *   id, user_id(FK→users, UNIQUE), date_of_birth(DATE), gender(ENUM male|female|other),
 *   blood_type(VARCHAR), allergies(TEXT), address(TEXT)
 *
 * Callers:
 *   pages/patient/dashboard.php (patient views/edits their own profile)
 *   pages/receptionist/register-patient.php (register walk-in patient)
 *   pages/admin/users.php (Admin views patient list)
 */

require_once __DIR__ . '/../config/Database.php';

class Patient
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    /** Find a patient profile by user_id (JOINs users to get full information) */
    public function findByUserId(int $userId): ?array
    {
        // TODO: SELECT p.*, u.full_name, u.email, u.phone
        //       FROM patients p JOIN users u ON u.id = p.user_id
        //       WHERE p.user_id = ?
        return null;
    }

    /** Find a patient by patient.id */
    public function findById(int $id): ?array
    {
        // TODO: SELECT p.*, u.full_name FROM patients p
        //       JOIN users u ON u.id = p.user_id WHERE p.id = ?
        return null;
    }

    /**
     * Create a new patient profile (used by the receptionist to register a walk-in patient)
     * A PDO transaction must be started externally; inserts into users + patients together, rolls back on failure
     */
    public function create(int $userId, array $data): int
    {
        // TODO: INSERT INTO patients (user_id, date_of_birth, gender, blood_type, allergies, address)
        //       VALUES (?, ?, ?, ?, ?, ?)  returns lastInsertId()
        return 0;
    }

    /** Patient updates their own profile (blood type, allergies, address, etc.) */
    public function update(int $id, array $data): void
    {
        // TODO: UPDATE patients SET date_of_birth=?, gender=?, blood_type=?, allergies=?, address=?
        //       WHERE id = ?
    }
}
