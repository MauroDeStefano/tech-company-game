import api, { apiHelpers, apiErrors } from './api'

export const authService = {
  /**
   * Login user with email and password
   */
  async login(credentials) {
    try {
      const response = await api.post('/auth/login', {
        email: credentials.email,
        password: credentials.password
      })

      return response.data
    } catch (error) {
      throw this.handleAuthError(error)
    }
  },

  /**
   * Register new user
   */
  async register(userData) {
    try {
      const response = await api.post('/auth/register', {
        name: userData.name,
        email: userData.email,
        password: userData.password,
        password_confirmation: userData.password_confirmation
      })

      return response.data
    } catch (error) {
      throw this.handleAuthError(error)
    }
  },

  /**
   * Logout current user
   */
  async logout() {
    try {
      await api.post('/auth/logout')
      return true
    } catch (error) {
      // Even if logout API fails, we should still clear local storage
      console.warn('Logout API failed:', error.message)
      return true
    }
  },

  /**
   * Get current user data
   */
  async getUser() {
    try {
      const response = await api.get('/auth/me')
      return response.data
    } catch (error) {
      throw this.handleAuthError(error)
    }
  },

  /**
   * Update user profile
   */
  async updateProfile(profileData) {
    try {
      const response = await api.put('/user', profileData)
      return response.data
    } catch (error) {
      throw this.handleAuthError(error)
    }
  },

  /**
   * Change password
   */
  async changePassword(passwordData) {
    try {
      const response = await api.post('/auth/change-password', {
        current_password: passwordData.currentPassword,
        password: passwordData.newPassword,
        password_confirmation: passwordData.passwordConfirmation
      })

      return response.data
    } catch (error) {
      throw this.handleAuthError(error)
    }
  },

  /**
   * Request password reset
   */
  async requestPasswordReset(email) {
    try {
      const response = await api.post('/auth/forgot-password', { email })
      return response.data
    } catch (error) {
      throw this.handleAuthError(error)
    }
  },

  /**
   * Reset password with token
   */
  async resetPassword(resetData) {
    try {
      const response = await api.post('/auth/reset-password', {
        token: resetData.token,
        email: resetData.email,
        password: resetData.password,
        password_confirmation: resetData.password_confirmation
      })

      return response.data
    } catch (error) {
      throw this.handleAuthError(error)
    }
  },

  /**
   * Verify email with token
   */
  async verifyEmail(token) {
    try {
      const response = await api.post('/auth/verify-email', { token })
      return response.data
    } catch (error) {
      throw this.handleAuthError(error)
    }
  },

  /**
   * Resend email verification
   */
  async resendEmailVerification() {
    try {
      const response = await api.post('/auth/resend-verification')
      return response.data
    } catch (error) {
      throw this.handleAuthError(error)
    }
  },

  /**
   * Refresh authentication token
   */
  async refreshToken() {
    try {
      const response = await api.post('/auth/refresh')
      return response.data
    } catch (error) {
      throw this.handleAuthError(error)
    }
  },

  /**
   * Check if email is available
   */
  async checkEmailAvailability(email) {
    try {
      const response = await api.post('/auth/check-email', { email })
      return response.data.available
    } catch (error) {
      return false
    }
  },

  /**
   * Get user preferences
   */
  async getUserPreferences() {
    try {
      const response = await api.get('/user/preferences')
      return response.data
    } catch (error) {
      throw this.handleAuthError(error)
    }
  },

  /**
   * Update user preferences
   */
  async updateUserPreferences(preferences) {
    try {
      const response = await api.put('/user/preferences', preferences)
      return response.data
    } catch (error) {
      throw this.handleAuthError(error)
    }
  },

  /**
   * Upload user avatar
   */
  async uploadAvatar(file) {
    try {
      const response = await apiHelpers.uploadFile('/user/avatar', file)
      return response.data
    } catch (error) {
      throw this.handleAuthError(error)
    }
  },

  /**
   * Delete user avatar
   */
  async deleteAvatar() {
    try {
      const response = await api.delete('/user/avatar')
      return response.data
    } catch (error) {
      throw this.handleAuthError(error)
    }
  },

  /**
   * Get user activity log
   */
  async getUserActivity(page = 1, limit = 20) {
    try {
      const response = await apiHelpers.getPaginated('/user/activity', page, limit)
      return response.data
    } catch (error) {
      throw this.handleAuthError(error)
    }
  },

  /**
   * Delete user account
   */
  async deleteAccount(password) {
    try {
      const response = await api.delete('/user', {
        data: { password }
      })
      return response.data
    } catch (error) {
      throw this.handleAuthError(error)
    }
  },

  /**
   * Two-factor authentication setup
   */
  async enable2FA() {
    try {
      const response = await api.post('/auth/2fa/enable')
      return response.data
    } catch (error) {
      throw this.handleAuthError(error)
    }
  },

  /**
   * Confirm 2FA setup
   */
  async confirm2FA(code) {
    try {
      const response = await api.post('/auth/2fa/confirm', { code })
      return response.data
    } catch (error) {
      throw this.handleAuthError(error)
    }
  },

  /**
   * Disable 2FA
   */
  async disable2FA(password) {
    try {
      const response = await api.post('/auth/2fa/disable', { password })
      return response.data
    } catch (error) {
      throw this.handleAuthError(error)
    }
  },

  /**
   * Verify 2FA code during login
   */
  async verify2FA(code, loginToken) {
    try {
      const response = await api.post('/auth/2fa/verify', {
        code,
        login_token: loginToken
      })
      return response.data
    } catch (error) {
      throw this.handleAuthError(error)
    }
  },

  /**
   * Get recovery codes for 2FA
   */
  async getRecoveryCodes() {
    try {
      const response = await api.get('/auth/2fa/recovery-codes')
      return response.data
    } catch (error) {
      throw this.handleAuthError(error)
    }
  },

  /**
   * Regenerate recovery codes
   */
  async regenerateRecoveryCodes() {
    try {
      const response = await api.post('/auth/2fa/recovery-codes/regenerate')
      return response.data
    } catch (error) {
      throw this.handleAuthError(error)
    }
  },

  /**
   * Social login (OAuth)
   */
  async socialLogin(provider, code) {
    try {
      const response = await api.post(`/auth/social/${provider}`, { code })
      return response.data
    } catch (error) {
      throw this.handleAuthError(error)
    }
  },

  /**
   * Link social account
   */
  async linkSocialAccount(provider, code) {
    try {
      const response = await api.post(`/auth/social/${provider}/link`, { code })
      return response.data
    } catch (error) {
      throw this.handleAuthError(error)
    }
  },

  /**
   * Unlink social account
   */
  async unlinkSocialAccount(provider) {
    try {
      const response = await api.delete(`/auth/social/${provider}/unlink`)
      return response.data
    } catch (error) {
      throw this.handleAuthError(error)
    }
  },

  /**
   * Handle authentication errors
   */
  handleAuthError(error) {
    const errorMessage = apiErrors.getErrorMessage(error)
    const validationErrors = apiErrors.getValidationErrors(error)

    // Create enhanced error object
    const authError = new Error(errorMessage)
    authError.originalError = error
    authError.validationErrors = validationErrors
    authError.isValidationError = apiErrors.isValidationError(error)
    authError.isNetworkError = apiErrors.isNetworkError(error)
    authError.isServerError = apiErrors.isServerError(error)
    authError.statusCode = error?.response?.status

    // Log error for debugging
    console.error('Auth Service Error:', {
      message: errorMessage,
      status: error?.response?.status,
      validationErrors,
      originalError: error
    })

    return authError
  },

  /**
   * Validate email format
   */
  validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    return emailRegex.test(email)
  },

  /**
   * Validate password strength
   */
  validatePassword(password) {
    const minLength = 8
    const hasUpperCase = /[A-Z]/.test(password)
    const hasLowerCase = /[a-z]/.test(password)
    const hasNumbers = /\d/.test(password)
    const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(password)

    return {
      isValid: password.length >= minLength && hasUpperCase && hasLowerCase && hasNumbers,
      length: password.length >= minLength,
      hasUpperCase,
      hasLowerCase,
      hasNumbers,
      hasSpecialChar,
      score: [
        password.length >= minLength,
        hasUpperCase,
        hasLowerCase,
        hasNumbers,
        hasSpecialChar
      ].filter(Boolean).length
    }
  },

  /**
   * Check if user is authenticated (client-side check)
   */
  isAuthenticated() {
    const token = localStorage.getItem('auth-token')
    return !!token
  },

  /**
   * Get stored auth token
   */
  getAuthToken() {
    return localStorage.getItem('auth-token')
  },

  /**
   * Clear stored auth data
   */
  clearAuthData() {
    localStorage.removeItem('auth-token')
  }
}