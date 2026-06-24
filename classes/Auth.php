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

require_once __DIR__ . '/../config/Database.php';

class Auth
{
    //Log in
    public static function login(string $email, string $password): bool
    {
        //Connect database
        $pdo=Database::getInstance();
        //SQL Injection
        $stmt=$pdo->prepare("SELECT id, password, role, full_name, status FROM users WHERE email=?");
        $stmt->execute([$email]);
        $user=$stmt->fetch();

        //Password security
        if($user && password_verify($password, $user['password']) && $user['status']==='active'){
            if(session_status()===PHP_SESSION_NONE){
                session_start();
            }
            $_SESSION['user_id']=$user['id'];
            $_SESSION['role']=$user['role'];
            $_SESSION['name']=$user['full_name'];

            return true;
        }
        return false;
    }


    //Identity verification
    public static function requireRole(string|array $roles): void
    {
        if (!self::isLoggedIn()) {
            header('Location: '.BASE_URL.'index.php');
            exit;
        }
        $roles = (array) $roles;
        if (!in_array($_SESSION['role'], $roles, true)) {
            http_response_code(403);
            die("<h1>403 Forbidden</h1>
                 <p>You do not have the permission to access this page.</p>");
        }
    }

    //Session Verification
    public static function isLoggedIn():bool
    {
        if(session_status()===PHP_SESSION_NONE){
            session_start();
        }
        return !empty($_SESSION['user_id']) && $_SESSION['user_id']>0;
    }

    //Return current logged in user id
    public static function userId():?int
    {
        return $_SESSION['user_id']??null;
    }

    //Return current logged in user role
    public static function role():?string
    {
        return $_SESSION['role']??null;
    }

    //Log out
    public static function logout():void
    {
        if(session_status()===PHP_SESSION_NONE){
            session_start();
        }
        $_SESSION=[];
        session_destroy();
        header('Location: '.BASE_URL.'index.php');
        exit;
    }
}
