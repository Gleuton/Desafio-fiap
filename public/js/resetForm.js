function resetFormValidation(form) {
    form.querySelectorAll('.is-invalid').forEach(input => {
        input.classList.remove('is-invalid');
    });

    form.querySelectorAll('.invalid-feedback').forEach(feedback => {
        feedback.textContent = '';
    });
}

function displayErrors(errors, form) {
    Object.entries(errors).forEach(([field, message]) => {
        const input = form.querySelector(`[name="${field}"]`);
        if (input) {
            input.classList.add('is-invalid');
            const feedback = input.closest('.mb-3').querySelector('.invalid-feedback');
            if (feedback) feedback.textContent = message;
        }
    });
}