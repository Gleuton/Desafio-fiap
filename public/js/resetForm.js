function resetFormValidation(form) {
    form.querySelectorAll('.is-invalid').forEach(input => {
        input.classList.remove('is-invalid');
    });

    form.querySelectorAll('.invalid-feedback').forEach(feedback => {
        feedback.textContent = '';
    });
}