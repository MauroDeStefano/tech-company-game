<template>
  <div class="login-view">
    <div class="login-container">
      <!-- Header -->
      <div class="login-header">
        <div class="brand-logo">🏢</div>
        <h1 class="brand-title">Tech Company Game</h1>
        <p class="brand-subtitle">Accedi al tuo account per gestire la tua software house</p>
      </div>

      <!-- Login Form -->
      <BaseCard class="login-card">
        <form @submit.prevent="handleLogin" class="login-form">
          <!-- Error Alert -->
          <div v-if="error" class="error-alert">
            <div class="error-icon">⚠️</div>
            <div class="error-content">
              <p class="error-message">{{ error }}</p>
              <button
                type="button"
                @click="clearError"
                class="error-dismiss"
                aria-label="Chiudi errore"
              >
                ✕
              </button>
            </div>
          </div>

          <!-- Rate Limit Warning -->
          <div v-if="!canAttemptLogin" class="warning-alert">
            <div class="warning-icon">🕐</div>
            <div class="warning-content">
              <p class="warning-message">
                Troppi tentativi di login. Riprova tra {{ formatTimeRemaining(timeUntilNextAttempt) }}.
              </p>
            </div>
          </div>

          <!-- Email Field -->
          <BaseInput
            v-model="loginForm.email"
            type="email"
            label="Email"
            placeholder="mario.rossi@esempio.com"
            :error-message="validationErrors.email"
            :disabled="isLoading || !canAttemptLogin"
            required
            autofocus
            left-icon="✉️"
          />

          <!-- Password Field -->
          <BaseInput
            v-model="loginForm.password"
            type="password"
            label="Password"
            placeholder="Inserisci la tua password"
            :error-message="validationErrors.password"
            :disabled="isLoading || !canAttemptLogin"
            required
            left-icon="🔒"
          />

          <!-- Remember Me & Forgot Password -->
          <div class="form-options">
            <label class="remember-me">
              <input
                type="checkbox"
                v-model="loginForm.rememberMe"
                :disabled="isLoading"
                class="remember-checkbox"
              />
              <span class="remember-text">Ricordami</span>
            </label>

            <router-link
              to="/forgot-password"
              class="forgot-link"
              :class="{ 'forgot-link--disabled': isLoading }"
            >
              Password dimenticata?
            </router-link>
          </div>

          <!-- Submit Button -->
          <BaseButton
            type="submit"
            variant="primary"
            size="lg"
            block
            :loading="isLoading"
            :disabled="!isFormValid || !canAttemptLogin"
            class="login-button"
          >
            <template v-if="isLoading">
              Accesso in corso...
            </template>
            <template v-else>
              Accedi
            </template>
          </BaseButton>

          <!-- Divider -->
          <div class="divider">
            <span class="divider-text">oppure</span>
          </div>

          <!-- Social Login (placeholder) -->
          <div class="social-login">
            <BaseButton
              variant="outline"
              size="lg"
              block
              :disabled="isLoading"
              class="social-button"
            >
              <span class="social-icon">🔗</span>
              Accedi con Google
            </BaseButton>
          </div>

          <!-- Register Link -->
          <div class="register-section">
            <p class="register-text">
              Non hai ancora un account?
              <router-link
                to="/register"
                class="register-link"
                :class="{ 'register-link--disabled': isLoading }"
              >
                Registrati qui
              </router-link>
            </p>
          </div>
        </form>
      </BaseCard>

      <!-- Footer -->
      <div class="login-footer">
        <p class="footer-text">
          © 2025 Tech Company Game. Tutti i diritti riservati.
        </p>
        <div class="footer-links">
          <router-link to="/about" class="footer-link">Info</router-link>
          <router-link to="/help" class="footer-link">Aiuto</router-link>
        </div>
      </div>
    </div>

    <!-- Background Decoration -->
    <div class="login-background">
      <div class="bg-pattern"></div>
      <div class="bg-gradient"></div>
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
    const redirectTo = route.query.redirect || '/game'
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

<style scoped>
.login-view {
  @apply min-h-screen flex items-center justify-center relative;
  @apply bg-gradient-to-br from-brand-50 to-brand-100;
}

.login-container {
  @apply relative z-10 w-full max-w-md px-6;
}

/* Header */
.login-header {
  @apply text-center mb-8;
}

.brand-logo {
  @apply text-6xl mb-4;
}

.brand-title {
  @apply text-3xl font-bold text-neutral-900 mb-2;
}

.brand-subtitle {
  @apply text-neutral-600 text-sm;
}

