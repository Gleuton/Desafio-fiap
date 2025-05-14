function resetFormValidation(form) {
    form.classList.remove('was-validated');

    form.querySelectorAll('.is-invalid').forEach(input => {
        input.classList.remove('is-invalid');
    });
}

function setupFormFieldValidation(form) {
    form.querySelectorAll('input, textarea, select').forEach(field => {
        field.addEventListener('input', function() {
            if (this.checkValidity()) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        });
    });
}

function displayErrors(response, form) {
    if (response && typeof response.error === 'object' && response.error !== null) {
        Object.entries(response.error).forEach(([field, message]) => {
            const input = form.querySelector(`[name="${field}"]`);
            if (input) {
                input.classList.add('is-invalid');
                const feedback = input.closest('.mb-3')?.querySelector('.invalid-feedback');
                if (feedback) feedback.textContent = message;
            }
        });
    }
}
