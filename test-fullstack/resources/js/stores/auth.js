import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authService } from '@/services/authService'

export const useAuthStore = defineStore('auth', () => {
  // State
  const user = ref(null)
  const token = ref(localStorage.getItem('auth-token'))
  const isLoading = ref(false)
  const loginAttempts = ref(0)
  const lastLoginAttempt = ref(null)

  // Getters
  const isAuthenticated = computed(() => !!token.value && !!user.value)
  const userName = computed(() => user.value?.name || '')
  const userEmail = computed(() => user.value?.email || '')
  const userInitials = computed(() => {
    if (!user.value?.name) return 'U'
    return user.value.name
      .split(' ')
      .map(n => n.charAt(0))
      .slice(0, 2)
      .join('')
      .toUpperCase()
  })

  // Rate limiting
  const canAttemptLogin = computed(() => {
    if (loginAttempts.value < 5) return true

    const lastAttempt = lastLoginAttempt.value
    if (!lastAttempt) return true

    const timeSinceLastAttempt = Date.now() - lastAttempt
    const cooldownPeriod = 15 * 60 * 1000 // 15 minutes

    return timeSinceLastAttempt > cooldownPeriod
  })

  const timeUntilNextAttempt = computed(() => {
    if (canAttemptLogin.value) return 0

    const lastAttempt = lastLoginAttempt.value
    const cooldownPeriod = 15 * 60 * 1000 // 15 minutes
    const elapsed = Date.now() - lastAttempt

    return Math.max(0, cooldownPeriod - elapsed)
  })

  // Actions
  async function login(credentials) {
    if (!canAttemptLogin.value) {
      throw new Error('Troppi tentativi di login. Riprova tra qualche minuto.')
    }

    try {
      isLoading.value = true

      const response = await authService.login(credentials)

      if (response.token && response.user) {
        token.value = response.token
        user.value = response.user

        // Store token in localStorage
        localStorage.setItem('auth-token', response.token)

        // Reset login attempts on successful login
        loginAttempts.value = 0
        lastLoginAttempt.value = null

        console.log('✅ Login successful:', response.user.name)
        return response
      } else {
        throw new Error('Risposta login non valida dal server')
      }
    } catch (error) {
      // Increment login attempts on failure
      loginAttempts.value++
      lastLoginAttempt.value = Date.now()

      console.error('❌ Login failed:', error.message)

      // Handle specific error types
      if (error.response?.status === 422) {
        throw new Error('Email o password non validi')
      } else if (error.response?.status === 429) {
        throw new Error('Troppi tentativi. Riprova più tardi.')
      } else if (error.response?.status >= 500) {
        throw new Error('Errore del server. Riprova più tardi.')
      } else {
        throw new Error(error.message || 'Errore durante il login')
      }
    } finally {
      isLoading.value = false
    }
  }

  async function register(userData) {
    try {
      isLoading.value = true

      const response = await authService.register(userData)

      if (response.token && response.user) {
        token.value = response.token
        user.value = response.user

        localStorage.setItem('auth-token', response.token)

        console.log('✅ Registration successful:', response.user.name)
        return response
      } else {
        throw new Error('Risposta registrazione non valida dal server')
      }
    } catch (error) {
      console.error('❌ Registration failed:', error.message)

      if (error.response?.status === 422) {
        // Validation errors - pass them through for form handling
        throw error
      } else if (error.response?.status >= 500) {
        throw new Error('Errore del server. Riprova più tardi.')
      } else {
        throw new Error(error.message || 'Errore durante la registrazione')
      }
    } finally {
      isLoading.value = false
    }
  }

  async function logout() {
    try {
      isLoading.value = true

      // Call logout API if token exists
      if (token.value) {
        await authService.logout()
      }

      // Clear local state
      clearAuthData()

      console.log('✅ Logout successful')
    } catch (error) {
      console.error('❌ Logout API failed:', error.message)
      // Still clear local data even if API call fails
      clearAuthData()
    } finally {
      isLoading.value = false
    }
  }

  async function fetchUser() {
    if (!token.value) {
      console.warn('No token available for user fetch')
      return null
    }

    try {
      isLoading.value = true

      const response = await authService.getUser()
      user.value = response.user

      console.log('✅ User data refreshed')
      return response.user
    } catch (error) {
      console.error('❌ Failed to fetch user:', error.message)

      if (error.response?.status === 401) {
        // Token is invalid, clear auth data
        clearAuthData()
      }

      throw error
    } finally {
      isLoading.value = false
    }
  }

  async function updateProfile(profileData) {
    try {
      isLoading.value = true

      const response = await authService.updateProfile(profileData)
      user.value = { ...user.value, ...response.user }

      console.log('✅ Profile updated successfully')
      return response
    } catch (error) {
      console.error('❌ Profile update failed:', error.message)
      throw error
    } finally {
      isLoading.value = false
    }
  }

  async function changePassword(passwordData) {
    try {
      isLoading.value = true

      await authService.changePassword(passwordData)

      console.log('✅ Password changed successfully')
    } catch (error) {
      console.error('❌ Password change failed:', error.message)
      throw error
    } finally {
      isLoading.value = false
    }
  }

  function clearAuthData() {
    user.value = null
    token.value = null
    localStorage.removeItem('auth-token')
  }

  function initializeAuth() {
    const savedToken = localStorage.getItem('auth-token')

    if (savedToken) {
      token.value = savedToken
      // Optionally fetch user data on initialization
      fetchUser().catch(() => {
        console.warn('Failed to fetch user on auth initialization')
        clearAuthData()
      })
    }
  }

  // Permission helpers
  function hasPermission(permission) {
    if (!user.value) return false
    return user.value.permissions?.includes(permission) || false
  }

  function hasRole(role) {
    if (!user.value) return false
    return user.value.roles?.includes(role) || false
  }

  function isAdmin() {
    return hasRole('admin')
  }

  // Session helpers
  function refreshSession() {
    if (token.value) {
      return fetchUser()
    }
    return Promise.resolve(null)
  }

  function getAuthHeaders() {
    return token.value ? { Authorization: `Bearer ${token.value}` } : {}
  }

  // Reset password flow
  async function requestPasswordReset(email) {
    try {
      isLoading.value = true
      await authService.requestPasswordReset(email)
      console.log('✅ Password reset email sent')
    } catch (error) {
      console.error('❌ Password reset request failed:', error.message)
      throw error
    } finally {
      isLoading.value = false
    }
  }

  async function resetPassword(resetData) {
    try {
      isLoading.value = true
      await authService.resetPassword(resetData)
      console.log('✅ Password reset successful')
    } catch (error) {
      console.error('❌ Password reset failed:', error.message)
      throw error
    } finally {
      isLoading.value = false
    }
  }

  return {
    // State
    user,
    token,
    isLoading,

    // Getters
    isAuthenticated,
    userName,
    userEmail,
    userInitials,
    canAttemptLogin,
    timeUntilNextAttempt,

    // Actions
    login,
    register,
    logout,
    fetchUser,
    updateProfile,
    changePassword,
    clearAuthData,
    initializeAuth,

    // Permissions
    hasPermission,
    hasRole,
    isAdmin,

    // Session
    refreshSession,
    getAuthHeaders,

    // Password reset
    requestPasswordReset,
    resetPassword
  }
})