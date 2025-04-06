function loadCourses(page = 1) {
    const tableBody = document.getElementById("coursesTable");
    const pagination = document.getElementById("pagination");

    tableBody.innerHTML = `
        <tr>
            <td colspan="5" class="text-center">
                <div class="spinner-border text-primary" role="status"></div>
            </td>
        </tr>
    `;

    const url = `/api/courses?page=${page}`;
    fetch(url)
        .then(response => response.json())
        .then(data => {
            const courses = data.courses;
            let html = "";

            if (courses.length === 0) {
                html = `<tr><td colspan="5" class="text-center">Nenhuma turma encontrada.</td></tr>`;
            } else {
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
                                    data-bs-target="#courseModal"
                                    data-action="edit"
                                    data-id="${course.id}"
                                >
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" 
                                        onclick="deleteCourse(${course.id})">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });
            }

            tableBody.innerHTML = html;
            updatePagination(data.totalPages, page);
        })
        .catch(error => console.error(error));
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

function prepareModal(action, data = null) {
    const modalTitle = document.getElementById("modalLabel");
    const form = document.getElementById("classForm");

    if (action === "create") {
        modalTitle.textContent = "Nova Turma";
        form.reset();
        document.getElementById("classId").value = "";
    } else if (action === "edit") {
        modalTitle.textContent = "Editar Turma";
        document.getElementById("classId").value = data.id;
        document.getElementById("className").value = data.nome;
        document.getElementById("classDescription").value = data.descricao;
    }
}

function saveCourse() {
    const form = document.getElementById("classForm");
    const formData = {
        id: form.classId.value,
        name: form.className.value,
        description: form.classDescription.value
    };

    if (formData.nome.length < 3) {
        alert("O nome da turma deve ter pelo menos 3 caracteres (RN02).");
        return;
    }

    const method = formData.id ? "PUT" : "POST";
    const url = formData.id ? `/api/courses/${formData.id}` : "/api/courses";

    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(formData)
    })
        .then(response => response.json())
        .then(() => {
            loadCourses();
            $('#classModal').modal('hide');
        })
        .catch(error => console.error(error));
}

function deleteCourse(id) {
    if (confirm("Tem certeza que deseja excluir esta turma?")) {
        fetch(`/api/courses/${id}`, {
            method: "DELETE"
        })
            .then(() => loadCourses())
            .catch(error => console.error(error));
    }
}

document.addEventListener("DOMContentLoaded", () => {
    loadCourses();
});