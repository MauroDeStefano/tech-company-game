<template>
  <div class="register-view">
    <!-- Page Header -->
    <div class="register-header">
      <div class="brand-section">
        <h1 class="brand-title">
          <span class="brand-icon">🏢</span>
          Tech Company Game
        </h1>
        <p class="brand-subtitle">Crea il tuo account e inizia a gestire la tua software house</p>
      </div>
    </div>

    <!-- Registration Form -->
    <div class="register-form-container">
      <BaseCard class="register-card" padding="lg">
        <template #title>
          <span class="register-form-title">Registrazione</span>
        </template>

        <!-- Success Message -->
        <div v-if="successMessage" class="success-message">
          <div class="success-content">
            <span class="success-icon">✅</span>
            <span class="success-text">{{ successMessage }}</span>
          </div>
        </div>

        <!-- Error Messages -->
        <div v-if="generalError" class="error-message">
          <div class="error-content">
            <span class="error-icon">⚠️</span>
            <span class="error-text">{{ generalError }}</span>
          </div>
        </div>

        <!-- Registration Form -->
        <form @submit.prevent="handleRegister" class="register-form">
          <div class="form-fields">
            <!-- Nome -->
            <BaseInput
              v-model="form.name"
              type="text"
              label="Nome completo"
              placeholder="Inserisci il tuo nome completo"
              leftIcon="👤"
              :errorMessage="validationErrors.name?.[0]"
              required
              autocomplete="name"
              :disabled="isLoading"
            />

            <!-- Email -->
            <BaseInput
              v-model="form.email"
              type="email"
              label="Indirizzo email"
              placeholder="esempio@email.com"
              leftIcon="📧"
              :errorMessage="validationErrors.email?.[0]"
              required
              autocomplete="email"
              :disabled="isLoading"
            />

            <!-- Password -->
            <BaseInput
              v-model="form.password"
              type="password"
              label="Password"
              placeholder="Scegli una password sicura"
              leftIcon="🔒"
              :errorMessage="validationErrors.password?.[0]"
              required
              autocomplete="new-password"
              :disabled="isLoading"
              helperText="Minimo 8 caratteri"
            />

            <!-- Conferma Password -->
            <BaseInput
              v-model="form.password_confirmation"
              type="password"
              label="Conferma password"
              placeholder="Ripeti la password"
              leftIcon="🔒"
              :errorMessage="validationErrors.password_confirmation?.[0]"
              required
              autocomplete="new-password"
              :disabled="isLoading"
            />
          </div>

          <!-- Password Strength Indicator -->
          <div v-if="form.password" class="password-strength">
            <div class="strength-label">Sicurezza password:</div>
            <div class="strength-bar">
              <div
                class="strength-fill"
                :class="`strength-fill--${passwordStrength.level}`"
                :style="{ width: `${passwordStrength.percentage}%` }"
              ></div>
            </div>
            <div class="strength-text" :class="`strength-text--${passwordStrength.level}`">
              {{ passwordStrength.label }}
            </div>
          </div>

          <!-- Terms and Conditions -->
          <div class="terms-section">
            <label class="terms-checkbox">
              <input
                type="checkbox"
                v-model="form.acceptTerms"
                :disabled="isLoading"
                required
              />
              <span class="checkmark"></span>
              <span class="terms-text">
                Accetto i
                <router-link to="/terms" class="terms-link">Termini e Condizioni</router-link>
                e la
                <router-link to="/privacy" class="terms-link">Privacy Policy</router-link>
              </span>
            </label>
          </div>

          <!-- Submit Button -->
          <BaseButton
            type="submit"
            variant="primary"
            size="lg"
            block
            :loading="isLoading"
            :disabled="!isFormValid || isLoading"
            class="register-button"
          >
            <template v-if="!isLoading">
              <span class="button-icon">🚀</span>
              Crea Account
            </template>
            <template v-else>
              Creazione account...
            </template>
          </BaseButton>
        </form>

        <!-- Login Link -->
        <div class="login-link-section">
          <p class="login-text">
            Hai già un account?
            <router-link to="/login" class="login-link">
              Accedi qui
            </router-link>
          </p>
        </div>

        <!-- Development Info -->
        <div v-if="isDevelopment" class="dev-info">
          <details class="dev-details">
            <summary class="dev-summary">🛠️ Info Sviluppo</summary>
            <div class="dev-content">
              <pre>{{ JSON.stringify({ form, validationErrors, passwordStrength }, null, 2) }}</pre>
            </div>
          </details>
        </div>
      </BaseCard>
    </div>

    <!-- Background Elements -->
    <div class="background-elements">
      <div class="bg-element bg-element--1">💼</div>
      <div class="bg-element bg-element--2">🚀</div>
      <div class="bg-element bg-element--3">💰</div>
      <div class="bg-element bg-element--4">📊</div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/js/stores/auth'
import { useNotificationStore } from '@/js/stores/notifications'

