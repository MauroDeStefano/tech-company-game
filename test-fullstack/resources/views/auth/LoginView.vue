<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-sky-100 via-sky-50 to-white relative overflow-hidden">
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-20">
    </div>

    <!-- Main Login Container -->
    <div class="w-full max-w-md mx-auto p-6 z-10 relative">
      <!-- Header Section -->
      <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-2 tracking-tight">
          Tech Company Game
        </h1>
        <p class="text-gray-600 text-lg">
          Accedi al tuo account per gestire la tua software house
        </p>
      </div>

      <!-- Login Card -->
      <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 p-8">
        <form @submit.prevent="handleLogin" class="space-y-6">
          <!-- Error Alert -->
          <div 
            v-if="error" 
            class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-start gap-3"
          >
            <div class="text-red-500 text-xl flex-shrink-0 mt-0.5">⚠️</div>
            <div class="flex-1 min-w-0">
              <p class="text-red-800 font-medium text-sm">{{ error }}</p>
            </div>
            <button
              type="button"
              @click="clearError"
              class="text-red-400 hover:text-red-600 transition-colors duration-200 flex-shrink-0 p-1 rounded-lg hover:bg-red-100"
              aria-label="Chiudi errore"
            >
              <span class="text-sm font-bold">✕</span>
            </button>
          </div>

          <!-- Rate Limit Warning -->
          <div 
            v-if="!canAttemptLogin" 
            class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 flex items-start gap-3"
          >
            <div class="text-yellow-600 text-xl flex-shrink-0 mt-0.5">🕐</div>
            <div class="flex-1">
              <p class="text-yellow-800 font-medium text-sm">
                Troppi tentativi di login. Riprova tra {{ formatTimeRemaining(timeUntilNextAttempt) }}.
              </p>
            </div>
          </div>

          <!-- Email Field -->
          <div class="space-y-2">
            <label class="block text-sm font-semibold text-gray-700">
              Email
            </label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="text-gray-400 text-lg">✉️</span>
              </div>
              <input
                v-model="loginForm.email"
                type="email"
                placeholder="mario.rossi@esempio.com"
                :disabled="isLoading || !canAttemptLogin"
                required
                autofocus
                class="block w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl bg-white text-gray-900 placeholder-gray-500 focus:ring-2 focus:ring-sky-500 focus:border-sky-500 disabled:bg-gray-50 disabled:text-gray-500 disabled:cursor-not-allowed transition-all duration-200"
                :class="{
                  'border-red-300 focus:ring-red-500 focus:border-red-500': validationErrors.email,
                  'border-gray-300': !validationErrors.email
                }"
              />
            </div>
            <p v-if="validationErrors.email" class="text-red-600 text-sm font-medium">
              {{ validationErrors.email }}
            </p>
          </div>

          <!-- Password Field -->
          <div class="space-y-2">
            <label class="block text-sm font-semibold text-gray-700">
              Password
            </label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="text-gray-400 text-lg">🔒</span>
              </div>
              <input
                v-model="loginForm.password"
                type="password"
                placeholder="Inserisci la tua password"
                :disabled="isLoading || !canAttemptLogin"
                required
                class="block w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl bg-white text-gray-900 placeholder-gray-500 focus:ring-2 focus:ring-sky-500 focus:border-sky-500 disabled:bg-gray-50 disabled:text-gray-500 disabled:cursor-not-allowed transition-all duration-200"
                :class="{
                  'border-red-300 focus:ring-red-500 focus:border-red-500': validationErrors.password,
                  'border-gray-300': !validationErrors.password
                }"
              />
            </div>
            <p v-if="validationErrors.password" class="text-red-600 text-sm font-medium">
              {{ validationErrors.password }}
            </p>
          </div>

          <!-- Remember Me & Forgot Password -->
          <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 cursor-pointer">
              <input
                type="checkbox"
                v-model="loginForm.rememberMe"
                :disabled="isLoading"
                class="w-4 h-4 text-sky-600 bg-gray-100 border-gray-300 rounded focus:ring-sky-500 focus:ring-2 disabled:bg-gray-50 disabled:cursor-not-allowed"
              />
              <span class="text-sm text-gray-700 font-medium">Ricordami</span>
            </label>

            <router-link
              to="/forgot-password"
              class="text-sm text-sky-600 hover:text-sky-800 font-semibold transition-colors duration-200"
              :class="{ 'opacity-50 cursor-not-allowed': isLoading }"
            >
              Password dimenticata?
            </router-link>
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="!isFormValid || !canAttemptLogin || isLoading"
            class="w-full py-3 px-4 bg-sky-600 hover:bg-sky-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-bold rounded-xl transition-all duration-200 transform hover:scale-105 disabled:hover:scale-100 focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 shadow-lg"
          >
            <span v-if="isLoading" class="flex items-center justify-center gap-2">
              <svg class="animate-spin w-5 h-5 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Accesso in corso...
            </span>
            <span v-else>Accedi</span>
          </button>

          <!-- Divider -->
          <div class="relative my-6">
            <div class="absolute inset-0 flex items-center">
              <div class="w-full border-t border-gray-300"></div>
            </div>
            <div class="relative flex justify-center text-sm">
              <span class="px-4 bg-white text-gray-500 font-medium">oppure</span>
            </div>
          </div>

          <!-- Social Login -->
          <button
            type="button"
            :disabled="isLoading"
            class="w-full py-3 px-4 border border-gray-300 bg-white hover:bg-gray-50 disabled:bg-gray-50 disabled:cursor-not-allowed text-gray-700 font-semibold rounded-xl transition-all duration-200 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 shadow-sm flex items-center justify-center gap-3"
          >
            <span class="text-xl">🔗</span>
            Accedi con Google
          </button>

          <!-- Register Link -->
          <div class="text-center pt-4">
            <p class="text-gray-600 text-sm">
              Non hai ancora un account?
              <router-link
                to="/register"
                class="text-sky-600 hover:text-sky-800 font-semibold transition-colors duration-200 ml-1"
                :class="{ 'opacity-50 cursor-not-allowed': isLoading }"
              >
                Registrati qui
              </router-link>
            </p>
          </div>
        </form>
      </div>

      <!-- Footer -->
      <div class="text-center mt-8 space-y-3">
        <p class="text-gray-500 text-sm">
          © 2025 Tech Company Game. Tutti i diritti riservati.
        </p>
        <div class="flex justify-center items-center gap-6">
          <router-link 
            to="/about" 
            class="text-gray-500 hover:text-gray-700 text-sm font-medium transition-colors duration-200"
          >
            Info
          </router-link>
          <router-link 
            to="/help" 
            class="text-gray-500 hover:text-gray-700 text-sm font-medium transition-colors duration-200"
          >
            Aiuto
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, reactive, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/js/stores/auth'
import { useNotificationStore } from '@/js/stores/notifications'

