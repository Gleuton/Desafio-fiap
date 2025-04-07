const studentListModal = document.getElementById('studentListModal');
studentListModal.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const courseId = button.getAttribute('data-course-id');
    loadStudentList(courseId);
});

async function deleteEnrollment(enrollmentId, courseId) {
    if (!confirm('Tem certeza que deseja remover esta matrícula?')) return;

    try {
        const response = await fetch(`/api/enrollments/${enrollmentId}`, {
            method: 'DELETE'
        });

        if (response.ok) {
            // Recarrega apenas a lista de alunos sem fechar a modal
            await loadStudentList(courseId);

            // Atualiza a lista principal de cursos (contadores)
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

    try {
        const response = await fetch(`/api/courses/${courseId}/enrollments`);
        const enrollments = await response.json();

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