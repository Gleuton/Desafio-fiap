const studentListModal = document.getElementById('studentListModal');
studentListModal.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const courseId = button.getAttribute('data-course-id');
    loadStudentList(courseId);
});

async function deleteEnrollment(enrollmentId, courseId) {
    if (!confirm('Tem certeza que deseja remover esta matrícula?')) return;

    const token = localStorage.getItem('token');

    try {
        const response = await fetch(`/api/enrollments/${enrollmentId}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });

        if (response.ok) {
            await loadStudentList(courseId);
            if (typeof loadCourses === 'function') {
                loadCourses();
            }
        } else {
            const errorText = await response.text();
            alert(`Erro: ${errorText}`);
        }
    } catch (error) {
        console.error('Erro ao excluir matrícula:', error);
        alert('Erro ao excluir matrícula.');
    }
}

async function loadStudentList(courseId) {
    const list = document.getElementById('studentListContent');
    list.innerHTML = '<li class="list-group-item">Carregando...</li>';

    const token = localStorage.getItem('token');
    if (!token) {
        list.innerHTML = '<li class="list-group-item text-danger">Token não encontrado.</li>';
        return;
    }

    try {
        const response = await fetch(`/api/courses/${courseId}/enrollments`, {
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });

        const enrollments = await response.json();

        if (!response.ok) {
            throw new Error(enrollments.message || 'Erro ao buscar matrículas');
        }

        if (enrollments.length === 0) {
            list.innerHTML = '<li class="list-group-item">Nenhum aluno matriculado.</li>';
        } else {
            list.innerHTML = '';
            enrollments.forEach(enrollment => {
                const li = document.createElement('li');
                li.className = 'list-group-item d-flex justify-content-between align-items-center';
                li.innerHTML = `
                    ${enrollment.student_name}
                    <button class="btn btn-sm btn-danger" onclick="deleteEnrollment(${enrollment.id}, ${courseId})">
                        Remover
                    </button>
                `;
                list.appendChild(li);
            });
        }
    } catch (error) {
        console.error('Erro ao carregar alunos:', error);
        list.innerHTML = '<li class="list-group-item text-danger">Erro ao carregar alunos.</li>';
    }
}
