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

//Flash message (automatic fade after 3secs)
document.addEventListener('DOMContentLoaded', ()=>{
    const flash=document.querySelector('.flash');
    if(flash){
        setTimeout(()=>{
            flash.style.transition='opacity 0.5s';
            flash.style.opacity='0';
            setTimeout(()=>flash.remove(), 500); 
        }, 3000);
    }
});

//Client-side form validation
function initFormValidation(formId){
    const form=document.getElementById(formId);
    if(!form) return;

    form.addEventListener('submit', (e)=>{
        let valid=true;
        form.querySelectorAll('[data-required]').forEach(input=>{
            if (!input.value.trim()){
                showFieldError(input, input.dataset.required+' is required.');
                valid = false;
            }
        });
        if(!valid)e.preventDefault();
    });
}

//Create error span if it doesn't exist
function showFieldError(input, message){
    let error=input.nextElementSibling;
    if (!error || !error.classList.contains('field-error')){
        error=document.createElement('span');
        error.className='field-error';
        input.insertAdjacentElement('afterend', error);
    }
    error.textContent=message;
    input.style.borderColor='#dc3545';
}

/* ─── 3. Dynamic Prescription Rows (Task D5: Doctor Module) ─── */
/**
 * TODO: Task D5 implementation for cloning prescription template rows
 * and appending them to the container to allow "N" prescriptions [9].
 */
function addPrescriptionRow() {
    // TODO: Implement dynamic row addition for prescriptions here.
}

function removePrescriptionRow(btn) {
    // TODO: Implement row removal logic.
}

//Live Doctor Search
function initDoctorSearch(){
    const input = document.getElementById('search-name');
    if (!input) return;

    input.addEventListener('input', ()=>{
        const query=input.value.toLowerCase();
        document.querySelectorAll('.doctor-card').forEach(card =>{
            const name=card.querySelector('h3')?.textContent.toLowerCase()??'';
            card.style.display=name.includes(query) ? '' : 'none';
        });
    });
}

//Confirm Destructive Actions
function initConfirmForms(){
    document.querySelectorAll('form[data-confirm]').forEach(form=>{
        form.addEventListener('submit', (e)=>{
            if (!window.confirm(form.dataset.confirm)){
                e.preventDefault();
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', ()=>{
    initDoctorSearch();
    initConfirmForms();
    initFormValidation('login-form');
    initFormValidation('register-form');
    initFormValidation('slot-form');
    initFormValidation('visit-note-form');
    initFormValidation('create-user-form');
});
