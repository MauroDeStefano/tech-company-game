<!-- src/views/auth/ResetPasswordView.vue -->
<template>
  <div class="min-h-screen bg-gradient-to-br from-sky-50 via-sky-50 to-white">
    <!-- Header -->
    <div class="text-center mb-8 pt-8">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl font-bold text-gray-900 tracking-tight mb-4">
          <span class="text-2xl">🔑</span>
          Reimposta Password
        </h1>
        <p class="text-xl text-gray-600 max-w-md mx-auto">
          Inserisci la tua nuova password per completare il reset
        </p>
      </div>
    </div>

    <!-- Reset Password Form -->
    <div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8">
      <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 p-8">
        <form @submit.prevent="handleSubmit" class="space-y-6">
          <!-- Token Info (nascosto ma utile per debug) -->
          <div v-if="showTokenInfo" class="bg-gray-50 rounded-lg p-4 border border-gray-200">
            <div class="space-y-2">
              <div class="flex items-center gap-2">
                <span class="text-sm font-semibold text-gray-700">Token:</span>
                <code class="text-xs bg-gray-100 px-2 py-1 rounded font-mono">{{ resetToken }}</code>
              </div>
              <div class="flex items-center gap-2">
                <span class="text-sm font-semibold text-gray-700">Email:</span>
                <span class="text-sm text-gray-600">{{ email }}</span>
              </div>
            </div>
          </div>

          <!-- Messaggio di errore globale -->
          <div v-if="errorMessage" class="bg-red-50 border border-red-200 rounded-xl p-4">
            <div class="flex items-start gap-3">
              <div class="text-red-400 text-lg">⚠️</div>
              <div class="flex-1">
                <div class="text-red-800 font-semibold text-sm">Errore nel reset password</div>
                <div class="text-red-700 text-sm mt-1">{{ errorMessage }}</div>
              </div>
              <button
                type="button"
                @click="clearError"
                class="text-red-400 hover:text-red-600 text-lg font-bold"
                aria-label="Chiudi errore"
              >
                ✕
              </button>
            </div>
          </div>

          <!-- Messaggio di successo -->
          <div v-if="successMessage" class="bg-green-50 border border-green-200 rounded-xl p-4">
            <div class="flex items-start gap-3">
              <div class="text-green-500 text-lg">✅</div>
              <div class="flex-1">
                <div class="text-green-800 font-semibold text-sm">Password reimpostata!</div>
                <div class="text-green-700 text-sm mt-1">{{ successMessage }}</div>
              </div>
            </div>
          </div>

          <!-- Form fields -->
          <div class="space-y-6">
            <!-- Email (readonly) -->
            <div class="space-y-2">
              <label class="block text-sm font-semibold text-gray-700">
                Indirizzo Email
              </label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <span class="text-gray-400 text-lg">📧</span>
                </div>
                <input
                  v-model="form.email"
                  type="email"
                  placeholder="example@email.com"
                  readonly
                  class="block w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl bg-gray-50 text-gray-600 cursor-not-allowed focus:outline-none"
                />
              </div>
              <p v-if="formErrors.email" class="text-red-600 text-sm font-medium">
                {{ formErrors.email }}
              </p>
            </div>

            <!-- Nuova Password -->
            <div class="space-y-2">
              <label class="block text-sm font-semibold text-gray-700">
                Nuova Password
              </label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <span class="text-gray-400 text-lg">🔒</span>
                </div>
                <input
                  v-model="form.password"
                  type="password"
                  placeholder="Inserisci la nuova password"
                  required
                  maxlength="128"
                  autocomplete="new-password"
                  :class="{
                    'border-red-300 focus:ring-red-500 focus:border-red-500': formErrors.password,
                    'border-gray-300 focus:ring-sky-500 focus:border-sky-500': !formErrors.password
                  }"
                  class="block w-full pl-10 pr-4 py-3 border rounded-xl bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors duration-200"
                />
              </div>
              <p v-if="formErrors.password" class="text-red-600 text-sm font-medium">
                {{ formErrors.password }}
              </p>
            </div>

            <!-- Conferma Password -->
            <div class="space-y-2">
              <label class="block text-sm font-semibold text-gray-700">
                Conferma Password
              </label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <span class="text-gray-400 text-lg">🔒</span>
                </div>
                <input
                  v-model="form.password_confirmation"
                  type="password"
                  placeholder="Ripeti la nuova password"
                  required
                  autocomplete="new-password"
                  :class="{
                    'border-red-300 focus:ring-red-500 focus:border-red-500': formErrors.password_confirmation,
                    'border-gray-300 focus:ring-sky-500 focus:border-sky-500': !formErrors.password_confirmation
                  }"
                  class="block w-full pl-10 pr-4 py-3 border rounded-xl bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors duration-200"
                />
              </div>
              <p v-if="formErrors.password_confirmation" class="text-red-600 text-sm font-medium">
                {{ formErrors.password_confirmation }}
              </p>
            </div>
          </div>

          <!-- Password Strength Indicator -->
          <div v-if="form.password" class="bg-gray-50 rounded-lg p-4 border border-gray-200">
            <div class="mb-3">
              <div class="text-sm font-semibold text-gray-700 mb-2">Sicurezza password:</div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div
                  class="h-2 rounded-full transition-all duration-300"
                  :class="{
                    'bg-red-500': passwordStrength.level === 'very-weak',
                    'bg-orange-500': passwordStrength.level === 'weak',
                    'bg-yellow-500': passwordStrength.level === 'medium',
                    'bg-green-500': passwordStrength.level === 'strong',
                    'bg-emerald-600': passwordStrength.level === 'very-strong'
                  }"
                  :style="{ width: `${passwordStrength.percentage}%` }"
                ></div>
              </div>
              <div 
                class="text-sm font-medium mt-1"
                :class="{
                  'text-red-600': passwordStrength.level === 'very-weak',
                  'text-orange-600': passwordStrength.level === 'weak',
                  'text-yellow-600': passwordStrength.level === 'medium',
                  'text-green-600': passwordStrength.level === 'strong',
                  'text-emerald-700': passwordStrength.level === 'very-strong'
                }"
              >
                {{ passwordStrength.text }}
              </div>
            </div>
            
            <!-- Password requirements -->
            <div class="space-y-2">
              <div class="flex items-center gap-2" :class="{ 'text-green-600': passwordChecks.length, 'text-gray-500': !passwordChecks.length }">
                <span class="text-sm">{{ passwordChecks.length ? '✅' : '❌' }}</span>
                <span class="text-sm">Almeno 8 caratteri</span>
              </div>
              <div class="flex items-center gap-2" :class="{ 'text-green-600': passwordChecks.hasUpperCase, 'text-gray-500': !passwordChecks.hasUpperCase }">
                <span class="text-sm">{{ passwordChecks.hasUpperCase ? '✅' : '❌' }}</span>
                <span class="text-sm">Una lettera maiuscola</span>
              </div>
              <div class="flex items-center gap-2" :class="{ 'text-green-600': passwordChecks.hasLowerCase, 'text-gray-500': !passwordChecks.hasLowerCase }">
                <span class="text-sm">{{ passwordChecks.hasLowerCase ? '✅' : '❌' }}</span>
                <span class="text-sm">Una lettera minuscola</span>
              </div>
              <div class="flex items-center gap-2" :class="{ 'text-green-600': passwordChecks.hasNumbers, 'text-gray-500': !passwordChecks.hasNumbers }">
                <span class="text-sm">{{ passwordChecks.hasNumbers ? '✅' : '❌' }}</span>
                <span class="text-sm">Un numero</span>
              </div>
            </div>
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="!isFormValid || isLoading"
            class="w-full py-3 px-4 bg-sky-600 hover:bg-sky-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-semibold rounded-xl transition-colors duration-200 flex items-center justify-center gap-2"
          >
            <span v-if="isLoading" class="flex items-center justify-center gap-2">
              <svg class="animate-spin w-5 h-5 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Reimpostazione in corso...
            </span>
            <span v-else>
              🔑 Reimposta Password
            </span>
          </button>

          <!-- Link di supporto -->
          <div class="space-y-4 pt-4 border-t border-gray-200">
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
              <router-link 
                to="/login" 
                class="text-sky-600 hover:text-sky-700 font-medium text-sm transition-colors duration-200"
              >
                ← Torna al Login
              </router-link>
              <router-link 
                to="/forgot-password" 
                class="text-sky-600 hover:text-sky-700 font-medium text-sm transition-colors duration-200"
              >
                Richiedi nuovo link reset
              </router-link>
            </div>
            
            <div class="text-center">
              <p class="text-sm text-gray-600">
                Non riesci a reimpostare la password? 
                <a 
                  href="mailto:support@techcompanygame.com" 
                  class="text-sky-600 hover:text-sky-700 font-medium transition-colors duration-200"
                >
                  Contatta il supporto
                </a>
              </p>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Security Notice -->
    <div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8 mt-8 mb-8">
      <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
        <div class="flex items-start gap-3">
          <div class="text-blue-500 text-lg">🔐</div>
          <div class="flex-1">
            <div class="text-blue-800 font-semibold text-sm">Nota sulla Sicurezza</div>
            <div class="text-blue-700 text-sm mt-1">
              Dopo aver reimpostato la password, verrai automaticamente disconnesso da tutti gli altri dispositivi per sicurezza.
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/js/stores/auth'
import { useNotificationStore } from '@/js/stores/notifications'
import { authService } from '@/js/services/authService'

