document.addEventListener('DOMContentLoaded', () => {
    const tableBody = document.getElementById('alunosTable');
    tableBody.innerHTML = `
        <tr>
            <td colspan="6" class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Carregando...</span>
                </div>
            </td>
        </tr>
    `;

    fetch('/api/students')
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro ao carregar alunos');
            }
            return response.json();
        })
        .then(students => {
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
                        <button class="btn btn-sm btn-success" disabled>
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" disabled>
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        })
        .catch(error => {
            console.error(error);
            tableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-danger text-center">
                        Erro ao carregar alunos. Verifique o console.
                    </td>
                </tr>
            `;
        });
});