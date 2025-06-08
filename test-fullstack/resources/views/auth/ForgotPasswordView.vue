<template>
  <div class="min-h-screen bg-gradient-to-br from-emerald-50 via-sky-50 to-white">
    <!-- Background pattern (opzionale) -->
    <div class="absolute inset-0 opacity-10">
      <div class="absolute inset-0 bg-[url('data:image/svg+xml,...')]"></div>
    </div>

    <!-- Main content -->
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header con gradient -->
      <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-gray-900 tracking-tight mb-4">
          <span class="text-2xl">🔑</span>
          Recupera Password
        </h1>
        <p class="text-xl text-gray-600 max-w-md mx-auto">
          Inserisci la tua email per ricevere le istruzioni di reset
        </p>
      </div>

      <!-- Form card -->
      <div class="max-w-md mx-auto">
        <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 p-8">
          <!-- Form -->
          <form @submit.prevent="handleSubmit" class="space-y-6">
            <!-- Email Input -->
            <div class="space-y-2">
              <label class="block text-sm font-semibold text-gray-700">
                Email
              </label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <span class="text-gray-400 text-lg">📧</span>
                </div>
                <input
                  v-model="email"
                  type="email"
                  placeholder="inserisci@email.com"
                  required
                  autofocus
                  :class="{
                    'border-red-300 focus:ring-red-500 focus:border-red-500': errors.email,
                    'border-gray-300 focus:ring-emerald-500 focus:border-emerald-500': !errors.email
                  }"
                  class="block w-full pl-10 pr-4 py-3 border rounded-xl bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors duration-200"
                />
              </div>
              <p v-if="errors.email" class="text-red-600 text-sm font-medium">
                {{ errors.email }}
              </p>
            </div>

            <!-- Submit Button -->
            <button
              type="submit"
              :disabled="loading"
              class="w-full py-3 px-4 bg-emerald-600 hover:bg-emerald-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-semibold rounded-xl transition-colors duration-200 flex items-center justify-center gap-2"
            >
              <span v-if="loading" class="flex items-center justify-center gap-2">
                <svg class="animate-spin w-5 h-5 text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Invio in corso...
              </span>
              <span v-else>
                Invia Istruzioni
              </span>
            </button>
          </form>

          <!-- Back to Login -->
          <div class="mt-6 pt-4 border-t border-gray-200">
            <div class="text-center">
              <router-link 
                to="/login" 
                class="text-emerald-600 hover:text-emerald-700 font-medium text-sm transition-colors duration-200"
              >
                ← Torna al Login
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useNotificationStore } from '@/js/stores/notifications'

// Stores
const router = useRouter()
const notificationStore = useNotificationStore()

// State
const loading = ref(false)
const email = ref('')
const errors = reactive({})

// Methods
const handleSubmit = async () => {
  // Reset errors
  Object.keys(errors).forEach(key => delete errors[key])

  // Basic validation
  if (!email.value) {
    errors.email = 'Email richiesta'
    return
  }

  loading.value = true
  try {
    // TODO: Implement forgot password API call
    await new Promise(resolve => setTimeout(resolve, 1000)) // Simulate API call

    notificationStore.success('Email di reset inviata! Controlla la tua casella di posta.')
    router.push('/login')
  } catch (error) {
    notificationStore.error('Errore nell\'invio dell\'email di reset')
  } finally {
    loading.value = false
  }
}
</script>