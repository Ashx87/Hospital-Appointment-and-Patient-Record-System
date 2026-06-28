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
        $this->pdo=Database::getInstance();
    }

    public function search(string $query):array
    {
        try{
            $stmt=$this->pdo->prepare('SELECT*FROM users WHERE full_name LIKE ? OR email LIKE ? ORDER BY created_at DESC');
            $searchTerm='%'.trim($query).'%';
            $stmt->execute([$searchTerm, $searchTerm]);
            return $stmt->fetchAll();
        }catch (PDOException $e){
            error_log('User::search error: '.$e->getMessage());
            return [];
        }
    }

    //CRUD
    public function create(array $data):int
    {
        try{
            $stmt=$this->pdo->prepare(
                'INSERT INTO users(role, email, password_hash, full_name, phone, status)VALUES(?, ?, ?, ?, ?, "active")'
            );
            $stmt->execute([
                $data['role'],
                trim($data['email']),
                password_hash($data['password'], PASSWORD_DEFAULT),
                trim($data['full_name']),
                !empty($data['phone']) ? trim($data['phone']):null,
            ]);
            return (int) $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public function update(int $id, array $data):void
    {
        try{
            $stmt=$this->pdo->prepare('UPDATE users SET full_name = ?, phone = ? WHERE id = ?');
            $stmt->execute([
                trim($data['full_name']),
                !empty($data['phone']) ? trim($data['phone']):null,
                $id,
            ]);
        }catch (PDOException $e){
            header('Location: ' . BASE_URL . 'error.php?code=500&msg=' . urlencode('Database update failed.'));
            exit;
        }
    }

    public function toggleStatus(int $id):void
    {
        try{
            $stmt=$this->pdo->prepare("UPDATE users SET status=IF(status='active', 'inactive', 'active') WHERE id = ?");
            $stmt->execute([$id]);
        }catch(PDOException $e) {
            header('Location: ' . BASE_URL . 'error.php?code=500&msg=' . urlencode('Status update failed.'));
            exit;
        }
    }

    public function delete(int $id):void
    {
        try{
            $stmt=$this->pdo->prepare('DELETE FROM users WHERE id = ?');
            $stmt->execute([$id]);
        } catch (PDOException $e) {
            header('Location: ' . BASE_URL . 'error.php?code=500&msg=' . urlencode('Cannot delete: User is referenced by existing records.'));
            exit;
        }
    }

    //Find a user by email
    public function findByEmail(string $email):?array
    {
        try{
            $stmt=$this->pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            $row=$stmt->fetch();
            return $row !== false ?$row:null;
        }catch (PDOException $e){
            error_log('User::findByEmail error: ' . $e->getMessage());
            return null;
        }
    }

    //Find a user by ID
    public function findById(int $id): ?array
    {
        try{
            $stmt=$this->pdo->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
            $stmt->execute([$id]);
            $row=$stmt->fetch();
            return $row !== false ?$row:null;
        }catch (PDOException $e){
            error_log('User::findById error: ' . $e->getMessage());
            return null;
        }
    }

    //Get all users
    public function findAll(?string $role = null):array
    {
        try {
            if ($role !== null && $role !== ''){
                $stmt=$this->pdo->prepare(
                    'SELECT * FROM users WHERE role = ? ORDER BY created_at DESC'
                );
                $stmt->execute([$role]);
            }else{
                $stmt=$this->pdo->query('SELECT * FROM users ORDER BY created_at DESC');
            }
            return $stmt->fetchAll();
        } catch (PDOException $e){
            error_log('User::findAll error: ' . $e->getMessage());
            return [];
        }
    }
}