// Stores e router
const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const notificationStore = useNotificationStore()

// Reactive state
const isLoading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const showTokenInfo = ref(false) // Solo per debug in development

// Form data
const form = reactive({
  token: '',
  email: '',
  password: '',
  password_confirmation: ''
})

// Form errors
const formErrors = reactive({
  email: '',
  password: '',
  password_confirmation: '',
  token: ''
})

// Computed properties
const resetToken = computed(() => route.query.token || route.params.token || '')
const email = computed(() => route.query.email || '')

const isFormValid = computed(() => {
  return form.password &&
         form.password_confirmation &&
         form.password === form.password_confirmation &&
         form.password.length >= 8 &&
         form.email &&
         form.token
})

// Password strength checker
const passwordChecks = computed(() => {
  const password = form.password
  return {
    length: password.length >= 8,
    hasUpperCase: /[A-Z]/.test(password),
    hasLowerCase: /[a-z]/.test(password),
    hasNumbers: /\d/.test(password),
    hasSpecialChar: /[!@#$%^&*(),.?":{}|<>]/.test(password)
  }
})

const passwordStrength = computed(() => {
  const checks = passwordChecks.value
  const score = Object.values(checks).filter(Boolean).length
  
  let level, text, percentage
  
  switch (score) {
    case 0:
    case 1:
      level = 'very-weak'
      text = 'Molto Debole'
      percentage = 20
      break
    case 2:
      level = 'weak'
      text = 'Debole'
      percentage = 40
      break
    case 3:
      level = 'medium'
      text = 'Media'
      percentage = 60
      break
    case 4:
      level = 'strong'
      text = 'Forte'
      percentage = 80
      break
    case 5:
      level = 'very-strong'
      text = 'Molto Forte'
      percentage = 100
      break
    default:
      level = 'weak'
      text = 'Debole'
      percentage = 20
  }
  
  return { level, text, percentage }
})

// Methods
const clearError = () => {
  errorMessage.value = ''
  // Clear anche gli errori specifici dei campi
  Object.keys(formErrors).forEach(key => {
    formErrors[key] = ''
  })
}

const clearSuccess = () => {
  successMessage.value = ''
}

const validateForm = () => {
  clearError()
  let isValid = true

  // Validazione email
  if (!form.email) {
    formErrors.email = 'Email richiesta'
    isValid = false
  }

  // Validazione password
  if (!form.password) {
    formErrors.password = 'Password richiesta'
    isValid = false
  } else if (form.password.length < 8) {
    formErrors.password = 'La password deve contenere almeno 8 caratteri'
    isValid = false
  }

  // Validazione conferma password
  if (!form.password_confirmation) {
    formErrors.password_confirmation = 'Conferma password richiesta'
    isValid = false
  } else if (form.password !== form.password_confirmation) {
    formErrors.password_confirmation = 'Le password non corrispondono'
    isValid = false
  }

  // Validazione token
  if (!form.token) {
    formErrors.token = 'Token di reset non valido'
    isValid = false
  }

  return isValid
}

const handleSubmit = async () => {
  if (!validateForm()) {
    return
  }

  isLoading.value = true
  clearError()
  clearSuccess()

  try {
    const resetData = {
      token: form.token,
      email: form.email,
      password: form.password,
      password_confirmation: form.password_confirmation
    }

    // Chiamata API per reset password
    await authService.resetPassword(resetData)

    // Successo
    successMessage.value = 'Password reimpostata con successo! Ora puoi effettuare il login con la nuova password.'

    // Notifica
    notificationStore.success('Password reimpostata con successo!')

    // Redirect al login dopo 3 secondi
    setTimeout(() => {
      router.push({ 
        name: 'Login',
        query: { 
          message: 'Password reimpostata con successo. Effettua il login con la nuova password.' 
        }
      })
    }, 3000)

  } catch (error) {
    console.error('Reset password error:', error)

    // Gestione errori specifici
    if (error.response?.status === 422) {
      // Errori di validazione
      const errors = error.response.data.errors
      if (errors) {
        Object.keys(errors).forEach(key => {
          if (formErrors.hasOwnProperty(key)) {
            formErrors[key] = errors[key][0] // Primo messaggio di errore
          }
        })
      }
      errorMessage.value = 'Controlla i dati inseriti e riprova.'
    } else if (error.response?.status === 400) {
      errorMessage.value = 'Token di reset non valido o scaduto. Richiedi un nuovo link di reset.'
    } else if (error.response?.status >= 500) {
      errorMessage.value = 'Errore del server. Riprova più tardi.'
    } else {
      errorMessage.value = error.message || 'Si è verificato un errore. Riprova più tardi.'
    }

    // Notifica di errore
    notificationStore.error('Errore nella reimpostazione della password')
  } finally {
    isLoading.value = false
  }
}

const initializeForm = () => {
  // Prendi token e email dai parametri URL
  form.token = resetToken.value
  form.email = email.value

  // Validazione parametri
  if (!form.token) {
    errorMessage.value = 'Token di reset mancante. Controlla il link ricevuto via email.'
    return
  }

  if (!form.email) {
    errorMessage.value = 'Email mancante. Controlla il link ricevuto via email.'
    return
  }

  // Show token info in development
  if (process.env.NODE_ENV === 'development') {
    showTokenInfo.value = true
  }
}

// Lifecycle
onMounted(() => {
  initializeForm()
})
</script>