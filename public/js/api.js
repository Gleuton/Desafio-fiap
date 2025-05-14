
/**
 * Wrapper for fetch that handles token refresh automatically
 * @param {string} url - The URL to fetch
 * @param {Object} options - Fetch options
 * @returns {Promise} - The fetch promise
 */
async function fetchWithTokenRefresh(url, options = {}) {
    if (!options.headers) {
        options.headers = {};
    }

    if (!options.headers['Authorization'] && localStorage.getItem('token')) {
        options.headers['Authorization'] = `Bearer ${localStorage.getItem('token')}`;
    }

    try {
        const response = await fetch(url, options);

        if (response.ok) {
            return response;
        }

        if (response.status === 401) {
            const refreshToken = localStorage.getItem('refresh_token');

            if (!refreshToken) {
                localStorage.removeItem('token');
                window.location.href = '/login';
                return response;
            }

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

                    options.headers['Authorization'] = `Bearer ${refreshData.token}`;
                    return fetch(url, options);
                }

                localStorage.removeItem('token');
                localStorage.removeItem('refresh_token');
                window.location.href = '/login';
                return response;
            } catch (refreshError) {
                console.error('Error refreshing token:', refreshError);
                localStorage.removeItem('token');
                localStorage.removeItem('refresh_token');
                window.location.href = '/login';
                return response;
            }
        }

        return response;
    } catch (error) {
        console.error('Fetch error:', error);
        throw error;
    }
}

window.fetchWithTokenRefresh = fetchWithTokenRefresh;
