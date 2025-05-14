document.addEventListener('DOMContentLoaded', () => {
    loadStudents();
    configureModal();
});

function getToken() {
    return localStorage.getItem('token');
}

function loadStudents(searchTerm = "") {
    const tableBody = document.getElementById('alunosTable');
    tableBody.innerHTML = `
        <tr>
            <td colspan="6" class="text-center">
                <div class="spinner-border text-primary" role="status"></div>
            </td>
        </tr>
    `;

    const url = `/api/students${searchTerm ? `?name=${searchTerm}` : ""}`;

    fetchWithTokenRefresh(url, {
        headers: {
            'Content-Type': 'application/json'
        }
    })
        .then(response => response.json())
        .then(students => renderStudents(students, tableBody))
        .catch(error => handleError(error, tableBody));
}

function formatCpf(cpf) {
    cpf = cpf.replace(/\D/g, '');

    return cpf.replace(/^(\d{3})(\d{3})(\d{3})(\d{2})$/, '$1.$2.$3-$4');
}

function renderStudents(students, tableBody) {
    const formatBirthdate = date => {
        const [year, month, day] = date.split('-');
        return `${day}/${month}/${year}`;
    };

    tableBody.innerHTML = "<tr><td colspan=\"5\" class=\"text-center\">Nenhum aluno encontrado.</td></tr>";

    if (students.length >= 1) {
        tableBody.innerHTML = students.map(student => `
        <tr>
            <td>${student.id}</td>
            <td>${student.name}</td>
            <td>${formatBirthdate(student.birthdate)}</td>
            <td>${formatCpf(student.cpf)}</td>
            <td>${student.email}</td>
            <td>
                <button 
                    class="btn btn-sm btn-success" 
                    data-bs-toggle="modal"
                    data-bs-target="#studentModal"
                    data-action="edit"
                    data-id="${student.id}"
                    data-bs-toggle="tooltip"
                    data-bs-placement="top"
                    title="Editar Aluno">
                    <i class="bi bi-pencil-square"></i>
                </button>
                <button 
                    class="btn btn-sm btn-danger" 
                    onclick="deleteStudent(${student.id})"
                    data-bs-toggle="tooltip"
                    data-bs-placement="top"
                    title="Excluir Aluno">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');
    }
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

function configureModal() {
    const modal = document.getElementById('studentModal');
    modal.addEventListener('show.bs.modal', async (event) => {
        const formContainer = modal.querySelector('.modal-content');
        formContainer.innerHTML = await fetchForm();

        const form = document.getElementById('studentForm');
        form.addEventListener('submit', handleSubmit);
        setupFormFieldValidation(form);

        const cpfInput = form.querySelector('#cpf');
        if (cpfInput) {
            cpfInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length <= 11) {
                    value = value.replace(/^(\d{3})(\d{3})(\d{3})(\d{2})$/, '$1.$2.$3-$4');
                    value = value.replace(/^(\d{3})(\d{3})(\d{3})$/, '$1.$2.$3');
                    value = value.replace(/^(\d{3})(\d{3})$/, '$1.$2');
                    value = value.replace(/^(\d{3})$/, '$1');
                }
                e.target.value = value;
            });
        }

        const trigger = event.relatedTarget;
        const action = trigger?.dataset.action || 'create';
        const id = trigger?.dataset.id;

        form.dataset.action = action;

        if (action === 'edit' && id) {
            try {
                const data = await fetchStudent(id);
                populateForm(data, form);
            } catch (error) {
                console.error('Erro ao buscar aluno:', error);
            }
        }
    });
}

async function fetchForm() {
    const response = await fetchWithTokenRefresh('/students/form');
    return response.text();
}

function prepareModal(action, id = null) {
    const form = document.getElementById('studentForm');

    if (!form) return;

    if (action === 'edit') {
        fetchStudent(id)
            .then(data => populateForm(data, form))
            .catch(error => console.error('Erro ao buscar aluno:', error));
    }
}

async function fetchStudent(id) {
    const response = await fetchWithTokenRefresh(`/api/students/${id}`, {
        headers: {
            'Content-Type': 'application/json'
        }
    });
    return response.json();
}

function populateForm(data, form) {
    form.querySelector('#studentId').value = data.id;
    form.querySelector('#name').value = data.name;
    form.querySelector('#birthdate').value = data.birthdate;
    form.querySelector('#cpf').value = formatCpf(data.cpf);
    form.querySelector('#email').value = data.email;
    form.querySelector('#password').value = '';
}

async function handleSubmit(e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);
    const method = form.dataset.action === 'edit' ? 'PUT' : 'POST';
    const url = `/api/students${form.dataset.action === 'edit' ? '/' + formData.get('id') : ''}`;

    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        form.querySelectorAll(':invalid').forEach(input => {
            input.classList.add('is-invalid');
        });
        return;
    }

    resetFormValidation(form);

    try {
        const response = await fetchWithTokenRefresh(url, {
            method,
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(Object.fromEntries(formData))
        });

        if (response.ok) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('studentModal'));
            modal.hide();
            loadStudents();
        } else {
            const errors = await response.json();
            displayErrors(errors, form);
        }
    } catch (error) {
        console.error('Erro ao salvar aluno:', error);
    }
}

async function deleteStudent(id) {
    if (!confirm('Tem certeza que deseja excluir este aluno?')) return;

    try {
        const response = await fetchWithTokenRefresh(`/api/students/${id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            }
        });

        if (response.ok) {
            loadStudents();
        } else {
            let errorMessage = 'Erro ao excluir aluno.';

            if (response.status === 401) {
                errorMessage = 'Não autorizado. Faça login novamente.';
            } else if (response.status === 403) {
                errorMessage = 'Você não tem permissão para excluir este aluno.';
            } else if (response.status === 422) {
                try {
                    const error = await response.json();
                    errorMessage = Object.values(error)[0] || errorMessage;
                } catch (_) {
                }
            }

            alert(errorMessage);
        }
    } catch (error) {
        console.error('Erro ao excluir:', error);
        alert('Erro inesperado. Verifique sua conexão e tente novamente.');
    }
}


function handleSearch() {
    const searchTerm = document.getElementById("searchInput").value.trim();
    loadStudents(searchTerm);
}
