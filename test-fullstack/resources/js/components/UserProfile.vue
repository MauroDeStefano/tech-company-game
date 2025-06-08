<template>
  <div class="max-w-4xl mx-auto p-6 space-y-8">
    <!-- Header del Profilo -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
      <div class="flex items-center space-x-6">
        <!-- Avatar Section -->
        <div class="relative">
          <div class="w-24 h-24 rounded-full overflow-hidden bg-gray-100 border-4 border-white shadow-lg">
            <img
              v-if="getAvatarUrl()"
              :src="getAvatarUrl()"
              :alt="currentUser?.name || 'Avatar'"
              class="w-full h-full object-cover"
              @error="handleAvatarError"
            />
            <div
              v-else
              class="w-full h-full flex items-center justify-center text-2xl font-bold text-indigo-600 bg-indigo-100"
            >
              {{ userInitials }}
            </div>
          </div>

          <!-- Upload Button -->
          <button
            @click="triggerFileInput"
            :disabled="uploadingAvatar"
            class="absolute bottom-0 right-0 bg-indigo-600 hover:bg-indigo-700 text-white p-2 rounded-full shadow-lg transition-colors disabled:opacity-50"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
          </button>

          <!-- Remove Avatar Button (se ha avatar personalizzato) -->
          <button
            v-if="hasCustomAvatar"
            @click="handleRemoveAvatar"
            class="absolute top-0 right-0 bg-red-600 hover:bg-red-700 text-white p-1 rounded-full shadow-lg transition-colors"
          >
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>

          <!-- Loading Spinner for Avatar Upload -->
          <div
            v-if="uploadingAvatar"
            class="absolute inset-0 bg-black bg-black/50 rounded-full flex items-center justify-center"
          >
            <svg class="animate-spin h-6 w-6 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
          </div>
        </div>

        <!-- User Info -->
        <div class="flex-1">
          <h1 class="text-3xl font-bold text-gray-900">{{ currentUser?.name || 'Nome Utente' }}</h1>
          <p class="text-gray-600">{{ currentUser?.email }}</p>
          <div class="flex items-center mt-2 space-x-4">
            <span
              v-if="currentUser?.email_verified_at"
              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"
            >
              <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              Email verificata
            </span>
            <span
              v-else
              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800"
            >
              <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
              </svg>
              Email non verificata
            </span>
          </div>
        </div>

        <!-- Stats Preview -->
        <div v-if="userStats" class="text-right">
          <div class="text-2xl font-bold text-indigo-600">{{ userStats.profile_completion }}%</div>
          <div class="text-sm text-gray-500">Profilo completato</div>
          <div class="mt-1 text-xs text-gray-400">
            Membro da {{ userStats.account_age_days }} giorni
          </div>
        </div>
      </div>
    </div>

    <!-- Hidden File Input -->
    <input
      ref="fileInput"
      type="file"
      accept="image/*"
      @change="handleFileChange"
      class="hidden"
    />

    <!-- Form di Aggiornamento Profilo -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
      <h2 class="text-xl font-semibold text-gray-900 mb-6">Informazioni Profilo</h2>

      <form @submit.prevent="handleUpdateProfile" class="space-y-6">
        <!-- Alert Errori -->
        <div v-if="error" class="bg-red-50 border border-red-200 rounded-md p-4">
          <div class="flex">
            <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
            <div class="ml-3">
              <p class="text-sm text-red-800">{{ error }}</p>
            </div>
            <button
              @click="clearError"
              class="ml-auto text-red-400 hover:text-red-600"
            >
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
          </button>
        </div>

        <!-- Alert Successo -->
        <div
          v-if="successMessage"
          class="bg-green-50 border border-green-200 rounded-md p-4"
        >
          <div class="flex">
            <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <div class="ml-3">
              <p class="text-sm text-green-800">{{ successMessage }}</p>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Nome -->
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
              Nome completo
            </label>
            <input
              id="name"
              v-model="profileForm.name"
              type="text"
              required
              :class="[
                'w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500',
                formErrors.name ? 'border-red-300' : 'border-gray-300'
              ]"
              placeholder="Inserisci il tuo nome completo"
            />
            <p v-if="formErrors.name" class="mt-1 text-sm text-red-600">
              {{ formErrors.name }}
            </p>
          </div>

          <!-- Email -->
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
              Indirizzo email
            </label>
            <input
              id="email"
              v-model="profileForm.email"
              type="email"
              required
              :class="[
                'w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500',
                formErrors.email ? 'border-red-300' : 'border-gray-300'
              ]"
              placeholder="esempio@email.com"
            />
            <p v-if="formErrors.email" class="mt-1 text-sm text-red-600">
              {{ formErrors.email }}
            </p>
          </div>
        </div>

        <!-- Sezione Cambio Password -->
        <div class="border-t pt-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Cambia Password</h3>
          <p class="text-sm text-gray-600 mb-4">
            Lascia vuoto se non vuoi cambiare la password corrente.
          </p>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Password Corrente -->
            <div>
              <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">
                Password corrente
              </label>
              <input
                id="current_password"
                v-model="profileForm.current_password"
                type="password"
                :class="[
                  'w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500',
                  formErrors.current_password ? 'border-red-300' : 'border-gray-300'
                ]"
                placeholder="Password attuale"
              />
              <p v-if="formErrors.current_password" class="mt-1 text-sm text-red-600">
                {{ formErrors.current_password }}
              </p>
            </div>

            <!-- Nuova Password -->
            <div>
              <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                Nuova password
              </label>
              <input
                id="password"
                v-model="profileForm.password"
                type="password"
                :class="[
                  'w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500',
                  formErrors.password ? 'border-red-300' : 'border-gray-300'
                ]"
                placeholder="Nuova password (min. 8 caratteri)"
              />
              <p v-if="formErrors.password" class="mt-1 text-sm text-red-600">
                {{ formErrors.password }}
              </p>
            </div>

            <!-- Conferma Password -->
            <div>
              <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                Conferma password
              </label>
              <input
                id="password_confirmation"
                v-model="profileForm.password_confirmation"
                type="password"
                :class="[
                  'w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500',
                  formErrors.password_confirmation ? 'border-red-300' : 'border-gray-300'
                ]"
                placeholder="Ripeti nuova password"
              />
              <p v-if="formErrors.password_confirmation" class="mt-1 text-sm text-red-600">
                {{ formErrors.password_confirmation }}
              </p>
            </div>
          </div>
        </div>

        <!-- Buttons -->
        <div class="flex justify-between">
          <button
            type="button"
            @click="resetForm"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          >
            Annulla modifiche
          </button>

          <button
            type="submit"
            :disabled="updating"
            class="px-6 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="updating" class="flex items-center">
              <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Salvando...
            </span>
            <span v-else>Salva modifiche</span>
          </button>
        </div>
      </form>
    </div>

    <!-- Sezione Statistiche Dettagliate -->
    <div v-if="userStats" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
      <h2 class="text-xl font-semibold text-gray-900 mb-6">Statistiche Account</h2>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="text-center p-4 bg-gray-50 rounded-lg">
          <div class="text-2xl font-bold text-indigo-600">{{ userStats.profile_completion }}%</div>
          <div class="text-sm text-gray-600">Profilo completato</div>
        </div>

        <div class="text-center p-4 bg-gray-50 rounded-lg">
          <div class="text-2xl font-bold text-green-600">{{ userStats.account_age_days }}</div>
          <div class="text-sm text-gray-600">Giorni da membro</div>
        </div>

        <div class="text-center p-4 bg-gray-50 rounded-lg">
          <div class="text-2xl font-bold" :class="userStats.email_verified ? 'text-green-600' : 'text-red-600'">
            {{ userStats.email_verified ? '✓' : '✗' }}
          </div>
          <div class="text-sm text-gray-600">Email verificata</div>
        </div>
      </div>

      <div class="mt-4 text-sm text-gray-500">
        <p v-if="userStats.last_profile_update">
          Ultimo aggiornamento: {{ userStats.last_profile_update }}
        </p>
        <p v-else>
          Profilo mai aggiornato
        </p>
      </div>
    </div>

    <!-- Sezione Eliminazione Account -->
    <div class="bg-white rounded-lg shadow-sm border border-red-200 p-6">
      <h2 class="text-xl font-semibold text-red-900 mb-4">Zona Pericolosa</h2>
      <p class="text-gray-600 mb-4">
        L'eliminazione dell'account è permanente e non può essere annullata.
        Tutti i tuoi dati verranno rimossi definitivamente.
      </p>

      <button
        @click="showDeleteConfirmation = true"
        class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
      >
        Elimina Account
      </button>
    </div>

    <!-- Modal Conferma Eliminazione -->
    <div
      v-if="showDeleteConfirmation"
      class="fixed inset-0 bg-gray-600 bg-black/50 overflow-y-auto h-full w-full z-50"
      @click="showDeleteConfirmation = false"
    >
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" @click.stop>
        <div class="mt-3 text-center">
          <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
          </div>
          <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">Elimina Account</h3>
          <div class="mt-2 px-7 py-3">
            <p class="text-sm text-gray-500">
              Sei sicuro di voler eliminare il tuo account? Questa azione non può essere annullata.
            </p>
          </div>
          <div class="items-center px-4 py-3">
            <button
              @click="handleDeleteAccount"
              :disabled="loading"
              class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 disabled:opacity-50 mr-2"
            >
              <span v-if="loading">Eliminando...</span>
              <span v-else>Sì, elimina</span>
            </button>
            <button
              @click="showDeleteConfirmation = false"
              class="mt-3 px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500"
            >
              Annulla
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, reactive, computed } from 'vue'
import { useUser } from '@/js/composables/useUser'
import { useRouter } from 'vue-router'

