<?php
/**
 * classes/Appointment.php — Data access class for the appointments table
 *
 * Responsibilities:
 *   - Booking creation: within a PDO transaction, set slot.status to 'booked'
 *     + insert the appointments record, guaranteeing atomicity (both steps must succeed)
 *   - Cancellation: within a transaction, set appointments.status to 'cancelled'
 *     + set slot.status back to 'open' (reopens the time slot)
 *   - Query appointment lists needed by each role
 *
 * appointments table fields:
 *   id, slot_id(FK→slots, UNIQUE), patient_id(FK→patients),
 *   booked_by(FK→users), status(booked|completed|cancelled) DEFAULT booked,
 *   created_at
 *
 * Callers:
 *   pages/patient/book.php (patient books an appointment)
 *   pages/patient/my-appointments.php (patient views/cancels appointments)
 *   pages/doctor/my-appointments.php (doctor views appointments by date)
 *   pages/receptionist/manage-appointments.php (receptionist manages appointments)
 *   pages/admin/reports.php (report statistics)
 */

require_once __DIR__ . '/Database.php';

class Appointment
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    /**
     * Create an appointment (PDO transaction guarantees atomicity):
     * 1. Verify the slot is still 'open' (prevents race conditions)
     * 2. INSERT INTO appointments
     * 3. UPDATE slots SET status='booked'
     * On failure, ROLLBACK and throw an exception
     */
    public function book(int $slotId, int $patientId, int $bookedBy): int
    {
        try{

            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("
                SELECT status
                FROM slots
                WHERE id = ?
                FOR UPDATE
            ");

            $stmt->execute([$slotId]);

            $slot = $stmt->fetch();

            if(!$slot){
                throw new Exception("Slot not found.");
            }

            if($slot['status'] !== 'open'){
                throw new Exception("Selected slot is unavailable.");
            }

            $stmt = $this->pdo->prepare("
                INSERT INTO appointments
                (slot_id, patient_id, booked_by)
                VALUES (?, ?, ?)
            ");

            $stmt->execute([
                $slotId,
                $patientId,
                $bookedBy
            ]);

            $appointmentId = $this->pdo->lastInsertId();

            $stmt = $this->pdo->prepare("
                UPDATE slots
                SET status='booked'
                WHERE id=?
            ");

            $stmt->execute([$slotId]);

            $this->pdo->commit();

            return (int)$appointmentId;

        }catch(Exception $e){

            if($this->pdo->inTransaction()){
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }

    /**
     * Cancel an appointment (PDO transaction):
     * 1. UPDATE appointments SET status='cancelled'
     * 2. UPDATE slots SET status='open' (reopen the time slot for others to book)
     */
    public function cancel(int $id): void
    {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("
                SELECT slot_id, status
                FROM appointments
                WHERE id = ?
                FOR UPDATE
            ");
            $stmt->execute([$id]);
            $appointment = $stmt->fetch();

            if (!$appointment) {
                throw new Exception('Appointment not found.');
            }

            if ($appointment['status'] === 'cancelled') {
                throw new Exception('Appointment is already cancelled.');
            }

            if ($appointment['status'] === 'completed') {
                throw new Exception('Completed appointments cannot be cancelled.');
            }

            $stmt = $this->pdo->prepare("
                UPDATE appointments
                SET status = 'cancelled'
                WHERE id = ?
            ");
            $stmt->execute([$id]);

            $stmt = $this->pdo->prepare("
                UPDATE slots
                SET status = 'open'
                WHERE id = ?
            ");
            $stmt->execute([(int)$appointment['slot_id']]);

            $this->pdo->commit();

        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }

    /** Mark an appointment as completed (called after the doctor writes the visit note) */
    public function markCompleted(int $id): void
    {
        // TODO: UPDATE appointments SET status='completed' WHERE id = ?
    }

    /** Find a single appointment by ID (including slot, doctor, and patient JOIN details) */
    public function findById(int $id): ?array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT a.*, s.slot_date, s.start_time, s.end_time, 
                p.id AS patient_id, d.id AS doctor_id, u_p.full_name AS patient_name, u_d.full_name AS doctor_name, u_b.full_name AS booked_by_name
                FROM appointments a
                JOIN slots s ON s.id = a.slot_id
                JOIN patients p ON p.id = a.patient_id
                JOIN users u_p ON u_p.id = p.user_id
                JOIN doctors d ON d.id = s.doctor_id
                JOIN users u_d ON u_d.id = d.user_id
                JOIN users u_b ON u_b.id = a.booked_by
                WHERE a.id = ?
                LIMIT 1
            ");

            $stmt->execute([$id]);
            $row = $stmt->fetch();

            return $row !== false ? $row : null;

        } catch (PDOException $e) {
            error_log('Appointment::findById error: ' . $e->getMessage());
            return null;
        }
    }

    /** Get all appointments for a given patient */
    public function findByPatient(int $patientId): array
    {
        // TODO: SELECT ... WHERE a.patient_id = ? ORDER BY s.slot_date DESC
        return [];
    }

    /** Get the appointment list for a given doctor on a specific date */
    public function findByDoctor(int $doctorId, ?string $date = null): array
    {
        // TODO: SELECT ... WHERE s.doctor_id = ? [AND s.slot_date = ?]
        return [];
    }

    /** Get all appointments (for receptionist/Admin), optionally filtered by status */
    public function findAll(?string $status = null): array
    {
        try {
            $sql = "
                SELECT a.*, s.slot_date, s.start_time, s.end_time, 
                p.id AS patient_id, d.id AS doctor_id, u_p.full_name AS patient_name, u_d.full_name AS doctor_name, u_b.full_name AS booked_by_name
                FROM appointments a
                JOIN slots s ON s.id = a.slot_id
                JOIN patients p ON p.id = a.patient_id
                JOIN users u_p ON u_p.id = p.user_id
                JOIN doctors d ON d.id = s.doctor_id
                JOIN users u_d ON u_d.id = d.user_id
                JOIN users u_b ON u_b.id = a.booked_by
            ";

            $params = [];

            if ($status !== null && $status !== '') {
                $sql .= " WHERE a.status = ? ";
                $params[] = $status;
            }

            $sql .= " ORDER BY s.slot_date DESC, s.start_time DESC ";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll();

        } catch (PDOException $e) {
            error_log('Appointment::findAll error: ' . $e->getMessage());
            return [];
        }
    }
}
