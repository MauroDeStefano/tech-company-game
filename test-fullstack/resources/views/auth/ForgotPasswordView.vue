<template>
  <div class="forgot-password-view">
    <div class="auth-container">
      <div class="auth-card">
        <!-- Header -->
        <div class="auth-header">
          <h1 class="auth-title">Recupera Password</h1>
          <p class="auth-subtitle">
            Inserisci la tua email per ricevere le istruzioni di reset
          </p>
        </div>

        <!-- Form -->
        <form @submit.prevent="handleSubmit" class="auth-form">
          <BaseInput
            v-model="email"
            type="email"
            label="Email"
            placeholder="inserisci@email.com"
            :error-message="errors.email"
            required
            autofocus
          />

          <BaseButton
            type="submit"
            variant="primary"
            size="lg"
            :loading="loading"
            class="w-full"
          >
            Invia Istruzioni
          </BaseButton>
        </form>

        <!-- Back to Login -->
        <div class="auth-footer">
          <router-link to="/login" class="auth-link">
            ← Torna al Login
          </router-link>
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

<style scoped>
.forgot-password-view {
  @apply min-h-screen bg-neutral-50 flex items-center justify-center py-12 px-4;
}

.auth-container {
  @apply max-w-md w-full;
}

.auth-card {
  @apply bg-white rounded-lg shadow-md p-8;
}

.auth-header {
  @apply text-center mb-8;
}

.auth-title {
  @apply text-2xl font-bold text-neutral-900 mb-2;
}

.auth-subtitle {
  @apply text-neutral-600;
}

.auth-form {
  @apply space-y-6 mb-6;
}

.auth-footer {
  @apply text-center;
}

.auth-link {
  @apply text-brand-600 hover:text-brand-700 transition-colors;
}
</style>