const router = useRouter()

// Composable per gestione utente
const {
  loading,
  updating,
  uploadingAvatar,
  error,
  userStats,
  loadingStats,
  profileForm,
  currentUser,
  hasCustomAvatar,
  userInitials,
  fetchProfile,
  updateProfile,
  uploadAvatar,
  removeAvatar,
  fetchUserStats,
  deleteAccount,
  resetForm,
  validateForm,
  validateAvatarFile,
  getAvatarUrl,
  clearError,
} = useUser()

// State locale del componente
const fileInput = ref(null)
const successMessage = ref('')
const showDeleteConfirmation = ref(false)
const formErrors = reactive({})

// Computed
const isFormValid = computed(() => {
  const validation = validateForm()
  return validation.isValid
})

// Methods
function triggerFileInput() {
  fileInput.value?.click()
}

async function handleFileChange(event) {
  const file = event.target.files[0]
  if (!file) return

  const validation = validateAvatarFile(file)
  if (!validation.isValid) {
    error.value = validation.error
    return
  }

  try {
    await uploadAvatar(file)
    successMessage.value = 'Avatar aggiornato con successo!'
    setTimeout(() => {
      successMessage.value = ''
    }, 3000)
  } catch (err) {
    console.error('Errore upload avatar:', err)
  }

  // Reset input file
  event.target.value = ''
}

