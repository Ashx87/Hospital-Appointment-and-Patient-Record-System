<?php
/**
 * classes/Prescription.php — Data access class for the prescriptions table
 *
 * Responsibilities:
 *   - Doctor issues one or more prescriptions under a visit note
 *   - Patient views their own prescriptions via the records page (read-only)
 *   - One visit_note can have multiple prescriptions (one-to-many relationship)
 *
 * prescriptions table fields:
 *   id, visit_note_id(FK→visit_notes), medicine_name(VARCHAR),
 *   dosage(VARCHAR), instructions(TEXT), created_at(DATETIME)
 *
 * Callers:
 *   pages/doctor/write-note.php (doctor issues prescriptions)
 *   pages/patient/my-records.php (patient views prescriptions, read-only)
 */

require_once __DIR__ . '/Database.php';

class Prescription
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    /** Get all prescriptions for a given visit note (ordered by creation time) */
    public function findByVisitNote(int $visitNoteId): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM prescriptions
                WHERE visit_note_id = ? ORDER BY created_at ASC
            ");

            $stmt->execute([$visitNoteId]);
            return $stmt->fetchAll();

        } catch (PDOException $e) {
            error_log('Prescription::findByVisitNote error: '.$e->getMessage());
            return [];
        }
    }

    /**
     * Create a new prescription (can be called multiple times; one visit can have multiple medications)
     * created_at is automatically populated using the database NOW()
     */
    public function create(int $visitNoteId, array $data): int
    {
        // TODO: INSERT INTO prescriptions
        //       (visit_note_id, medicine_name, dosage, instructions, created_at)
        //       VALUES (?, ?, ?, ?, NOW())  returns lastInsertId()
        return 0;
    }

    /** Delete a prescription (used by the doctor to correct entries before submitting) */
    public function delete(int $id): void
    {
        // TODO: DELETE FROM prescriptions WHERE id = ?
    }
}
