-- sql/seed.sql — Initial test data
--
-- Responsibilities:
--   - Insert demo test data after schema.sql creates the tables
--   - Covers all 4 roles, making it easy to demonstrate each role's features in the demo recording
--   - All passwords are "Password123!" (pre-hashed using PHP password_hash())
--   - Includes sample doctors, time slots, appointments, and visit notes
--
-- Usage:
--   In phpMyAdmin, run schema.sql first, then run this file
--
-- Note: this is demo data for local development/demonstration only and does not contain real patient information

USE hospital_db;

-- ─── Test accounts (all passwords are Password123!) ─────────────────
-- password_hash values generated with PASSWORD_DEFAULT algorithm (demo placeholder values)
INSERT INTO users (role, email, password_hash, full_name, phone, status) VALUES
('admin',        'admin@hospital.com',        '$2y$12$PLACEHOLDER_HASH_ADMIN',        'System Admin',      '0123456789', 'active'),
('doctor',       'dr.smith@hospital.com',     '$2y$12$PLACEHOLDER_HASH_DR_SMITH',     'Dr. John Smith',    '0111234567', 'active'),
('doctor',       'dr.lee@hospital.com',       '$2y$12$PLACEHOLDER_HASH_DR_LEE',       'Dr. Sarah Lee',     '0122345678', 'active'),
('patient',      'patient@example.com',       '$2y$12$PLACEHOLDER_HASH_PATIENT',      'Alice Johnson',     '0133456789', 'active'),
('receptionist', 'reception@hospital.com',    '$2y$12$PLACEHOLDER_HASH_RECEPTION',    'Bob Receptionist',  '0144567890', 'active');

-- ─── Patient profiles ────────────────────────────────────────────────
INSERT INTO patients (user_id, date_of_birth, gender, blood_type, allergies, address) VALUES
(4, '1990-05-15', 'female', 'O+', 'Penicillin', '123 Main Street, Kuala Lumpur');

-- ─── Doctor profiles ─────────────────────────────────────────────────
INSERT INTO doctors (user_id, specialization, department, bio) VALUES
(2, 'Cardiology',    'Cardiology',    'Specialist in heart conditions with 10 years of experience.'),
(3, 'Dermatology',   'Dermatology',   'Expert in skin conditions and cosmetic procedures.');

-- ─── Time slots (examples: next 3 days) ──────────────────────────────
-- Note: replace CURDATE()+1 etc. with specific dates for actual use
INSERT INTO slots (doctor_id, slot_date, start_time, end_time, status) VALUES
(1, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '09:00', '09:30', 'open'),
(1, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '09:30', '10:00', 'open'),
(1, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '10:00', '10:30', 'open'),
(2, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '14:00', '14:30', 'open'),
(2, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '10:00', '10:30', 'open');

-- ─── Sample Appointments ─────────────────────────────────────────────
-- slot_id=1 make appointment by patient Alice（booked_by = her user_id=4）
INSERT INTO appointments (slot_id, patient_id, booked_by, status) VALUES
(1, 1, 4, 'booked');

-- slot_id=4 appointment succeeded
UPDATE slots SET status='booked' WHERE id = 4;
INSERT INTO appointments (slot_id, patient_id, booked_by, status) VALUES
(4, 1, 4, 'completed');

-- ─── Sample Medical Records and Prescriptions（appointment_id=2 completed）────────────────────
INSERT INTO visit_notes (appointment_id, doctor_id, diagnosis, notes) VALUES
(2, 2, 'Mild eczema on forearm', 'Patient reports itching for 2 weeks. Recommended moisturizer and avoiding harsh soaps.');

INSERT INTO prescriptions (visit_note_id, medicine_name, dosage, instructions) VALUES
(1, 'Hydrocortisone Cream 1%', '5g tube', 'Apply thin layer to affected area twice daily for 7 days.'),
(1, 'Cetirizine 10mg',         '14 tablets', 'Take 1 tablet at night before sleep for 2 weeks.');
