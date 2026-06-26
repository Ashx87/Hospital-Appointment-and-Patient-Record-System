<section class="error-container">
    <div class="error-card"> <h1><?php echo htmlspecialchars($errorCode); ?></h1>
        <p><?php echo htmlspecialchars($errorMessage); ?></p>
        <hr>
        <p>Would you like to try again?</p>
        <div class="error-actions">
            <a href="index.php" class="btn">Go Home</a>
            <a href="javascript:history.back()" class="btn">Go Back</a>
        </div>
    </div>
</section>