// Stores
const authStore = useAuthStore()
const notificationStore = useNotificationStore()
const router = useRouter()

// Reactive state
const isLoading = ref(false)
const successMessage = ref('')
const generalError = ref('')
const validationErrors = reactive({})

// Form data
const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  acceptTerms: false
})

// Computed properties
const isDevelopment = computed(() => process.env.NODE_ENV === 'development')

const isFormValid = computed(() => {
  return form.name.trim() &&
         form.email.trim() &&
         form.password.length >= 8 &&
         form.password === form.password_confirmation &&
         form.acceptTerms
})

// Password strength calculator
const passwordStrength = computed(() => {
  const password = form.password
  if (!password) return { level: 'none', percentage: 0, label: '' }

  let score = 0
  const checks = {
    length: password.length >= 8,
    lowercase: /[a-z]/.test(password),
    uppercase: /[A-Z]/.test(password),
    numbers: /\d/.test(password),
    special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
  }

  score = Object.values(checks).filter(Boolean).length

  const levels = {
    0: { level: 'very-weak', percentage: 0, label: '' },
    1: { level: 'very-weak', percentage: 20, label: 'Molto debole' },
    2: { level: 'weak', percentage: 40, label: 'Debole' },
    3: { level: 'medium', percentage: 60, label: 'Media' },
    4: { level: 'strong', percentage: 80, label: 'Forte' },
    5: { level: 'very-strong', percentage: 100, label: 'Molto forte' }
  }

  return levels[score] || levels[0]
})

// Methods
const clearErrors = () => {
  generalError.value = ''
  Object.keys(validationErrors).forEach(key => {
    delete validationErrors[key]
  })
}

const handleRegister = async () => {
  if (isLoading.value || !isFormValid.value) return

  clearErrors()
  isLoading.value = true

  try {
    // Preparazione dati per la registrazione
    const registrationData = {
      name: form.name.trim(),
      email: form.email.trim().toLowerCase(),
      password: form.password,
      password_confirmation: form.password_confirmation
    }

    // Chiamata al store per registrazione
    const response = await authStore.register(registrationData)

    // Successo!
    successMessage.value = 'Account creato con successo! Reindirizzamento...'

    notificationStore.success(
      `Benvenuto, ${response.user.name}! Il tuo account è stato creato.`
    )

    // Breve pausa per mostrare il messaggio, poi redirect
    setTimeout(() => {
      router.push({ name: 'GameDashboard' })
    }, 1500)

  } catch (error) {
    console.error('Errore registrazione:', error)

    // Gestione errori di validazione Laravel (422)
    if (error.response?.status === 422 && error.response?.data?.errors) {
      Object.assign(validationErrors, error.response.data.errors)
      generalError.value = 'Correggi gli errori evidenziati e riprova.'
    }
    // Errore se email già registrata
    else if (error.response?.status === 422 && error.response?.data?.message) {
      generalError.value = error.response.data.message
    }
    // Altri errori server
    else if (error.response?.status >= 500) {
      generalError.value = 'Errore del server. Riprova più tardi.'
    }
    // Errori di rete
    else if (!error.response) {
      generalError.value = 'Problemi di connessione. Verifica la tua connessione internet.'
    }
    // Errore generico
    else {
      generalError.value = error.message || 'Si è verificato un errore imprevisto.'
    }

    notificationStore.error(generalError.value)
  } finally {
    isLoading.value = false
  }
}

// Reset form quando si lascia la pagina
const resetForm = () => {
  Object.assign(form, {
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    acceptTerms: false
  })
  clearErrors()
  successMessage.value = ''
}

// Cleanup
import { onUnmounted } from 'vue'
onUnmounted(() => {
  resetForm()
})
</script>

<style scoped>
.register-view {
  @apply min-h-screen bg-gradient-to-br from-brand-50 to-brand-100;
  @apply flex flex-col justify-center items-center;
  @apply px-4 py-8 relative overflow-hidden;
}

/* Page Header */
.register-header {
  @apply text-center mb-8;
}

.brand-section {
  @apply space-y-2;
}

.brand-title {
  @apply text-3xl md:text-4xl font-bold text-neutral-900;
  @apply flex items-center justify-center;
}

.brand-icon {
  @apply text-4xl md:text-5xl mr-3;
}

.brand-subtitle {
  @apply text-neutral-600 text-lg max-w-md mx-auto;
}

/* Form Container */
.register-form-container {
  @apply w-full max-w-md;
}

.register-card {
  @apply shadow-xl border-0 bg-white/95 backdrop-blur-sm;
}

.register-form-title {
  @apply text-xl font-semibold text-neutral-900;
}

/* Messages */
.success-message,
.error-message {
  @apply mb-6 p-4 rounded-lg;
}

.success-message {
  @apply bg-success-50 border border-success-200;
}

.error-message {
  @apply bg-danger-50 border border-danger-200;
}

.success-content,
.error-content {
  @apply flex items-center;
}

.success-icon,
.error-icon {
  @apply mr-2 text-lg;
}

