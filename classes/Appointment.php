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

require_once __DIR__ . '/../config/Database.php';

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
        // TODO: $this->pdo->beginTransaction(); ... commit/rollback
        return 0;
    }

    /**
     * Cancel an appointment (PDO transaction):
     * 1. UPDATE appointments SET status='cancelled'
     * 2. UPDATE slots SET status='open' (reopen the time slot for others to book)
     */
    public function cancel(int $id): void
    {
        // TODO: transaction ensures both steps succeed together, otherwise roll back
    }

    /** Mark an appointment as completed (called after the doctor writes the visit note) */
    public function markCompleted(int $id): void
    {
        // TODO: UPDATE appointments SET status='completed' WHERE id = ?
    }

    /** Find a single appointment by ID (including slot, doctor, and patient JOIN details) */
    public function findById(int $id): ?array
    {
        // TODO: SELECT a.*, s.slot_date, s.start_time, s.end_time,
        //       u_p.full_name AS patient_name, u_d.full_name AS doctor_name
        //       FROM appointments a
        //       JOIN slots s ON s.id = a.slot_id
        //       JOIN patients p ON p.id = a.patient_id
        //       JOIN users u_p ON u_p.id = p.user_id
        //       JOIN doctors d ON d.id = s.doctor_id
        //       JOIN users u_d ON u_d.id = d.user_id
        //       WHERE a.id = ?
        return null;
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
        // TODO: SELECT ... [WHERE a.status = ?] ORDER BY s.slot_date DESC
        return [];
    }
}
