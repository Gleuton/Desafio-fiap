document.addEventListener('DOMContentLoaded', async () => {
    const token = localStorage.getItem('token');
    const refreshToken = localStorage.getItem('refresh_token');
    const logoutBtn = document.getElementById('logoutButton');

    if (!token) {
        // não use fetchWithTokenRefresh aqui para evitar dependência circular
        if (refreshToken) {
            try {
                const refreshResponse = await fetch('/api/refresh', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ refresh_token: refreshToken })
                });

                if (refreshResponse.ok) {
                    const refreshData = await refreshResponse.json();
                    localStorage.setItem('token', refreshData.token);
                    localStorage.setItem('refresh_token', refreshData.refresh_token);
                    return;
                }

                localStorage.removeItem('refresh_token');
                window.location.href = '/login';
                return;
            } catch (refreshErr) {
                console.error('Erro ao renovar token:', refreshErr);
                localStorage.removeItem('refresh_token');
                window.location.href = '/login';
                return;
            }
        } else {
            window.location.href = '/login';
            return;
        }
    }

    try {
        const response = await fetchWithTokenRefresh('/api/auth/check');

        if (!response.ok) throw new Error();

    } catch (err) {
        console.error('Erro ao verificar token:', err);

        // não use fetchWithTokenRefresh aqui para evitar dependência circular
        if (refreshToken) {
            try {
                const refreshResponse = await fetch('/api/refresh', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ refresh_token: refreshToken })
                });

                if (refreshResponse.ok) {
                    const refreshData = await refreshResponse.json();
                    localStorage.setItem('token', refreshData.token);
                    localStorage.setItem('refresh_token', refreshData.refresh_token);
                    return;
                }
            } catch (refreshErr) {
                console.error('Erro ao renovar token:', refreshErr);
            }
        }

        localStorage.removeItem('token');
        localStorage.removeItem('refresh_token');
        window.location.href = '/login';
    }

    if (logoutBtn) {
        logoutBtn.addEventListener('click', async (e) => {
            e.preventDefault();

            const token = localStorage.getItem('token');

            if (token) {
                try {
                    await fetchWithTokenRefresh('/api/logout', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        }
                    });
                } catch (error) {
                    console.error('Erro ao deslogar:', error);
                }
            }

            localStorage.removeItem('token');
            localStorage.removeItem('refresh_token');
            window.location.href = '/login';
        });
    }
});