.success-text {
  @apply text-success-800 text-sm font-medium;
}

.error-text {
  @apply text-danger-800 text-sm font-medium;
}

/* Form */
.register-form {
  @apply space-y-6;
}

.form-fields {
  @apply space-y-4;
}

/* Password Strength */
.password-strength {
  @apply space-y-2;
}

.strength-label {
  @apply text-sm font-medium text-neutral-700;
}

.strength-bar {
  @apply w-full h-2 bg-neutral-200 rounded-full overflow-hidden;
}

.strength-fill {
  @apply h-full transition-all duration-300 ease-out;
}

.strength-fill--very-weak {
  @apply bg-danger-500;
}

.strength-fill--weak {
  @apply bg-warning-500;
}

.strength-fill--medium {
  @apply bg-yellow-500;
}

.strength-fill--strong {
  @apply bg-success-500;
}

.strength-fill--very-strong {
  @apply bg-success-600;
}

.strength-text {
  @apply text-xs font-medium;
}

.strength-text--very-weak {
  @apply text-danger-600;
}

.strength-text--weak {
  @apply text-warning-600;
}

.strength-text--medium {
  @apply text-yellow-600;
}

.strength-text--strong {
  @apply text-success-600;
}

.strength-text--very-strong {
  @apply text-success-700;
}

/* Terms and Conditions */
.terms-section {
  @apply pt-2;
}

.terms-checkbox {
  @apply flex items-start cursor-pointer;
}

.terms-checkbox input[type="checkbox"] {
  @apply sr-only;
}

.checkmark {
  @apply w-5 h-5 border-2 border-neutral-300 rounded mr-3 mt-0.5;
  @apply flex items-center justify-center flex-shrink-0;
  @apply transition-all duration-200;
}

.terms-checkbox input[type="checkbox"]:checked + .checkmark {
  @apply bg-brand-600 border-brand-600;
}

.terms-checkbox input[type="checkbox"]:checked + .checkmark::after {
  content: '✓';
  @apply text-white text-sm font-bold;
}

.terms-text {
  @apply text-sm text-neutral-700 leading-relaxed;
}

.terms-link {
  @apply text-brand-600 hover:text-brand-700 font-medium;
  @apply transition-colors duration-200;
}

/* Submit Button */
.register-button {
  @apply mt-6;
}

.button-icon {
  @apply mr-2;
}

/* Login Link */
.login-link-section {
  @apply mt-6 pt-6 border-t border-neutral-200 text-center;
}

.login-text {
  @apply text-neutral-600 text-sm;
}

.login-link {
  @apply text-brand-600 hover:text-brand-700 font-medium ml-1;
  @apply transition-colors duration-200;
}

/* Development Info */
.dev-info {
  @apply mt-6 pt-4 border-t border-neutral-200;
}

.dev-details {
  @apply bg-neutral-50 rounded p-3;
}

.dev-summary {
  @apply cursor-pointer text-xs font-mono text-neutral-600;
  @apply hover:text-neutral-800 transition-colors;
}

.dev-content {
  @apply mt-2;
}

.dev-content pre {
  @apply text-xs font-mono text-neutral-700;
  @apply max-h-40 overflow-auto;
}

/* Background Elements */
.background-elements {
  @apply absolute inset-0 pointer-events-none overflow-hidden;
}

.bg-element {
  @apply absolute text-6xl opacity-10 text-brand-600;
  @apply animate-pulse;
}

.bg-element--1 {
  @apply top-20 left-10;
  animation-delay: 0s;
}

.bg-element--2 {
  @apply top-40 right-16;
  animation-delay: 1s;
}

.bg-element--3 {
  @apply bottom-32 left-20;
  animation-delay: 2s;
}

.bg-element--4 {
  @apply bottom-20 right-12;
  animation-delay: 3s;
}

/* Responsive */
@media (max-width: 640px) {
  .register-view {
    @apply px-3 py-6;
  }

  .brand-title {
    @apply text-2xl;
  }

  .brand-icon {
    @apply text-3xl mr-2;
  }

  .brand-subtitle {
    @apply text-base;
  }

  .register-form-container {
    @apply max-w-sm;
  }

  .bg-element {
    @apply text-4xl;
  }
}

/* Focus states for accessibility */
.terms-checkbox:focus-within .checkmark {
  @apply ring-2 ring-brand-500 ring-offset-2;
}

.register-form :deep(.input-field):focus {
  @apply ring-2 ring-brand-500 ring-offset-1;
}

/* Animation for success message */
.success-message {
  animation: slideInDown 0.5s ease-out;
}

@keyframes slideInDown {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Error shake animation */
.error-message {
  animation: shake 0.5s ease-in-out;
}

@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-5px); }
  75% { transform: translateX(5px); }
}

/* Loading state */
.register-card.loading {
  @apply pointer-events-none opacity-75;
}

/* Smooth transitions */
.register-form * {
  @apply transition-colors duration-200;
}
</style>