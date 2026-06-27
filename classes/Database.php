<?php
/**
 * classes/Database.php — PDO Database Connection Singleton
 *
 * Responsibilities:
 *   - Maintains a single PDO connection instance throughout the application (Singleton pattern)
 *   - All entity classes under classes/ obtain the connection via Database::getInstance()
 *   - Configures charset (utf8mb4), error mode (exceptions), and disables emulated prepared statements
 *     (Disabling emulated prepares ensures parameter types are handled correctly by the DB, preventing SQL injection)
 *
 * Callers: classes/User.php, Patient.php, Doctor.php, Slot.php,
 *          Appointment.php, VisitNote.php, Prescription.php
 *
 * Usage:
 *   $pdo = Database::getInstance();
 *   $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
 */

require_once __DIR__ . '/../config/config.example.php';

class Database
{
    private static ?PDO $instance = null;
    private function __construct() {}
    public static function getInstance(): PDO
    {
        if (self::$instance===null){
            try {
                $dsn='mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
                
                self::$instance=new PDO($dsn, DB_USER, DB_PASS,[
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            }
            catch(PDOException $e){
                header('Location: ' . BASE_URL . 'error.php?code=500&msg=' . urlencode('A database connection issue occurred.'));
                exit;
            }
        }
        return self::$instance;
    }
}
