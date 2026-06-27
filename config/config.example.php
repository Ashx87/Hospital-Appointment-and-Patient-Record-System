<?php
/**
 * config/config.example.php — Database configuration template (safe to commit, no real credentials)
 *
 * Responsibilities:
 *   - Provides a configuration template that can be safely committed to Git
 *   - Copy to config.php and fill in real values before use
 *   - config.php must be added to .gitignore and never committed
 *
 * Usage (run from the project root):
 *   cp config/config.example.php config/config.php
 *   Then edit the values in config.php
 *
 * Loaded by classes/Database.php via require_once.
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'hospital_db');   // Database name created in phpMyAdmin
define('DB_USER', 'root');          // XAMPP default user
define('DB_PASS', '');              // XAMPP default password is empty

define('BASE_URL', 'http://localhost/Hospital-Appointment-and-Patient-Record-System/');  // Change to the actual subdirectory path
