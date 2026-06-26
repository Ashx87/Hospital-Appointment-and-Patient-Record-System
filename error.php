<?php
/**
 * error.php — Generic Error / 404 Page
 *
 * Responsibilities:
 *   - Display a user-friendly error message without exposing server internals
 *   - Accepts URL parameters ?code=404&msg=... to specify the error type
 *   - All pages redirect here when an unrecoverable error occurs
 *
 * Usage example (from other pages):
 *   header('Location: /error.php?code=403&msg=Access+Denied');
 */

require_once __DIR__ . '/includes/header.php';

//Determine error type
$errorCode=$_GET['code']??'General Error';
$errorMessage=$_GET['msg']??'An unexpected error occurred.'

//404 Not Found
if($errorCode==='404'){
    $errorMessage="Page Not Found.";
}
?>

//Error Container
<section class="error-container">
    <div class="errror-card">
        <h1><?php echo htmlspecialchars($errorCode);?></h1>
        <p><?php echo htmlspecialchars($errorMessage);?></p>
        <hr>
        <p>Would you like to try again?</p>
        <div class="error-actions">
            <a href="index.php" class="btn">Go Home</a>
            <a href="javascript:history.back()" class="btn">Go Back</a>
        </div>
    </div>
</section>

<?php
require_once __DIR__ . '/includes/header.php';
?>
