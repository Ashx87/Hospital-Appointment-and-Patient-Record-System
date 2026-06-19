<?php
/**
 * includes/validation.php — Input validation utility functions
 *
 * Responsibilities:
 *   - Validate all user input at the system boundary (form POST entry points), failing fast on errors
 *   - Provide reusable validation functions to avoid duplicating validation logic across pages
 *   - Return an array of error messages: empty array = validation passed; non-empty = errors found, the calling page decides how to display them
 *   - Does not access the database directly; performs format and business rule validation only
 *
 * Required via require_once at the top of all POST-handling logic under pages/;
 * input must pass validation before calling any class methods.
 *
 * Data format conventions:
 *   - Date: YYYY-MM-DD (MySQL DATE format)
 *   - Time: HH:MM (MySQL TIME format)
 */

/**
 * Sanitize a single field: trim leading/trailing whitespace and escape HTML special characters (XSS protection at the display layer)
 * SQL injection protection is handled by PDO prepared statements and does not need to be handled here
 */
function sanitize(string $value): string
{
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate the user registration/edit form
 *
 * @param array $data  Raw data from $_POST
 * @return string[]    List of error messages (empty = passed)
 */
function validateUser(array $data): array
{
    $errors = [];

    if (empty(trim($data['full_name'] ?? ''))) {
        $errors[] = 'Full name is required.';
    }

    if (empty(trim($data['email'] ?? '')) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email address is required.';
    }

    // Only validate length when a new password is submitted (can be left blank during editing to mean no change)
    if (!empty($data['password']) && strlen($data['password']) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }

    return $errors;
}

/**
 * Validate the time slot form (used when a doctor publishes a time slot)
 * Date format: YYYY-MM-DD; time format: HH:MM
 *
 * @param array $data  Raw data from $_POST
 * @return string[]    List of error messages (empty = passed)
 */
function validateSlot(array $data): array
{
    $errors = [];

    if (empty($data['slot_date']) || !strtotime($data['slot_date'])) {
        $errors[] = 'A valid date is required (YYYY-MM-DD).';
    } elseif ($data['slot_date'] < date('Y-m-d')) {
        $errors[] = 'Slot date cannot be in the past.';
    }

    if (empty($data['start_time']) || empty($data['end_time'])) {
        $errors[] = 'Start time and end time are required.';
    } elseif ($data['start_time'] >= $data['end_time']) {
        $errors[] = 'End time must be after start time.';
    }

    return $errors;
}

/**
 * Validate the visit note form (used when a doctor writes a diagnosis)
 *
 * @param array $data  Raw data from $_POST
 * @return string[]    List of error messages (empty = passed)
 */
function validateVisitNote(array $data): array
{
    $errors = [];

    if (empty(trim($data['diagnosis'] ?? ''))) {
        $errors[] = 'Diagnosis is required.';
    }

    return $errors;
}