async function handleRemoveAvatar() {
  try {
    await removeAvatar()
    successMessage.value = 'Avatar rimosso con successo!'
    setTimeout(() => {
      successMessage.value = ''
    }, 3000)
  } catch (err) {
    console.error('Errore rimozione avatar:', err)
  }
}

async function handleUpdateProfile() {
  // Reset errori precedenti
  Object.keys(formErrors).forEach(key => {
    delete formErrors[key]
  })

  // Validazione lato client
  const validation = validateForm()
  if (!validation.isValid) {
    Object.assign(formErrors, validation.errors)
    return
  }

  try {
    await updateProfile()
    successMessage.value = 'Profilo aggiornato con successo!'
    setTimeout(() => {
      successMessage.value = ''
    }, 3000)
  } catch (err) {
    // Gli errori sono gestiti nel composable
    if (err.response?.data?.errors) {
      Object.assign(formErrors, err.response.data.errors)
    }
  }
}

async function handleDeleteAccount() {
  try {
    await deleteAccount()
    // Il composable gestisce il logout automatico
    router.push('/login')
  } catch (err) {
    console.error('Errore eliminazione account:', err)
    showDeleteConfirmation.value = false
  }
}

function handleAvatarError() {
  // Fallback in caso di errore caricamento immagine
  console.warn('Errore caricamento avatar, usando fallback')
}

// Lifecycle
onMounted(async () => {
  try {
    await fetchProfile()
    await fetchUserStats()
  } catch (err) {
    console.error('Errore inizializzazione profilo:', err)
  }
})
</script>