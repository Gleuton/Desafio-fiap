document.addEventListener('DOMContentLoaded', () => {
    const logoutBtn = document.getElementById('logoutButton');

    if (logoutBtn) {
        logoutBtn.addEventListener('click', (e) => {
            e.preventDefault();

            // Limpa o token
            localStorage.removeItem('token');

            // Redireciona para login (ajuste a URL conforme seu app)
            window.location.href = '/login';
        });
    }
});
