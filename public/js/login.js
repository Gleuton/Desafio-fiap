document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('loginForm');
    const loginError = document.getElementById('loginError');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        loginError.textContent = ''; // limpa mensagem anterior
        await login(loginError);
    });
});

async function login(loginError) {
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    try {
        const response = await fetch('/api/login', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({email, password})
        });

        if (response.ok) {
            const data = await response.json();
            localStorage.setItem('token', data.token);
            window.location.href = '/';
        } else {
            const errorData = await response.json();
            loginError.textContent = errorData.error || 'Erro ao fazer login.';
        }
    } catch (error) {
        console.error('Erro:', error);
        loginError.textContent = 'Erro de conexão com o servidor.';
    }
}
