<?php
/**
 * classes/Slot.php — Data access class for the slots table
 *
 * Responsibilities:
 *   - Doctor publishes/blocks/deletes time slots
 *   - Query open time slots for a given doctor on a given date (used by the patient booking page)
 *   - On booking, set status to 'booked'; on cancellation, set back to 'open'
 *     (status changes are called by the Appointment class inside a transaction to guarantee atomicity)
 *
 * slots table fields:
 *   id, doctor_id(FK→doctors), slot_date(DATE), start_time(TIME),
 *   end_time(TIME), status(open|booked|blocked) DEFAULT open
 *
 * Callers:
 *   pages/doctor/my-slots.php (doctor manages their own time slots)
 *   pages/patient/book.php (patient views available time slots)
 *   classes/Appointment.php (updates slot.status on booking/cancellation)
 */

require_once __DIR__ . '/Database.php';

class Slot
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    /** Get all time slots for a given doctor (for the doctor management page), optionally filtered by date */
    public function findByDoctor(int $doctorId, ?string $date = null): array
    {
        try {
            if ($date !== null && $date !== '') {
                $stmt = $this->pdo->prepare(
                    'SELECT *
                    FROM slots
                    WHERE doctor_id = ? AND slot_date = ?
                    ORDER BY slot_date ASC, start_time ASC'
                );
                $stmt->execute([$doctorId, $date]);
            } else {
                $stmt = $this->pdo->prepare(
                    'SELECT *
                    FROM slots
                    WHERE doctor_id = ?
                    ORDER BY slot_date ASC, start_time ASC'
                );
                $stmt->execute([$doctorId]);
            }

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('Slot::findByDoctor error: ' . $e->getMessage());
            return [];
        }
    }

    /** Get open time slots for a given doctor on a specific date (for the patient booking page) */
    public function findOpenByDoctor(int $doctorId, string $date): array
    {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT *
                FROM slots
                WHERE doctor_id = ?
                AND slot_date = ?
                AND status = 'open'
                ORDER BY start_time ASC"
            );
            $stmt->execute([$doctorId, $date]);

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('Slot::findOpenByDoctor error: ' . $e->getMessage());
            return [];
        }
    }

    /** Find a single time slot by ID */
    public function findById(int $id): ?array
    {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT *
                FROM slots
                WHERE id = ?
                LIMIT 1'
            );
            $stmt->execute([$id]);
            $row = $stmt->fetch();

            return $row !== false ? $row : null;
        } catch (PDOException $e) {
            error_log('Slot::findById error: ' . $e->getMessage());
            return null;
        }
    }

    /** Doctor creates a new time slot */
    public function create(int $doctorId, array $data): int
    {
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO slots (doctor_id, slot_date, start_time, end_time, status)
                VALUES (?, ?, ?, ?, "open")'
            );

            $stmt->execute([
                $doctorId,
                $data['slot_date'],
                $data['start_time'],
                $data['end_time'],
            ]);

            return (int)$this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log('Slot::create error: ' . $e->getMessage());
            throw $e;
        }
    }

    /** Update a time slot's status (open | booked | blocked) */
    public function updateStatus(int $id, string $status): void
    {
        if (!in_array($status, ['open', 'booked', 'blocked'], true)) {
            throw new Exception('Invalid slot status.');
        }

        try {
            $stmt = $this->pdo->prepare(
                'UPDATE slots
                SET status = ?
                WHERE id = ?'
            );
            $stmt->execute([$status, $id]);
        } catch (PDOException $e) {
            error_log('Slot::updateStatus error: ' . $e->getMessage());
            throw $e;
        }
    }

    /** Delete a time slot (only allowed when status='open' and not yet booked) */
    public function delete(int $id): void
    {
        try {
            $stmt = $this->pdo->prepare(
                "DELETE FROM slots
                WHERE id = ? AND status = 'open'"
            );
            $stmt->execute([$id]);

            if ($stmt->rowCount() === 0) {
                throw new Exception('Only open slots can be deleted.');
            }
        } catch (PDOException $e) {
            error_log('Slot::delete error: ' . $e->getMessage());
            throw $e;
        }
    }
}
