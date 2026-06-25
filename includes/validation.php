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

//Sanitize input
function inputValue(string $value):string{
    return htmlspecialchars(trim($value),ENT_QUOTES,'UTF-8');
}


function validateUser(array $data):array{
    $errors=[];

    //Field check for full name
    if(empty(trim($data['full_name']??''))){
        $errors[]='Please enter your full name.';
    }

    //preg_match for email format
    $emailFormat='/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';
    if(empty($data['email']) || !preg_match($emailFormat, $data['email'])){
        $errors[]='Invalid email format. (E.g. example@domain.com)';
    }

    //Length validation
    if(!empty($data['password']) && strlen($data['password'])<8){
        $errors[]='Password must be at least 8 characters.';
    }

    return $errors;
}

//Date format
function validDate(array $data, string $field):array{
    $errors=[];
    $date=$data[$field]??'';

    $dateFormat='/^\d{4}-\d{2}-\d{2}$/';
    if(empty($date) || !preg_match($dateFormat, $date)){
        $errors[]='Invalid date format. (E.g. YYYY-MM-DD)';
    }
    if ($date<date('Y-m-d')){
        $errors[]='Appointment date cannot be in the past.';
    }
    return $errors;
}

//Diagnosis and visit notes for doctors
function validateVisitNote(array $data):array{
    $errors=[];

    $diagnosis=trim($data['diagnosis']??'');
    if(empty($diagnosis)){
        $errors[]='Please enter the diagnosis.';
    }elseif(strlen($diagnosis) > 5000){
        $errors[]='The diagnosis is too long (max: 5000 characters).';
    }

    $visit_notes=trim($data['visit_notes']??'');
    if(empty($visit_notes)){
        $errors[]='Please enter the visit notes.';
    }elseif(strlen($visit_notes)>5000){
        $errors[]='The visit notes is too long. (max: 5000 characters.)';
    }
    return $errors;
}
