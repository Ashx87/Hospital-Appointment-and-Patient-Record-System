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
 * Loaded by config/Database.php via require_once.
 */

define('BASE_URL', 'http://localhost/Hospital-Appointment-and-Patient-Record-System-main/');

define('DB_HOST', 'localhost');
// Database name created in phpMyAdmin
define('DB_NAME', 'hospital_db');
// XAMPP default user
define('DB_USER', 'root');
// XAMPP default password is empty
define('DB_PASS', '');