/* Login Card */
.login-card {
  @apply shadow-xl border-0;
  @apply bg-white/95 backdrop-blur-sm;
}

.login-form {
  @apply space-y-6 p-8;
}

/* Error Alert */
.error-alert {
  @apply flex items-start p-4 bg-danger-50 border border-danger-200 rounded-lg;
}

.error-icon {
  @apply text-danger-500 mr-3 flex-shrink-0;
}

.error-content {
  @apply flex-1 flex justify-between items-start;
}

.error-message {
  @apply text-sm text-danger-800;
}

.error-dismiss {
  @apply ml-2 text-danger-400 hover:text-danger-600;
  @apply transition-colors duration-200;
}

/* Warning Alert */
.warning-alert {
  @apply flex items-start p-4 bg-warning-50 border border-warning-200 rounded-lg;
}

.warning-icon {
  @apply text-warning-500 mr-3 flex-shrink-0;
}

.warning-content {
  @apply flex-1;
}

.warning-message {
  @apply text-sm text-warning-800;
}

/* Form Options */
.form-options {
  @apply flex items-center justify-between;
}

.remember-me {
  @apply flex items-center cursor-pointer;
}

.remember-checkbox {
  @apply mr-2 rounded border-neutral-300;
  @apply focus:ring-2 focus:ring-brand-500 focus:border-brand-500;
}

.remember-text {
  @apply text-sm text-neutral-700;
}

.forgot-link {
  @apply text-sm text-brand-600 hover:text-brand-700;
  @apply transition-colors duration-200;
}

.forgot-link--disabled {
  @apply pointer-events-none opacity-50;
}

/* Submit Button */
.login-button {
  @apply mt-6;
}

/* Divider */
.divider {
  @apply relative;
}

.divider::before {
  @apply absolute inset-0 flex items-center;
  content: '';
}

.divider::before {
  @apply border-t border-neutral-300;
}

.divider-text {
  @apply relative bg-white px-4 text-sm text-neutral-500;
  @apply mx-auto w-fit;
}

/* Social Login */
.social-login {
  @apply space-y-3;
}

.social-button {
  @apply flex items-center justify-center;
}

.social-icon {
  @apply mr-2;
}

/* Register Section */
.register-section {
  @apply text-center pt-4 border-t border-neutral-200;
}

.register-text {
  @apply text-sm text-neutral-600;
}

.register-link {
  @apply text-brand-600 hover:text-brand-700 font-medium;
  @apply transition-colors duration-200;
}

.register-link--disabled {
  @apply pointer-events-none opacity-50;
}

/* Footer */
.login-footer {
  @apply text-center mt-8 space-y-2;
}

.footer-text {
  @apply text-xs text-neutral-500;
}

.footer-links {
  @apply flex justify-center space-x-4;
}

.footer-link {
  @apply text-xs text-neutral-400 hover:text-neutral-600;
  @apply transition-colors duration-200;
}

/* Background */
.login-background {
  @apply absolute inset-0;
}

.bg-pattern {
  @apply absolute inset-0 opacity-10;
  background-image: radial-gradient(circle at 1px 1px, theme('colors.brand.400') 1px, transparent 0);
  background-size: 20px 20px;
}

.bg-gradient {
  @apply absolute inset-0;
  background: linear-gradient(45deg,
    theme('colors.brand.100') 0%,
    theme('colors.brand.50') 50%,
    theme('colors.secondary.50') 100%);
}

/* Responsive */
@media (max-width: 640px) {
  .login-container {
    @apply px-4;
  }

  .login-form {
    @apply p-6;
  }

  .brand-logo {
    @apply text-5xl mb-3;
  }

  .brand-title {
    @apply text-2xl;
  }

  .form-options {
    @apply flex-col space-y-3 items-start;
  }
}

/* Focus states */
.login-form input:focus {
  @apply ring-2 ring-brand-500 border-brand-500;
}

/* Loading state */
.login-form.loading {
  @apply pointer-events-none opacity-75;
}

/* Animations */
.error-alert,
.warning-alert {
  animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
  .login-card {
    @apply bg-neutral-900/95;
  }

  .brand-title {
    @apply text-neutral-100;
  }

  .brand-subtitle {
    @apply text-neutral-400;
  }

  .register-text {
    @apply text-neutral-400;
  }

  .footer-text {
    @apply text-neutral-500;
  }

  .divider-text {
    @apply bg-neutral-900 text-neutral-400;
  }

  .divider::before {
    @apply border-neutral-700;
  }
}
</style>