// Stores
const authStore = useAuthStore()
const notificationStore = useNotificationStore()
const router = useRouter()
const route = useRoute()

// Reactive state
const isLoading = ref(false)
const error = ref('')

const loginForm = reactive({
  email: '',
  password: '',
  rememberMe: false
})

const validationErrors = reactive({
  email: '',
  password: ''
})

// Computed properties
const canAttemptLogin = computed(() => authStore.canAttemptLogin)
const timeUntilNextAttempt = computed(() => authStore.timeUntilNextAttempt)

const isFormValid = computed(() => {
  return loginForm.email.trim() &&
         loginForm.password.trim() &&
         isValidEmail(loginForm.email)
})

// Methods
const handleLogin = async () => {
  if (!isFormValid.value || !canAttemptLogin.value) return

  // Clear previous errors
  clearValidationErrors()
  error.value = ''

  isLoading.value = true

  try {
    await authStore.login({
      email: loginForm.email.trim(),
      password: loginForm.password
    })

    notificationStore.success('Accesso effettuato con successo!')

    // Redirect to intended page or dashboard
    const redirectTo = route.query.redirect || '/games'
    router.push(redirectTo)

  } catch (err) {
    console.error('Login error:', err)

    // Handle validation errors
    if (err.validationErrors) {
      Object.assign(validationErrors, err.validationErrors)
    } else {
      error.value = err.message || 'Errore durante l\'accesso. Riprova.'
    }
  } finally {
    isLoading.value = false
  }
}

const clearError = () => {
  error.value = ''
}

const clearValidationErrors = () => {
  validationErrors.email = ''
  validationErrors.password = ''
}

const isValidEmail = (email) => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return emailRegex.test(email)
}

const formatTimeRemaining = (ms) => {
  const minutes = Math.ceil(ms / (1000 * 60))
  return minutes === 1 ? '1 minuto' : `${minutes} minuti`
}

// Auto-focus on page load
onMounted(() => {
  // If user is already authenticated, redirect
  if (authStore.isAuthenticated) {
    router.push('/game')
  }
})
</script>