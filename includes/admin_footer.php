<?php
/**
 * includes/admin_footer.php — Closes the admin console shell
 *
 * Pairs with includes/admin_header.php. Closes the .admin-content / .admin-main /
 * .admin-shell wrappers opened there, renders the small console footer, and loads
 * the shared assets/js/app.js (form validation + confirm dialogs the admin pages use).
 *
 * Required via require_once at the very bottom of every admin page.
 */
?>
        </section><!-- .admin-content -->

        <footer class="admin-footer">
            <span>&copy; <?= date('Y') ?> Hospital Appointment System</span>
            <span>TWT2231 — Web Techniques and Applications</span>
        </footer>
    </div><!-- .admin-main -->
</div><!-- .admin-shell -->

<script src="<?= BASE_URL ?>assets/js/app.js"></script>
</body>
</html>
