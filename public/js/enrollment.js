document.addEventListener('DOMContentLoaded', () => {
    const token = localStorage.getItem('token');
    if (!token) {
        window.location.href = '/login';
    }

    const enrollmentModal = document.getElementById('enrollmentModal');
    configureEnrollmentModal(enrollmentModal);
});

function getToken() {
    return localStorage.getItem('token');
}

async function fetchEnrollmentForm() {
    try {
        const response = await fetch('/enrollments/form', {
            headers: {
                'Authorization': `Bearer ${getToken()}`
            }
        });
        return await response.text();
    } catch (error) {
        console.error('Erro ao carregar formulário:', error);
        return '<p class="text-danger">Erro ao carregar formulário.</p>';
    }
}

async function initializeAutocomplete() {
    const input = document.getElementById('studentSearch');
    const list = document.getElementById('studentList');

    if (!input || !list) return;

    input.addEventListener('input', async (e) => {
        const searchTerm = e.target.value.trim();
        if (!searchTerm) return;

        try {
            const response = await fetch(`/api/students?name=${encodeURIComponent(searchTerm)}&limit=10`, {
                headers: {
                    'Authorization': `Bearer ${getToken()}`
                }
            });
            const students = await response.json();

            list.innerHTML = '';

            students.forEach(student => {
                const li = document.createElement('li');
                li.className = 'list-group-item list-group-item-action';
                li.textContent = student.name;
                li.dataset.studentId = student.id;
                li.addEventListener('click', selectStudent);
                list.appendChild(li);
            });
        } catch (error) {
            console.error('Erro ao buscar alunos:', error);
        }
    });
}

function selectStudent(event) {
    document.getElementById('studentId').value = event.target.dataset.studentId;
    document.getElementById('studentName').textContent = event.target.textContent;
    document.getElementById('studentList').innerHTML = '';
}

async function submitEnrollment() {
    const courseId = document.getElementById('courseId')?.value;
    const studentId = document.getElementById('studentId')?.value;
    const messageBox = document.getElementById('formMessages');

    if (messageBox) {
        messageBox.classList.add('d-none');
        messageBox.textContent = '';
        messageBox.classList.remove('alert-danger', 'alert-success');
    }

    if (!courseId || !studentId) {
        if (messageBox) {
            messageBox.classList.remove('d-none');
            messageBox.classList.add('alert', 'alert-danger');
            messageBox.textContent = 'Selecione uma turma e um aluno!';
        }
        return;
    }

    try {
        const response = await fetch('/api/enrollments', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${getToken()}`
            },
            body: JSON.stringify({ course_id: courseId, user_id: studentId })
        });

        if (response.ok) {
            if (messageBox) {
                messageBox.classList.remove('d-none');
                messageBox.classList.add('alert', 'alert-success');
                messageBox.textContent = 'Matrícula realizada com sucesso!';
            }

            setTimeout(() => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('enrollmentModal'));
                modal.hide();
                loadCourses(); // função global
            }, 250);
        } else {
            const errorData = await response.json();
            if (messageBox) {
                messageBox.classList.remove('d-none');
                messageBox.classList.add('alert', 'alert-danger');
                const messages = Object.values(errorData).join(' ');
                messageBox.textContent = messages || 'Erro ao realizar matrícula.';
            }
        }
    } catch (error) {
        console.error('Erro ao salvar matrícula:', error);
        if (messageBox) {
            messageBox.classList.remove('d-none');
            messageBox.classList.add('alert', 'alert-danger');
            messageBox.textContent = 'Erro inesperado ao tentar salvar matrícula.';
        }
    }
}

function configureEnrollmentModal(modalElement) {
    modalElement.addEventListener('show.bs.modal', async (event) => {
        const body = modalElement.querySelector('.modal-body');
        if (!body) {
            console.error("modal-body não encontrada!");
            return;
        }

        body.innerHTML = await fetchEnrollmentForm();

        const form = modalElement.querySelector('#enrollmentForm');
        if (!form) {
            console.error("Formulário de matrícula não encontrado!");
            return;
        }

        const button = event.relatedTarget;
        const courseId = button?.dataset?.courseId;
        if (courseId) {
            const hiddenCourseInput = form.querySelector('#courseId');
            if (hiddenCourseInput) {
                hiddenCourseInput.value = courseId;
            }
        }

        initializeAutocomplete();

        form.addEventListener('submit', (e) => {
            e.preventDefault();
            submitEnrollment();
        });
    });
}
