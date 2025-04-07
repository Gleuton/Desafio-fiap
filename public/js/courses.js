document.addEventListener("DOMContentLoaded", () => {
    const token = localStorage.getItem('token');
    if (!token) {
        window.location.href = '/login';
    }
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

    const token = localStorage.getItem('token');
    const url = `/api/courses?page=${page}`;

    fetch(url, {
        headers: {
            'Authorization': `Bearer ${token}`,
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
    const token = localStorage.getItem('token');
    const response = await fetch(`/api/courses/${id}`, {
        headers: {
            'Authorization': `Bearer ${token}`,
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
    const token = localStorage.getItem('token');
    const response = await fetch('/courses/form', {
        headers: {
            'Authorization': `Bearer ${token}`
        }
    });
    return response.text();
}

async function handleSubmit(e) {
    e.preventDefault();

    const token = localStorage.getItem('token');
    const form = e.target;
    const formData = new FormData(form);
    const method = form.dataset.action === 'edit' ? 'PUT' : 'POST';
    const url = `/api/courses${form.dataset.action === 'edit' ? '/' + formData.get('id') : ''}`;

    resetFormValidation(form);

    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
    }

    try {
        const response = await fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
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

function deleteCourse(id) {
    const token = localStorage.getItem('token');
    if (confirm("Tem certeza que deseja excluir esta turma?")) {
        fetch(`/api/courses/${id}`, {
            method: "DELETE",
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            }
        })
            .then(() => loadCourses())
            .catch(error => console.error(error));
    }
}
