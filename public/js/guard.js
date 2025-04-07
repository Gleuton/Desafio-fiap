document.addEventListener('DOMContentLoaded', async () => {
    const token = localStorage.getItem('token');
    const logoutBtn = document.getElementById('logoutButton');

    if (!token) {
        window.location.href = '/login';
        return;
    }

    try {
        const response = await fetch('/api/auth/check', {
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });

        if (!response.ok) throw new Error();

    } catch (err) {
        localStorage.removeItem('token');
        window.location.href = '/login';
    }

    if (logoutBtn) {
        logoutBtn.addEventListener('click', async (e) => {
            e.preventDefault();

            const token = localStorage.getItem('token');

            if (token) {
                try {
                    await fetch('/api/logout', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Authorization': `Bearer ${token}`
                        }
                    });
                } catch (error) {
                    console.error('Erro ao deslogar:', error);
                }
            }

            localStorage.removeItem('token');
            window.location.href = '/login';
        });
    }
});
