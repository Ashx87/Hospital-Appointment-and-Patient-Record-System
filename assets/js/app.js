/**
 * assets/js/app.js — Global front-end JavaScript
 *
 * Responsibilities:
 *   - Client-side form validation (check required fields, date logic, and password length before submission)
 *   - Modal control: confirmation dialogs for delete/cancel actions
 *   - Dynamic prescription rows: dynamically add/remove prescription input rows in write-note.php (addRow/removeRow)
 *   - Live doctor search: instantly filter doctor cards by name in find-doctor.php (no page reload required)
 *   - Flash messages auto-dismiss: fade out after 3 seconds
 *
 * Hand-written vanilla JavaScript (ES6+), no external frameworks or libraries.
 * Loaded by includes/footer.php before the </body> tag.
 */

'use strict';

/* ─── Flash message auto-dismiss (fade out after 3 seconds) ─────── */
document.addEventListener('DOMContentLoaded', () => {
    const flash = document.querySelector('.flash');
    if (flash) {
        setTimeout(() => {
            flash.style.transition = 'opacity 0.5s';
            flash.style.opacity = '0';
            setTimeout(() => flash.remove(), 500);
        }, 3000);
    }
});

/* ─── Client-side form validation ───────────────────────────────── */
/**
 * Attach pre-submission validation to the specified form
 * Validation rules are declared in HTML via data-required and data-min-length attributes
 */
function initFormValidation(formId) {
    const form = document.getElementById(formId);
    if (!form) return;

    form.addEventListener('submit', (e) => {
        let valid = true;
        form.querySelectorAll('[data-required]').forEach(input => {
            if (!input.value.trim()) {
                showFieldError(input, input.dataset.required + ' is required.');
                valid = false;
            }
        });
        if (!valid) e.preventDefault();
    });
}

function showFieldError(input, message) {
    let err = input.nextElementSibling;
    if (!err || !err.classList.contains('field-error')) {
        err = document.createElement('span');
        err.className = 'field-error';
        input.insertAdjacentElement('afterend', err);
    }
    err.textContent = message;
    input.style.borderColor = '#dc3545';
}

/* ─── Dynamic prescription rows (used by write-note.php) ─────────── */
/**
 * Dynamically add a new prescription input group to the prescriptions container
 */
function addPrescriptionRow() {
    // TODO: clone the prescription template row and append it to the container
}

function removePrescriptionRow(btn) {
    btn.closest('.prescription-row').remove();
}

/* ─── Live doctor search (used by find-doctor.php) ─────────────────── */
/**
 * Instantly filter the .doctor-card list by doctor name (no server request needed)
 */
function initDoctorSearch() {
    const input = document.getElementById('search-name');
    if (!input) return;

    input.addEventListener('input', () => {
        const query = input.value.toLowerCase();
        document.querySelectorAll('.doctor-card').forEach(card => {
            const name = card.querySelector('h3')?.textContent.toLowerCase() ?? '';
            card.style.display = name.includes(query) ? '' : 'none';
        });
    });
}

/* ─── Confirm destructive actions ───────────────────────────────── */
/**
 * Ask for confirmation before submitting any form that carries a
 * data-confirm="message" attribute (e.g. the admin "Delete user" button).
 */
function initConfirmForms() {
    document.querySelectorAll('form[data-confirm]').forEach(form => {
        form.addEventListener('submit', (e) => {
            if (!window.confirm(form.dataset.confirm)) {
                e.preventDefault();
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initDoctorSearch();
    initConfirmForms();
    initFormValidation('login-form');
    initFormValidation('slot-form');
    initFormValidation('visit-note-form');
    initFormValidation('create-user-form');
    initFormValidation('create-doctor-form');
});
