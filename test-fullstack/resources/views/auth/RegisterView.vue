<template>
  <div class="min-h-screen bg-gradient-to-br from-emerald-50 via-sky-50 to-white relative overflow-hidden">
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-10">
    </div>

    <!-- Floating Background Elements -->
    <div class="absolute inset-0 pointer-events-none">
      <div class="absolute top-20 left-10 text-6xl opacity-10 animate-bounce delay-1000">💼</div>
      <div class="absolute top-40 right-20 text-5xl opacity-10 animate-pulse delay-2000">🚀</div>
      <div class="absolute bottom-40 left-20 text-4xl opacity-10 animate-bounce delay-3000">💰</div>
      <div class="absolute bottom-20 right-10 text-5xl opacity-10 animate-pulse delay-4000">📊</div>
    </div>

    <div class="relative z-10 py-12 px-4 sm:px-6 lg:px-8">
      <!-- Header Section -->
      <div class="text-center mb-8">
        <div class="flex items-center justify-center gap-3 mb-4">
          <span class="text-5xl">🏢</span>
          <h1 class="text-4xl font-bold text-gray-900 tracking-tight">
            Tech Company Game
          </h1>
        </div>
        <p class="text-xl text-gray-600 max-w-md mx-auto">
          Crea il tuo account e inizia a gestire la tua software house
        </p>
      </div>

      <!-- Registration Form Container -->
      <div class="max-w-md mx-auto">
        <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 p-8">
          <!-- Card Header -->
          <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Registrazione</h2>
          </div>

          <!-- Success Message -->
          <div 
            v-if="successMessage" 
            class="mb-6 bg-green-50 border border-green-200 rounded-xl p-4 flex items-center gap-3"
          >
            <span class="text-green-500 text-xl flex-shrink-0">✅</span>
            <span class="text-green-800 font-medium text-sm">{{ successMessage }}</span>
          </div>

          <!-- Error Message -->
          <div 
            v-if="generalError" 
            class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4 flex items-start gap-3"
          >
            <span class="text-red-500 text-xl flex-shrink-0 mt-0.5">⚠️</span>
            <span class="text-red-800 font-medium text-sm">{{ generalError }}</span>
          </div>

          <!-- Registration Form -->
          <form @submit.prevent="handleRegister" class="space-y-6">
            <!-- Nome completo -->
            <div class="space-y-2">
              <label class="block text-sm font-semibold text-gray-700">
                Nome completo
              </label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <span class="text-gray-400 text-lg">👤</span>
                </div>
                <input
                  v-model="form.name"
                  type="text"
                  placeholder="Inserisci il tuo nome completo"
                  :disabled="isLoading"
                  required
                  autocomplete="name"
                  class="block w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl bg-white text-gray-900 placeholder-gray-500 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 disabled:bg-gray-50 disabled:text-gray-500 disabled:cursor-not-allowed transition-all duration-200"
                  :class="{
                    'border-red-300 focus:ring-red-500 focus:border-red-500': validationErrors.name,
                    'border-gray-300': !validationErrors.name
                  }"
                />
              </div>
              <p v-if="validationErrors.name?.[0]" class="text-red-600 text-sm font-medium">
                {{ validationErrors.name[0] }}
              </p>
            </div>

            <!-- Email -->
            <div class="space-y-2">
              <label class="block text-sm font-semibold text-gray-700">
                Indirizzo email
              </label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <span class="text-gray-400 text-lg">📧</span>
                </div>
                <input
                  v-model="form.email"
                  type="email"
                  placeholder="esempio@email.com"
                  :disabled="isLoading"
                  required
                  autocomplete="email"
                  class="block w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl bg-white text-gray-900 placeholder-gray-500 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 disabled:bg-gray-50 disabled:text-gray-500 disabled:cursor-not-allowed transition-all duration-200"
                  :class="{
                    'border-red-300 focus:ring-red-500 focus:border-red-500': validationErrors.email,
                    'border-gray-300': !validationErrors.email
                  }"
                />
              </div>
              <p v-if="validationErrors.email?.[0]" class="text-red-600 text-sm font-medium">
                {{ validationErrors.email[0] }}
              </p>
            </div>

            <!-- Password -->
            <div class="space-y-2">
              <label class="block text-sm font-semibold text-gray-700">
                Password
              </label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <span class="text-gray-400 text-lg">🔒</span>
                </div>
                <input
                  v-model="form.password"
                  type="password"
                  placeholder="Scegli una password sicura"
                  :disabled="isLoading"
                  required
                  autocomplete="new-password"
                  class="block w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl bg-white text-gray-900 placeholder-gray-500 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 disabled:bg-gray-50 disabled:text-gray-500 disabled:cursor-not-allowed transition-all duration-200"
                  :class="{
                    'border-red-300 focus:ring-red-500 focus:border-red-500': validationErrors.password,
                    'border-gray-300': !validationErrors.password
                  }"
                />
              </div>
              <p class="text-gray-500 text-xs">Minimo 8 caratteri</p>
              <p v-if="validationErrors.password?.[0]" class="text-red-600 text-sm font-medium">
                {{ validationErrors.password[0] }}
              </p>
            </div>

            <!-- Password Confirmation -->
            <div class="space-y-2">
              <label class="block text-sm font-semibold text-gray-700">
                Conferma password
              </label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <span class="text-gray-400 text-lg">🔒</span>
                </div>
                <input
                  v-model="form.password_confirmation"
                  type="password"
                  placeholder="Ripeti la password"
                  :disabled="isLoading"
                  required
                  autocomplete="new-password"
                  class="block w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl bg-white text-gray-900 placeholder-gray-500 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 disabled:bg-gray-50 disabled:text-gray-500 disabled:cursor-not-allowed transition-all duration-200"
                  :class="{
                    'border-red-300 focus:ring-red-500 focus:border-red-500': validationErrors.password_confirmation,
                    'border-gray-300': !validationErrors.password_confirmation
                  }"
                />
              </div>
              <p v-if="validationErrors.password_confirmation?.[0]" class="text-red-600 text-sm font-medium">
                {{ validationErrors.password_confirmation[0] }}
              </p>
            </div>

            <!-- Password Strength Indicator -->
            <div v-if="form.password" class="space-y-2">
              <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-700">Sicurezza password:</span>
                <span 
                  class="text-xs font-semibold"
                  :class="{
                    'text-red-600': passwordStrength.level === 'very-weak' || passwordStrength.level === 'weak',
                    'text-yellow-600': passwordStrength.level === 'medium',
                    'text-green-600': passwordStrength.level === 'strong' || passwordStrength.level === 'very-strong'
                  }"
                >
                  {{ passwordStrength.label }}
                </span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div 
                  class="h-2 rounded-full transition-all duration-300 ease-out"
                  :class="{
                    'bg-red-500': passwordStrength.level === 'very-weak' || passwordStrength.level === 'weak',
                    'bg-yellow-500': passwordStrength.level === 'medium',
                    'bg-green-500': passwordStrength.level === 'strong' || passwordStrength.level === 'very-strong'
                  }"
                  :style="{ width: `${passwordStrength.percentage}%` }"
                ></div>
              </div>
            </div>

            <!-- Terms and Conditions -->
            <div class="space-y-3">
              <label class="flex items-start gap-3 cursor-pointer">
                <input
                  type="checkbox"
                  v-model="form.acceptTerms"
                  :disabled="isLoading"
                  required
                  class="w-4 h-4 text-emerald-600 bg-gray-100 border-gray-300 rounded focus:ring-emerald-500 focus:ring-2 disabled:bg-gray-50 disabled:cursor-not-allowed mt-0.5"
                />
                <span class="text-sm text-gray-700 leading-relaxed">
                  Accetto i
                  <router-link to="/terms" class="text-emerald-600 hover:text-emerald-800 font-semibold transition-colors duration-200">
                    Termini e Condizioni
                  </router-link>
                  e la
                  <router-link to="/privacy" class="text-emerald-600 hover:text-emerald-800 font-semibold transition-colors duration-200">
                    Privacy Policy
                  </router-link>
                </span>
              </label>
            </div>

            <!-- Submit Button -->
            <button
              type="submit"
              :disabled="!isFormValid || isLoading"
              class="w-full py-3 px-4 bg-emerald-600 hover:bg-emerald-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-bold rounded-xl transition-all duration-200 transform hover:scale-105 disabled:hover:scale-100 focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 shadow-lg"
            >
              <span v-if="!isLoading" class="flex items-center justify-center gap-2">
                <span class="text-xl">🚀</span>
                Crea Account
              </span>
              <span v-else class="flex items-center justify-center gap-2">
                <svg class="animate-spin w-5 h-5 text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Creazione account...
              </span>
            </button>
          </form>

          <!-- Login Link -->
          <div class="text-center mt-6 pt-6 border-t border-gray-200">
            <p class="text-gray-600 text-sm">
              Hai già un account?
              <router-link
                to="/login"
                class="text-emerald-600 hover:text-emerald-800 font-semibold transition-colors duration-200 ml-1"
              >
                Accedi qui
              </router-link>
            </p>
          </div>

          <!-- Development Info -->
          <div v-if="isDevelopment" class="mt-6 pt-6 border-t border-gray-200">
            <details class="group">
              <summary class="flex cursor-pointer items-center gap-2 text-sm font-medium text-gray-600 hover:text-gray-800 transition-colors duration-200">
                <span>🛠️ Info Sviluppo</span>
                <svg class="w-4 h-4 transition-transform duration-200 group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
              </summary>
              <div class="mt-3 p-3 bg-gray-50 rounded-lg border">
                <pre class="text-xs text-gray-600 whitespace-pre-wrap overflow-x-auto">{{ JSON.stringify({ form, validationErrors, passwordStrength }, null, 2) }}</pre>
              </div>
            </details>
          </div>
        </div>
      </div>
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
      router.push('/game/dashboard')
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