<?php
/**
 * classes/Auth.php — Authentication and Access Guard
 *
 * Responsibilities:
 *   - Manage user login state (write/read/destroy $_SESSION)
 *   - Provide requireRole(): called at the top of every protected page;
 *     redirects to index.php if not logged in or role does not match
 *   - Provide isLoggedIn() and currentUser() helper methods
 *
 * Usage (at the top of each protected page):
 *   require_once '../../classes/Auth.php';
 *   Auth::requireRole('doctor');  // Only the doctor role can access this page
 *
 * Session structure:
 *   $_SESSION['user_id']  — users.id
 *   $_SESSION['role']     — admin | doctor | patient | receptionist
 *   $_SESSION['name']     — users.full_name (used for navigation bar display)
 */

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/../includes/flash.php';   // setFlash() for the "please log in" redirect

class Auth
{
    /**
     * Attempt login: validate email + password; write to session on success.
     * Returns true on success, false if credentials are wrong or account is inactive.
     */
    public static function login(string $email, string $password): bool
    {
        try {
            $user   = new User();
            $record = $user->findByEmail($email);

            if ($record === null || $record['status'] !== 'active') {
                return false;
            }

            if (!password_verify($password, $record['password_hash'])) {
                return false;
            }

            // Prevent session fixation: issue a fresh session ID on privilege change
            session_regenerate_id(true);

            $_SESSION['user_id'] = (int) $record['id'];
            $_SESSION['role']    = $record['role'];
            $_SESSION['name']    = $record['full_name'];
            return true;
        } catch (Exception $e) {
            error_log('Auth::login error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Require the current user to be logged in with a matching role.
     * $roles accepts a single string or an array of roles (supports shared pages).
     * If not satisfied, immediately calls header() + exit without executing further page logic.
     */
    public static function requireRole(string|array $roles): void
    {
        if (!self::isLoggedIn()) {
            setFlash('error', 'Please log in to continue.');
            header('Location: ' . BASE_URL . 'index.php');
            exit;
        }
        $roles = (array) $roles;
        if (!in_array($_SESSION['role'], $roles, true)) {
            header('Location: ' . BASE_URL . 'error.php?code=403&msg=Access+Denied');
            exit;
        }
    }

    /** Check whether the current request is authenticated */
    public static function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']);
    }

    /** Return the current logged-in user ID */
    public static function userId(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    /** Return the current logged-in user role */
    public static function role(): ?string
    {
        return $_SESSION['role'] ?? null;
    }
}
