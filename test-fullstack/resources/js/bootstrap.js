import axios from 'axios'

// Make axios available globally
window.axios = axios

// Setup default headers for Laravel integration
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

// Get CSRF token from meta tag (set in blade template)
const token = document.head.querySelector('meta[name="csrf-token"]')
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content
} else {
    console.error('CSRF token not found: Make sure to include <meta name="csrf-token" content="{{ csrf_token() }}"> in your blade template')
}

// Setup base URL for API calls
window.axios.defaults.baseURL = '/api'

// Request interceptor to add auth token
window.axios.interceptors.request.use(
    config => {
        const token = localStorage.getItem('auth-token')
        if (token) {
            config.headers.Authorization = `Bearer ${token}`
        }
        return config
    },
    error => {
        return Promise.reject(error)
    }
)

// Response interceptor for global error handling
window.axios.interceptors.response.use(
    response => {
        return response
    },
    error => {
        // Handle common HTTP errors
        switch (error.response?.status) {
            case 401:
                // Unauthorized - redirect to login
                localStorage.removeItem('auth-token')
                if (!window.location.pathname.includes('/login')) {
                    window.location.href = '/login'
                }
                break

            case 403:
                // Forbidden - show error message
                console.warn('Access forbidden:', error.response.data.message)
                break

            case 404:
                // Not found - could redirect to 404 page
                console.warn('Resource not found:', error.config.url)
                break

            case 422:
                // Validation errors - these should be handled by individual components
                break

            case 500:
                // Server error - show generic error message
                console.error('Server error occurred:', error.response.data)
                break

            default:
                console.error('API Error:', error)
        }

        return Promise.reject(error)
    }
)

// Global configuration
window.axios.defaults.timeout = 10000 // 10 seconds timeout