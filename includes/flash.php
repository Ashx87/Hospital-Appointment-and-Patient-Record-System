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

/**
 * Write a flash message (displayed once on the next GET request, then automatically cleared)
 */
function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Read and output the flash message, then delete it from the session (ensures it is shown only once)
 * Called by includes/header.php at the top of <main>
 */
function displayFlash(): void
{
    if (!isset($_SESSION['flash'])) {
        return;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']); // Clear immediately to prevent the message from showing again on page refresh

    $type    = htmlspecialchars($flash['type']);
    $message = htmlspecialchars($flash['message']);
    echo "<div class=\"flash flash--{$type}\">{$message}</div>";
}
