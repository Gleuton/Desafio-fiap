document.addEventListener('DOMContentLoaded', () => {
    const tableBody = document.getElementById('alunosTable');
    const studentModal = document.getElementById('studentModal');

    loadStudents(tableBody);
    configureModal(studentModal);
});

function loadStudents(tableBody) {
    tableBody.innerHTML = `
        <tr>
            <td colspan="6" class="text-center">
                <div class="spinner-border text-primary" role="status"></div>
            </td>
        </tr>
    `;

    fetch('/api/students')
        .then(response => response.json())
        .then(students => renderStudents(students, tableBody))
        .catch(error => handleError(error, tableBody));
}

function renderStudents(students, tableBody) {
    const formatBirthdate = date => {
        const [year, month, day] = date.split('-');
        return `${day}/${month}/${year}`;
    };

    tableBody.innerHTML = students.map(student => `
        <tr>
            <td>${student.id}</td>
            <td>${student.name}</td>
            <td>${formatBirthdate(student.birthdate)}</td>
            <td>${student.cpf}</td>
            <td>${student.email}</td>
            <td>
                <button 
                    class="btn btn-sm btn-success" 
                    onclick="prepareModal('edit', ${student.id})"
                >
                    <i class="bi bi-pencil-square"></i>
                </button>
                <button class="btn btn-sm btn-danger" disabled>
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

function handleError(error, tableBody) {
    console.error(error);
    tableBody.innerHTML = `
        <tr>
            <td colspan="6" class="text-danger text-center">
                Erro ao carregar alunos. Verifique o console.
            </td>
        </tr>
    `;
}

function configureModal(modal) {
    modal.addEventListener('show.bs.modal', async (event) => {
        const formContainer = modal.querySelector('.modal-content');
        formContainer.innerHTML = await fetchForm();

        const form = document.getElementById('studentForm');
        form.addEventListener('submit', handleSubmit);
    });
}

async function fetchForm() {
    const response = await fetch('/students/form');
    return response.text();
}

function prepareModal(action, id = null) {
    const modal = document.getElementById('studentModal');
    const form = document.getElementById('studentForm');

    if (!form) return;

    if (action === 'edit') {
        fetchStudent(id)
            .then(data => populateForm(data, form))
            .catch(error => console.error('Erro ao buscar aluno:', error));
    }
}

async function fetchStudent(id) {
    const response = await fetch(`/students/${id}`);
    return response.json();
}

function populateForm(data, form) {
    form.querySelector('#studentId').value = data.id;
    form.querySelector('#name').value = data.name;

}

async function handleSubmit(e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);
    const method = form.dataset.action === 'edit' ? 'PUT' : 'POST';
    const url = `/api/students${form.dataset.action === 'edit' ? '/' + formData.get('id') : ''}`;

    // Limpa erros anteriores
    resetFormValidation(form);

    // Validação Bootstrap
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
    }

    try {
        const response = await fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(Object.fromEntries(formData))
        });

        if (response.ok) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('studentModal'));
            modal.hide();
            loadStudents(document.getElementById('alunosTable'));
        } else {
            const errors = await response.json();
            displayErrors(errors, form);
        }
    } catch (error) {
        console.error('Erro ao salvar aluno:', error);
    }
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

function resetFormValidation(form) {
    form.querySelectorAll('.is-invalid').forEach(input => {
        input.classList.remove('is-invalid');
    });

    form.querySelectorAll('.invalid-feedback').forEach(feedback => {
        feedback.textContent = '';
    });
}