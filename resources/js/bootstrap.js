import axios from 'axios';

// Create axios instance with default config
const instance = axios.create({
    baseURL: '/api',
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        'Content-Type': 'application/json'
    },
    withCredentials: true // Important for sending cookies with CORS
});

// Set axios as a global variable
window.axios = instance;

// Add CSRF token to all requests
const csrfToken = document.head.querySelector('meta[name="csrf-token"]');
if (csrfToken) {
    instance.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.content;
}

// Add request interceptor to include auth token
instance.interceptors.request.use(
    config => {
        // Skip adding auth header for login/register routes
        const publicEndpoints = ['/login', '/register', '/sanctum/csrf-cookie'];
        const isPublicEndpoint = publicEndpoints.some(endpoint => config.url.includes(endpoint));

        if (!isPublicEndpoint) {
            // Get token from localStorage
            const token = localStorage.getItem('auth_token') ||
                         localStorage.getItem('token') ||
                         sessionStorage.getItem('auth_token');

            if (token) {
                config.headers['Authorization'] = `Bearer ${token}`;
            }
        }

        return config;
    },
    error => {
        return Promise.reject(error);
    }
);

// Add response interceptor to handle 401 Unauthorized
instance.interceptors.response.use(
    response => response,
    error => {
        if (error.response && error.response.status === 401) {
            console.error('API request failed with 401:', error.config.url);

            // Only handle authentication for specific endpoints
            const protectedEndpoints = [
                '/api/user',
                '/api/auth/user',
                '/api/me'
            ];

            const isProtectedEndpoint = protectedEndpoints.some(endpoint =>
                error.config.url.includes(endpoint)
            );

            // Only redirect to login if it's a protected endpoint or specifically for widgets
            const isWidgetsEndpoint = error.config.url.includes('/api/dashboard/widgets');

            if (isProtectedEndpoint || isWidgetsEndpoint) {
                console.log('Authentication required, redirecting to login');
                if (window.location.pathname !== '/login') {
                    localStorage.removeItem('auth_token');
                    window.location.href = '/login';
                }
            }
        }
        return Promise.reject(error);
    }
);

// Helper function to set auth token
window.setAuthToken = (token) => {
    if (token) {
        localStorage.setItem('auth_token', token);
        instance.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    } else {
        localStorage.removeItem('auth_token');
        delete instance.defaults.headers.common['Authorization'];
    }
};

// Initialize auth token if exists
const savedToken = localStorage.getItem('auth_token');
if (savedToken) {
    window.setAuthToken(savedToken);
}
