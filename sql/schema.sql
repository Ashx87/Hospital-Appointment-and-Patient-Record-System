-- sql/schema.sql — Database table creation script
--
-- Responsibilities:
--   - Define the complete database structure for the Hospital Appointment System (7 tables)
--   - Importing this file into phpMyAdmin initialises the database
--   - Includes foreign key constraints to ensure referential integrity and necessary indexes to improve query performance
--
-- Usage:
--   1. Create the database hospital_db in phpMyAdmin
--   2. Select that database, click "Import", upload this file, and execute
--
-- Table dependency order (foreign key order):
--   users → patients, doctors
--   doctors → slots
--   slots + patients + users → appointments
--   appointments + doctors → visit_notes
--   visit_notes → prescriptions

CREATE DATABASE IF NOT EXISTS hospital_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE hospital_db;

-- Disable foreign key checks (allows drop-and-recreate, for easy reset)
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS prescriptions;
DROP TABLE IF EXISTS visit_notes;
DROP TABLE IF EXISTS appointments;
DROP TABLE IF EXISTS slots;
DROP TABLE IF EXISTS doctors;
DROP TABLE IF EXISTS patients;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

-- ─── 1. users (shared login table) ──────────────────────────────────
-- All roles (admin/doctor/patient/receptionist) share this table for login
CREATE TABLE users (
    id            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    role          ENUM('admin','doctor','patient','receptionist') NOT NULL,
    email         VARCHAR(255)     NOT NULL,
    password_hash VARCHAR(255)     NOT NULL,
    full_name     VARCHAR(100)     NOT NULL,
    phone         VARCHAR(20)      NULL,
    status        ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── 2. patients (patient profile, 1:1 extension of users) ──────────
CREATE TABLE patients (
    id            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    user_id       INT UNSIGNED     NOT NULL,
    date_of_birth DATE             NULL,
    gender        ENUM('male','female','other') NULL,
    blood_type    VARCHAR(5)       NULL,
    allergies     TEXT             NULL,
    address       TEXT             NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_patients_user (user_id),
    CONSTRAINT fk_patients_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── 3. doctors (doctor profile, 1:1 extension of users) ────────────
CREATE TABLE doctors (
    id             INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    user_id        INT UNSIGNED     NOT NULL,
    specialization VARCHAR(100)     NULL,
    department     VARCHAR(100)     NULL,
    bio            TEXT             NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_doctors_user (user_id),
    CONSTRAINT fk_doctors_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    KEY idx_doctors_dept (department)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── 4. slots (doctor time slots) ───────────────────────────────────
CREATE TABLE slots (
    id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    doctor_id   INT UNSIGNED  NOT NULL,
    slot_date   DATE          NOT NULL,
    start_time  TIME          NOT NULL,
    end_time    TIME          NOT NULL,
    status      ENUM('open','booked','blocked') NOT NULL DEFAULT 'open',
    PRIMARY KEY (id),
    CONSTRAINT fk_slots_doctor FOREIGN KEY (doctor_id) REFERENCES doctors (id) ON DELETE CASCADE,
    KEY idx_slots_doctor_date (doctor_id, slot_date, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── 5. appointments ─────────────────────────────────────────────────
-- slot_id UNIQUE ensures each time slot has only one appointment
-- booked_by can be the user_id of a patient or a receptionist
CREATE TABLE appointments (
    id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    slot_id     INT UNSIGNED  NOT NULL,
    patient_id  INT UNSIGNED  NOT NULL,
    booked_by   INT UNSIGNED  NOT NULL,
    status      ENUM('booked','completed','cancelled') NOT NULL DEFAULT 'booked',
    created_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_appt_slot (slot_id),
    CONSTRAINT fk_appt_slot    FOREIGN KEY (slot_id)    REFERENCES slots    (id),
    CONSTRAINT fk_appt_patient FOREIGN KEY (patient_id) REFERENCES patients (id),
    CONSTRAINT fk_appt_booked  FOREIGN KEY (booked_by)  REFERENCES users    (id),
    KEY idx_appt_patient (patient_id),
    KEY idx_appt_status  (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── 6. visit_notes ──────────────────────────────────────────────────
-- appointment_id UNIQUE ensures each appointment has only one visit note
CREATE TABLE visit_notes (
    id             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    appointment_id INT UNSIGNED  NOT NULL,
    doctor_id      INT UNSIGNED  NOT NULL,
    diagnosis      TEXT          NOT NULL,
    notes          TEXT          NULL,
    visited_at     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_vn_appointment (appointment_id),
    CONSTRAINT fk_vn_appointment FOREIGN KEY (appointment_id) REFERENCES appointments (id),
    CONSTRAINT fk_vn_doctor      FOREIGN KEY (doctor_id)      REFERENCES doctors      (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── 7. prescriptions ────────────────────────────────────────────────
-- One visit_note can have multiple prescriptions (one-to-many)
CREATE TABLE prescriptions (
    id             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    visit_note_id  INT UNSIGNED  NOT NULL,
    medicine_name  VARCHAR(200)  NOT NULL,
    dosage         VARCHAR(100)  NULL,
    instructions   TEXT          NULL,
    created_at     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_presc_vn FOREIGN KEY (visit_note_id) REFERENCES visit_notes (id) ON DELETE CASCADE,
    KEY idx_presc_vn (visit_note_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
