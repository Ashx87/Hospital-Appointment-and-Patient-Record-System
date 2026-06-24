<?php
/**
 * includes/footer.php — Shared page footer template
 *
 * Responsibilities:
 *   - Close the <main class="container"> tag opened by header.php
 *   - Render the footer shared by all pages (copyright info, etc.)
 *   - Include the global JavaScript file assets/js/app.js
 *   - Close the <body> and <html> tags to ensure complete HTML structure
 *
 * Required via require_once at the very bottom of all pages; used together with includes/header.php.
 */
?>

</main>
<footer class="site-footer">
    <div class="container">
        <p>&copy;<?= date('Y')?> Hospital Appointment System — TWT6223 Group Project</p>
    </div>
</footer>

<script src="<?=BASE_URL ?>assets/js/app.js"></script>
</body>
</html>
