const studentListModal = document.getElementById('studentListModal');
studentListModal.addEventListener('show.bs.modal', async function (event) {
    const button = event.relatedTarget;
    const courseId = button.getAttribute('data-course-id');
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
});

async function deleteEnrollment(enrollmentId, courseId) {
    if (!confirm('Tem certeza que deseja remover esta matrícula?')) return;

    try {
        const response = await fetch(`/api/enrollments/${enrollmentId}`, {
            method: 'DELETE'
        });

        if (response.ok) {
            // Recarrega a lista de alunos do curso
            const modal = bootstrap.Modal.getInstance(document.getElementById('studentListModal'));
            modal.hide();
            const reopen = new bootstrap.Modal(document.getElementById('studentListModal'));
            reopen.show(); // Reabre para atualizar (pode ser refinado com reload direto se preferir)
        } else {
            const errorText = await response.text();
            alert(`Erro: ${errorText}`);
        }
    } catch (error) {
        console.error('Erro ao excluir matrícula:', error);
        alert('Erro ao excluir matrícula.');
    }
}
