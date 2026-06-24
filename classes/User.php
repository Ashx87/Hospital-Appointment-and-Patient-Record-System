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

require_once __DIR__ . '/Database.php';

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
        try {
            $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            $row = $stmt->fetch();
            return $row !== false ? $row : null;
        } catch (PDOException $e) {
            error_log('User::findByEmail error: ' . $e->getMessage());
            return null;
        }
    }

    /** Find a user by ID */
    public function findById(int $id): ?array
    {
        try {
            $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            return $row !== false ? $row : null;
        } catch (PDOException $e) {
            error_log('User::findById error: ' . $e->getMessage());
            return null;
        }
    }

    /** Get all users (for the Admin management page), optionally filtered by role */
    public function findAll(?string $role = null): array
    {
        try {
            if ($role !== null && $role !== '') {
                $stmt = $this->pdo->prepare(
                    'SELECT * FROM users WHERE role = ? ORDER BY created_at DESC'
                );
                $stmt->execute([$role]);
            } else {
                $stmt = $this->pdo->query('SELECT * FROM users ORDER BY created_at DESC');
            }
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('User::findAll error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Create a new user (automatically hashes the password with password_hash()).
     * Expected $data keys: role, email, password, full_name, phone (optional).
     * Returns the new user id. Throws PDOException on failure (e.g. duplicate email)
     * so the caller can roll back a surrounding transaction and report the error.
     */
    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (role, email, password_hash, full_name, phone)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['role'],
            trim($data['email']),
            password_hash($data['password'], PASSWORD_DEFAULT),
            trim($data['full_name']),
            !empty($data['phone']) ? trim($data['phone']) : null,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    /** Update basic user information (name, phone) */
    public function update(int $id, array $data): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE users SET full_name = ?, phone = ? WHERE id = ?'
        );
        $stmt->execute([
            trim($data['full_name']),
            !empty($data['phone']) ? trim($data['phone']) : null,
            $id,
        ]);
    }

    /** Toggle account status (active ↔ inactive) */
    public function toggleStatus(int $id): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE users SET status = IF(status = 'active', 'inactive', 'active') WHERE id = ?"
        );
        $stmt->execute([$id]);
    }

    /**
     * Delete a user (Admin only; cascades to related patients/doctors records).
     * Throws PDOException when the row is referenced by appointments and cannot be
     * removed; the calling page catches it and suggests deactivating instead.
     */
    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$id]);
    }
}
