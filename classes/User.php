<?php
/**
 * classes/User.php — Data access class for the users table
 *
 * Responsibilities:
 *   - Encapsulate CRUD operations on the users table
 *   - Use PDO prepared statements to prevent SQL injection
 *   - Password hashing: store with password_hash(), verify with password_verify()
 *
 * users table fields:
 *   id, role(admin|doctor|patient|receptionist), email(UNIQUE),
 *   password_hash, full_name, phone, status(active|inactive), created_at
 *
 * Callers:
 *   classes/Auth.php (login verification)
 *   pages/admin/users.php (user management CRUD)
 */

require_once __DIR__ . '/../config/Database.php';

class User
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    /** Find a user by email (used for login verification) */
    public function findByEmail(string $email): ?array
    {
        // TODO: SELECT * FROM users WHERE email = ? LIMIT 1
        return null;
    }

    /** Find a user by ID */
    public function findById(int $id): ?array
    {
        // TODO: SELECT * FROM users WHERE id = ?
        return null;
    }

    /** Get all users (for the Admin management page), optionally filtered by role */
    public function findAll(?string $role = null): array
    {
        // TODO: SELECT * FROM users [WHERE role = ?] ORDER BY created_at DESC
        return [];
    }

    /** Create a new user (automatically hashes the password with password_hash()) */
    public function create(array $data): int
    {
        // TODO: INSERT INTO users (role, email, password_hash, full_name, phone)
        //       returns lastInsertId()
        return 0;
    }

    /** Update basic user information (name, phone) */
    public function update(int $id, array $data): void
    {
        // TODO: UPDATE users SET full_name=?, phone=? WHERE id = ?
    }

    /** Toggle account status (active ↔ inactive) */
    public function toggleStatus(int $id): void
    {
        // TODO: UPDATE users SET status = IF(status='active','inactive','active') WHERE id = ?
    }

    /** Delete a user (Admin only; cascades to related patients/doctors records) */
    public function delete(int $id): void
    {
        // TODO: DELETE FROM users WHERE id = ?
    }
}
