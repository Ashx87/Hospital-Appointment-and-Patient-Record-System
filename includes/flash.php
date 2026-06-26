<?php
/**
 * includes/flash.php — Session flash message utility
 *
 * Responsibilities:
 *   - Implement one-time user feedback for the PRG (Post/Redirect/Get) pattern
 *   - setFlash(): write a message after POST processing succeeds/fails, then immediately redirect
 *   - displayFlash(): read the message when rendering a GET request and clear it immediately, ensuring it is shown only once
 *   - Supports two types: 'success' (green) and 'error' (red)
 *
 * Session structure (no DB reads/writes — pure session operations):
 *   $_SESSION['flash'] = ['type' => 'success|error', 'message' => 'message text']
 *
 * Callers:
 *   includes/header.php (around line 30, calls displayFlash() to render the message)
 *   All POST-handling logic under pages/ (calls setFlash() to write the message before redirecting)
 *
 * Usage example (in POST handling in each page):
 *   setFlash('success', 'Appointment booked successfully!');
 *   header('Location: my-appointments.php');
 *   exit;
 */

//Display message
function displayFlash():void{
    if (isset($_SESSION['flash']) && is_array($_SESSION['flash'])){
        foreach ($_SESSION['flash'] as $type => $message) {
            echo "<div class='alert alert-{$type}'>" . htmlspecialchars($message) . "</div>";
        }
        unset($_SESSION['flash']);
    }
}

//Store the message in the session
function setFlash(string $type, string $message):void{
    $_SESSION['flash'][$type]=$message;
}
