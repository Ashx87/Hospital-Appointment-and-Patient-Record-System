<?php
/**
 * classes/VisitNote.php — Data access class for the visit_notes table
 *
 * Responsibilities:
 *   - Doctor creates a visit note for a completed appointment (diagnosis + notes)
 *   - Patient views their own historical visit notes (read-only)
 *   - Each appointment can only have one visit_note (UNIQUE constraint)
 *
 * visit_notes table fields:
 *   id, appointment_id(FK→appointments, UNIQUE),
 *   doctor_id(FK→doctors), diagnosis(TEXT), notes(TEXT),
 *   visited_at(DATETIME)
 *
 * Callers:
 *   pages/doctor/write-note.php (doctor creates a visit note)
 *   pages/patient/my-records.php (patient views historical records)
 *   pages/doctor/my-appointments.php (doctor views written notes)
 */

require_once __DIR__ . '/Database.php';

class VisitNote
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    /** Find a visit note by appointment ID */
    public function findByAppointment(int $appointmentId): ?array
    {
        // TODO: SELECT vn.*, u.full_name AS doctor_name
        //       FROM visit_notes vn JOIN doctors d ON d.id = vn.doctor_id
        //       JOIN users u ON u.id = d.user_id
        //       WHERE vn.appointment_id = ?
        return null;
    }

    /** Get all visit notes for a given patient (ordered by time descending) */
    public function findByPatient(int $patientId): array
    {
        // TODO: SELECT vn.*, s.slot_date, u.full_name AS doctor_name
        //       FROM visit_notes vn
        //       JOIN appointments a ON a.id = vn.appointment_id
        //       JOIN slots s ON s.id = a.slot_id
        //       JOIN doctors d ON d.id = vn.doctor_id
        //       JOIN users u ON u.id = d.user_id
        //       WHERE a.patient_id = ? ORDER BY vn.visited_at DESC
        return [];
    }

    /**
     * Create a visit note (also calls Appointment::markCompleted() after writing)
     * visited_at is automatically populated using the database NOW()
     */
    public function create(int $appointmentId, int $doctorId, array $data): int
    {
        // TODO: INSERT INTO visit_notes (appointment_id, doctor_id, diagnosis, notes, visited_at)
        //       VALUES (?, ?, ?, ?, NOW())  returns lastInsertId()
        return 0;
    }

    /** Doctor updates the content of a visit note */
    public function update(int $id, array $data): void
    {
        // TODO: UPDATE visit_notes SET diagnosis=?, notes=? WHERE id = ?
    }
}
