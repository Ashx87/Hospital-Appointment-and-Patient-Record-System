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

require_once __DIR__ . '/Database.php';

class Patient
{
    private PDO $pdo;
    public function __construct()
    {
        $this->pdo=Database::getInstance();
    }

    //Find a patient profile by user_id
    public function findByUserId(int $userId):?array
    {
        try{
            $sql="SELECT p.*, u.full_name, u.email, u.phone
            FROM patients p
            JOIN users u ON u.id=p.user_id
            WHERE p.user_id= ? LIMIT 1";

            $stmt=$this->pdo->prepare($sql);
            $stmt->execute([$userId]);
            $row=$stmt->fetch();
            return $row !== false ? $row:null;
        }catch(PDOException $e){
            header('Location: ' . BASE_URL . 'error.php?code=500&msg=' . urlencode('Failed to retrieve patient profile.'));
            exit;
        }
    }

    //Find a patient by patient.id
    public function findById(int $id): ?array
    {
        try{
            $sql="SELECT p.*, u.full_name
            FROM patients p
            JOIN users u ON u.id=p.user_id
            WHERE p.id= ? LIMIT 1";

            $stmt=$this->pdo->prepare($sql);
            $stmt->execute([$id]);
            $row=$stmt->fetch();
            return $row !== false ? $row:null;
        }catch(PDOException $e){
            error_log('Patient::findById error: '.$e->getMessage());
            return null;
        } 
    }

    //Create a new patient profile
    public function create(int $userId, array $data): int
    {
        try {
            $sql = "INSERT INTO patients (user_id, date_of_birth, gender, blood_type, allergies, address) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt=$this->pdo->prepare($sql);
            $stmt->execute([
                $userId,
                $data['date_of_birth'],
                $data['gender'],
                !empty($data['blood_type']) ? trim($data['blood_type']) : null,
                !empty($data['allergies']) ? trim($data['allergies']) : null,
                !empty($data['address']) ? trim($data['address']) : null,
            ]);
            return (int) $this->pdo->lastInsertId();
        }catch (PDOException $e){
            throw $e; 
        }
    }

    //Patient updates their own profile
    public function update(int $id, array $data): void
    {
        try {
            $sql = "UPDATE patients 
                    SET date_of_birth=?, gender=?, blood_type=?, allergies=?, address=? 
                    WHERE id=?";
            
            $stmt=$this->pdo->prepare($sql);
            $stmt->execute([
                $data['date_of_birth'],
                $data['gender'],
                !empty($data['blood_type']) ? trim($data['blood_type']) : null,
                !empty($data['allergies']) ? trim($data['allergies']) : null,
                !empty($data['address']) ? trim($data['address']) : null,
                $id
            ]);
        } catch(PDOException $e){
            header('Location: ' . BASE_URL . 'error.php?code=500&msg=' . urlencode('Profile update failed.'));
            exit;
        }
    }
}
