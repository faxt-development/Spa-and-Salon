import axios from 'axios';

console.log('Bootstrap.js is loading...');

// Add session API token if available in the page
const getSessionApiToken = () => {
    console.log(window.apiToken || 'no token in the window');
    // This function attempts to get the API token from the session
    // It will be populated by blade templates using {{ session()->get('api_token') }}
    return window.apiToken || null;
};


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
console.log('Axios instance created and set to window.axios');

/**
 * Example fetch request we need to match with axios:
 * fetch('/api/dashboard/staff/stats', {
 *          headers: {
 *              'Accept': 'application/json',
 *              'X-Requested-With': 'XMLHttpRequest',
 *              'X-CSRF-TOKEN': '{{ csrf_token() }}',
 *              'Authorization': 'Bearer {{ session()->get('api_token') }}'
 *          },
 *          credentials: 'same-origin'
 *      })
 *
 * Note: To use the session API token in JavaScript, add this to your blade templates:
 * <script>
 *     window.apiToken = '{{ session()->get('api_token') }}';
 * </script>
 */

// Add CSRF token to all requests
const csrfToken = document.head.querySelector('meta[name="csrf-token"]');
if (csrfToken) {
    instance.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.content;
}
const sessionApiToken = getSessionApiToken();
if (sessionApiToken && sessionApiToken.trim() !== '') {
    instance.defaults.headers.common['Authorization'] = `Bearer ${sessionApiToken}`;
}

// Add request interceptor to include auth token
instance.interceptors.request.use(
    config => {
        // Skip adding auth header for login/register routes
        const publicEndpoints = ['/login', '/register', '/sanctum/csrf-cookie'];
        const isPublicEndpoint = publicEndpoints.some(endpoint => config.url.includes(endpoint));
        
        if (!isPublicEndpoint) {
            // First try to get the session API token (from blade template)
            const sessionApiToken = getSessionApiToken();

            // If no session token, fall back to localStorage/sessionStorage
            const token = sessionApiToken ||
                         localStorage.getItem('auth_token') ||
                         localStorage.getItem('token') ||
                         sessionStorage.getItem('auth_token');
            
            if (token) {
                console.log(`Adding auth token for request to: ${config.url}`);
                config.headers['Authorization'] = `Bearer ${token}`;
            } else {
                console.warn(`No auth token available for request to: ${config.url}`);
            }
        }

        // Ensure credentials are sent with every request (like fetch's credentials: 'same-origin')
        config.withCredentials = true;

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
            console.log('Request headers:', error.config.headers);
            
            // Check if this is an API request (not a public endpoint)
            const publicEndpoints = ['/login', '/register', '/sanctum/csrf-cookie'];
            const isPublicEndpoint = publicEndpoints.some(endpoint => 
                error.config.url.includes(endpoint)
            );
            
            // Handle all API 401 errors consistently
            if (!isPublicEndpoint && error.config.url.startsWith('/api')) {
                console.log('Authentication required for API request, redirecting to login');
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
