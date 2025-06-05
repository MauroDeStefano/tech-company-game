import axios from 'axios'

// Create axios instance with base configuration
const api = axios.create({
  baseURL: '/api',
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  }
})

// Request interceptor
api.interceptors.request.use(
  (config) => {
    // Add CSRF token
    const csrfToken = document.head.querySelector('meta[name="csrf-token"]')?.content
    if (csrfToken) {
      config.headers['X-CSRF-TOKEN'] = csrfToken
    }

    // Add auth token
    const token = localStorage.getItem('auth-token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }

    // Log request in development
    if (process.env.NODE_ENV === 'development') {
      console.log(`🚀 API Request: ${config.method?.toUpperCase()} ${config.url}`, {
        data: config.data,
        params: config.params
      })
    }

    return config
  },
  (error) => {
    console.error('❌ Request interceptor error:', error)
    return Promise.reject(error)
  }
)

// Response interceptor
api.interceptors.response.use(
  (response) => {
    // Log response in development
    if (process.env.NODE_ENV === 'development') {
      console.log(`✅ API Response: ${response.config.method?.toUpperCase()} ${response.config.url}`, {
        status: response.status,
        data: response.data
      })
    }

    return response
  },
  (error) => {
    // Log error in development
    if (process.env.NODE_ENV === 'development') {
      console.error(`❌ API Error: ${error.config?.method?.toUpperCase()} ${error.config?.url}`, {
        status: error.response?.status,
        data: error.response?.data,
        message: error.message
      })
    }

    // Handle specific error cases
    if (error.response) {
      const { status, data } = error.response

      switch (status) {
        case 401:
          // Unauthorized - token expired or invalid
          localStorage.removeItem('auth-token')
          if (!window.location.pathname.includes('/login')) {
            window.location.href = '/login'
          }
          break

        case 403:
          // Forbidden - user doesn't have permission
          console.warn('Access forbidden:', data.message)
          break

        case 404:
          // Not found
          console.warn('Resource not found:', error.config.url)
          break

        case 422:
          // Validation error - let component handle this
          break

        case 429:
          // Rate limited
          console.warn('Rate limited. Please slow down.')
          break

        case 500:
        case 502:
        case 503:
        case 504:
          // Server errors
          console.error('Server error occurred. Please try again later.')
          break
      }

      // Enhance error object with useful information
      error.isValidationError = status === 422
      error.isServerError = status >= 500
      error.isClientError = status >= 400 && status < 500
      error.statusCode = status
      error.errorMessage = data?.message || error.message
      error.validationErrors = data?.errors || null
    } else if (error.request) {
      // Network error
      console.error('Network error - no response received')
      error.isNetworkError = true
      error.errorMessage = 'Errore di connessione. Controlla la tua connessione internet.'
    } else {
      // Request setup error
      console.error('Request setup error:', error.message)
      error.errorMessage = error.message
    }

    return Promise.reject(error)
  }
)

// Helper functions for common API patterns
export const apiHelpers = {
  // Standard CRUD operations
  get: (url, config = {}) => api.get(url, config),
  post: (url, data = {}, config = {}) => api.post(url, data, config),
  put: (url, data = {}, config = {}) => api.put(url, data, config),
  patch: (url, data = {}, config = {}) => api.patch(url, data, config),
  delete: (url, config = {}) => api.delete(url, config),

  // File upload
  uploadFile: (url, file, onProgress = null) => {
    const formData = new FormData()
    formData.append('file', file)

    return api.post(url, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
      onUploadProgress: onProgress
    })
  },

  // Download file
  downloadFile: async (url, filename = null) => {
    const response = await api.get(url, {
      responseType: 'blob'
    })

    const blob = new Blob([response.data])
    const downloadUrl = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = downloadUrl
    link.download = filename || 'download'
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    window.URL.revokeObjectURL(downloadUrl)
  },

  // Paginated requests
  getPaginated: async (url, page = 1, perPage = 15, params = {}) => {
    const response = await api.get(url, {
      params: {
        page,
        per_page: perPage,
        ...params
      }
    })
    return response
  },

  // Retry mechanism for failed requests
  retry: async (apiCall, maxRetries = 3, delay = 1000) => {
    for (let i = 0; i < maxRetries; i++) {
      try {
        return await apiCall()
      } catch (error) {
        if (i === maxRetries - 1 || error.response?.status < 500) {
          throw error
        }

        // Wait before retry
        await new Promise(resolve => setTimeout(resolve, delay * (i + 1)))
      }
    }
  },

  // Health check
  healthCheck: () => api.get('/health'),

  // Batch requests
  batch: (requests) => Promise.allSettled(requests),

  // Search with debouncing (use with composable)
  search: (url, query, params = {}) => {
    return api.get(url, {
      params: {
        q: query,
        ...params
      }
    })
  }
}

// Request/Response logging utility
export const apiLogger = {
  enable: () => {
    window.__API_LOGGING_ENABLED__ = true
  },

  disable: () => {
    window.__API_LOGGING_ENABLED__ = false
  },

  isEnabled: () => !!window.__API_LOGGING_ENABLED__
}

// Error handling utilities
export const apiErrors = {
  isValidationError: (error) => error?.isValidationError || error?.response?.status === 422,
  isNetworkError: (error) => error?.isNetworkError || !error?.response,
  isServerError: (error) => error?.isServerError || error?.response?.status >= 500,
  isAuthError: (error) => error?.response?.status === 401,
  isForbiddenError: (error) => error?.response?.status === 403,

  getErrorMessage: (error) => {
    if (error?.errorMessage) return error.errorMessage
    if (error?.response?.data?.message) return error.response.data.message
    if (error?.message) return error.message
    return 'Si è verificato un errore imprevisto'
  },

  getValidationErrors: (error) => {
    return error?.validationErrors || error?.response?.data?.errors || null
  }
}

// Export configured axios instance as default
export default api