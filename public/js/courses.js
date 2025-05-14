document.addEventListener("DOMContentLoaded", () => {
    configureModal();
    loadCourses();
});

function loadCourses(page = 1) {
    const tableBody = document.getElementById('coursesTable');
    tableBody.innerHTML = `
        <tr>
            <td colspan="5" class="text-center">
                <div class="spinner-border text-primary" role="status"></div>
            </td>
        </tr>
    `;

    const url = `/api/courses?page=${page}`;

    fetchWithTokenRefresh(url, {
        headers: {
            'Content-Type': 'application/json'
        }
    })
        .then(response => response.json())
        .then(data => renderCourses(data, page, tableBody))
        .catch(error => handleError(error, tableBody));
}

function handleError(error, tableBody) {
    console.error(error);
    tableBody.innerHTML = `
        <tr>
            <td colspan="6" class="text-danger text-center">
                Erro ao carregar Turmas. Verifique o console.
            </td>
        </tr>
    `;
}

function renderCourses(data, page, tableBody) {
    const courses = data.courses;
    let html = "<tr><td colspan=\"5\" class=\"text-center\">Nenhuma turma encontrada.</td></tr>";

    if (courses.length >= 1) {
        html = '';
        courses.forEach(course => {
            html += `
                <tr>
                    <td>${course.id}</td>
                    <td>${course.name}</td>
                    <td>${course.description || '-'}</td>
                    <td>${course.students}</td>
                    <td>
                        <button 
                            class="btn btn-sm btn-success" 
                            data-bs-toggle="modal"
                            data-bs-target="#enrollmentModal"
                            data-action="enroll"
                            data-course-id="${course.id}"
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                            title="Matricular Alunos">
                            <i class="bi bi-person-plus-fill"></i>
                        </button>                                
                        <button 
                            class="btn btn-sm btn-info" 
                            data-bs-toggle="modal"
                            data-bs-target="#studentListModal"
                            data-course-id="${course.id}"
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                            title="Lista de Alunos Matrículados">
                            <i class="bi bi-people"></i>
                        </button>
                        <button 
                            class="btn btn-sm btn-success" 
                            data-bs-toggle="modal"
                            data-bs-target="#courseModal"
                            data-action="edit"
                            data-id="${course.id}"
                            onclick="prepareModal(${course.id})"
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                            title="Editar Turma">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" 
                                onclick="deleteCourse(${course.id})"
                                data-bs-toggle="tooltip"
                                data-bs-placement="top"
                                title="Excluir Turma">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
    }

    tableBody.innerHTML = html;
    updatePagination(data.totalPages, page);
}

function updatePagination(totalPages, currentPage) {
    const pagination = document.getElementById("pagination");
    let html = "";

    for (let page = 1; page <= totalPages; page++) {
        html += `
            <li class="page-item ${page === currentPage ? 'active' : ''}">
                <a class="page-link" 
                   href="javascript:void(0)" 
                   onclick="loadCourses(${page})">${page}</a>
            </li>
        `;
    }

    pagination.innerHTML = html;
}

function prepareModal(action, id = null) {
    const form = document.getElementById("classForm");

    if (!form) return;

    if (action === "edit") {
        fetchCourse(id)
            .then(data => populateForm(data, form))
            .catch(error => console.error('Erro ao buscar turma:', error));
    }
}

async function fetchCourse(id) {
    const response = await fetchWithTokenRefresh(`/api/courses/${id}`, {
        headers: {
            'Content-Type': 'application/json'
        }
    });
    return response.json();
}

function populateForm(data, form) {
    form.querySelector('#classId').value = data.id;
    form.querySelector('#className').value = data.name;
    form.querySelector('#classDescription').value = data.description;
}

function configureModal() {
    const modal = document.getElementById('courseModal');
    modal.addEventListener('show.bs.modal', async (event) => {
        const formContainer = modal.querySelector('.modal-content');
        formContainer.innerHTML = await fetchForm();

        const form = document.getElementById('classForm');
        form.addEventListener('submit', handleSubmit);
        setupFormFieldValidation(form);

        const trigger = event.relatedTarget;
        const action = trigger?.dataset.action || 'create';
        const id = trigger?.dataset.id;

        form.dataset.action = action;

        if (action === 'edit' && id) {
            try {
                const data = await fetchCourse(id);
                populateForm(data, form);
            } catch (error) {
                console.error('Erro ao buscar Turma:', error);
            }
        }
    });
}

async function fetchForm() {
    const response = await fetchWithTokenRefresh('/courses/form');
    return response.text();
}

async function handleSubmit(e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);
    const method = form.dataset.action === 'edit' ? 'PUT' : 'POST';
    const url = `/api/courses${form.dataset.action === 'edit' ? '/' + formData.get('id') : ''}`;

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
            const modal = bootstrap.Modal.getInstance(document.getElementById('courseModal'));
            modal.hide();
            loadCourses();
        } else {
            const errors = await response.json();
            displayErrors(errors, form);
        }
    } catch (error) {
        console.error('Erro ao salvar turma:', error);
    }
}

async function deleteCourse(id) {
    if (!confirm("Tem certeza que deseja excluir esta turma?")) return;

    try {
        const response = await fetchWithTokenRefresh(`/api/courses/${id}`, {
            method: "DELETE",
            headers: {
                'Content-Type': 'application/json'
            }
        });

        if (response.ok) {
            loadCourses();
        } else {
            let errorMessage = 'Erro ao excluir turma.';

            if (response.status === 401) {
                errorMessage = 'Não autorizado. Faça login novamente.';
            } else if (response.status === 403) {
                errorMessage = 'Você não tem permissão para excluir esta turma.';
            } else if (response.status === 422) {
                try {
                    const errorData = await response.json();
                    errorMessage = Object.values(errorData)[0] || errorMessage;
                } catch (_) {
                }
            }

            alert(errorMessage);
        }
    } catch (error) {
        console.error('Erro inesperado:', error);
        alert('Erro inesperado. Verifique sua conexão e tente novamente.');
    }
}
