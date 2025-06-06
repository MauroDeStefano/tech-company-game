<template>
  <div class="profile-view">
    <!-- Page Header -->
    <div class="page-header">
      <div class="header-content">
        <h1 class="page-title">
          <span class="title-icon">👤</span>
          Il Tuo Profilo
        </h1>
        <p class="page-subtitle">
          Gestisci le informazioni del tuo account e le preferenze
        </p>
      </div>

      <div class="header-actions">
        <BaseButton
          variant="outline"
          icon="⚙️"
          @click="goToSettings"
        >
          Impostazioni
        </BaseButton>
      </div>
    </div>

    <!-- Profile Content -->
    <div class="profile-content">
      <!-- User Profile Card -->
      <div class="profile-section">
        <BaseCard
          title="Informazioni Profilo"
          icon="👤"
          class="profile-card"
        >
          <div class="profile-info">
            <!-- Avatar Section -->
            <div class="avatar-section">
              <div class="avatar-container">
                <div class="avatar" :class="{ 'avatar--loading': uploadingAvatar }">
                  <img
                    v-if="user?.avatar_url"
                    :src="user.avatar_url"
                    :alt="user.name"
                    class="avatar-image"
                    @error="handleAvatarError"
                  />
                  <div v-else class="avatar-fallback">
                    {{ userInitials }}
                  </div>

                  <!-- Loading overlay -->
                  <div v-if="uploadingAvatar" class="avatar-loading">
                    <div class="loading-spinner">
                      <svg class="animate-spin h-6 w-6 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                      </svg>
                    </div>
                  </div>
                </div>

                <!-- Avatar Actions -->
                <div class="avatar-actions">
                  <BaseButton
                    size="sm"
                    variant="outline"
                    icon="📷"
                    @click="triggerFileInput"
                    :disabled="uploadingAvatar"
                  >
                    Cambia
                  </BaseButton>

                  <BaseButton
                    v-if="user?.avatar_url"
                    size="sm"
                    variant="ghost"
                    icon="🗑️"
                    @click="removeAvatar"
                    :disabled="uploadingAvatar"
                  >
                    Rimuovi
                  </BaseButton>
                </div>
              </div>

              <!-- Hidden file input -->
              <input
                ref="fileInput"
                type="file"
                accept="image/*"
                @change="handleFileChange"
                class="hidden"
              />
            </div>

            <!-- User Details -->
            <div class="user-details">
              <div class="user-main">
                <h2 class="user-name">{{ user?.name || 'Nome Utente' }}</h2>
                <p class="user-email">{{ user?.email }}</p>

                <!-- Verification Status -->
                <div class="verification-status">
                  <span
                    v-if="user?.email_verified_at"
                    class="status-badge status-badge--verified"
                  >
                    ✅ Email Verificata
                  </span>
                  <span
                    v-else
                    class="status-badge status-badge--unverified"
                  >
                    ⚠️ Email Non Verificata
                  </span>
                </div>
              </div>

              <!-- User Stats -->
              <div class="user-stats">
                <div class="stat-item">
                  <span class="stat-value">{{ userStats.accountAge }}</span>
                  <span class="stat-label">Giorni come membro</span>
                </div>

                <div class="stat-item">
                  <span class="stat-value">{{ userStats.totalGames }}</span>
                  <span class="stat-label">Partite create</span>
                </div>

                <div class="stat-item">
                  <span class="stat-value">{{ userStats.hoursPlayed }}h</span>
                  <span class="stat-label">Ore di gioco</span>
                </div>
              </div>
            </div>
          </div>
        </BaseCard>
      </div>

      <!-- Edit Profile Form -->
      <div class="profile-section">
        <BaseCard
          title="Modifica Profilo"
          icon="✏️"
          class="edit-card"
        >
          <!-- Success Message -->
          <div v-if="successMessage" class="success-alert">
            <span class="success-icon">✅</span>
            <span class="success-text">{{ successMessage }}</span>
            <button @click="successMessage = ''" class="success-dismiss">✕</button>
          </div>

          <!-- Error Message -->
          <div v-if="errorMessage" class="error-alert">
            <span class="error-icon">⚠️</span>
            <span class="error-text">{{ errorMessage }}</span>
            <button @click="errorMessage = ''" class="error-dismiss">✕</button>
          </div>

          <form @submit.prevent="handleUpdateProfile" class="profile-form">
            <!-- Name Field -->
            <BaseInput
              v-model="profileForm.name"
              label="Nome Completo"
              placeholder="Il tuo nome completo"
              :error-message="validationErrors.name"
              :disabled="isUpdating"
              required
              left-icon="👤"
            />

            <!-- Email Field -->
            <BaseInput
              v-model="profileForm.email"
              type="email"
              label="Email"
              placeholder="tua@email.com"
              :error-message="validationErrors.email"
              :disabled="isUpdating"
              required
              left-icon="✉️"
            />

            <!-- Bio Field -->
            <BaseInput
              v-model="profileForm.bio"
              input-type="textarea"
              label="Bio (Opzionale)"
              placeholder="Raccontaci qualcosa di te..."
              :error-message="validationErrors.bio"
              :disabled="isUpdating"
              :rows="3"
              :maxlength="500"
              :show-counter="true"
            />

            <!-- Form Actions -->
            <div class="form-actions">
              <BaseButton
                type="button"
                variant="outline"
                @click="resetForm"
                :disabled="isUpdating"
              >
                Annulla
              </BaseButton>

              <BaseButton
                type="submit"
                variant="primary"
                :loading="isUpdating"
                :disabled="!isFormChanged"
              >
                Salva Modifiche
              </BaseButton>
            </div>
          </form>
        </BaseCard>
      </div>

      <!-- Change Password Section -->
      <div class="profile-section">
        <BaseCard
          title="Cambia Password"
          icon="🔒"
          class="password-card"
        >
          <form @submit.prevent="handleChangePassword" class="password-form">
            <!-- Current Password -->
            <BaseInput
              v-model="passwordForm.currentPassword"
              type="password"
              label="Password Attuale"
              placeholder="Inserisci la password attuale"
              :error-message="passwordErrors.currentPassword"
              :disabled="isChangingPassword"
              required
              left-icon="🔒"
            />

            <!-- New Password -->
            <BaseInput
              v-model="passwordForm.newPassword"
              type="password"
              label="Nuova Password"
              placeholder="Inserisci la nuova password"
              :error-message="passwordErrors.newPassword"
              :disabled="isChangingPassword"
              required
              left-icon="🆕"
            />

            <!-- Confirm Password -->
            <BaseInput
              v-model="passwordForm.confirmPassword"
              type="password"
              label="Conferma Password"
              placeholder="Ripeti la nuova password"
              :error-message="passwordErrors.confirmPassword"
              :disabled="isChangingPassword"
              required
              left-icon="✅"
            />

            <!-- Password Strength Indicator -->
            <div v-if="passwordForm.newPassword" class="password-strength">
              <div class="strength-label">Forza Password:</div>
              <div class="strength-bar">
                <div
                  class="strength-fill"
                  :class="`strength-fill--${passwordStrength.level}`"
                  :style="{ width: `${passwordStrength.percentage}%` }"
                ></div>
              </div>
              <div class="strength-text">{{ passwordStrength.text }}</div>
            </div>

            <!-- Password Form Actions -->
            <div class="form-actions">
              <BaseButton
                type="button"
                variant="outline"
                @click="resetPasswordForm"
                :disabled="isChangingPassword"
              >
                Annulla
              </BaseButton>

              <BaseButton
                type="submit"
                variant="primary"
                :loading="isChangingPassword"
                :disabled="!isPasswordFormValid"
              >
                Cambia Password
              </BaseButton>
            </div>
          </form>
        </BaseCard>
      </div>

      <!-- Account Stats -->
      <div class="profile-section">
        <BaseCard
          title="Statistiche Account"
          icon="📊"
          class="stats-card"
        >
          <div class="stats-grid">
            <div class="stat-card">
              <div class="stat-header">
                <span class="stat-icon">🎮</span>
                <span class="stat-title">Gaming</span>
              </div>
              <div class="stat-content">
                <div class="stat-row">
                  <span class="stat-label">Partite Totali</span>
                  <span class="stat-value">{{ userStats.totalGames }}</span>
                </div>
                <div class="stat-row">
                  <span class="stat-label">Partite Completate</span>
                  <span class="stat-value">{{ userStats.completedGames }}</span>
                </div>
                <div class="stat-row">
                  <span class="stat-label">Miglior Patrimonio</span>
                  <span class="stat-value">{{ formatCurrency(userStats.bestMoney) }}</span>
                </div>
              </div>
            </div>

            <div class="stat-card">
              <div class="stat-header">
                <span class="stat-icon">📈</span>
                <span class="stat-title">Progressi</span>
              </div>
              <div class="stat-content">
                <div class="stat-row">
                  <span class="stat-label">Livello Utente</span>
                  <span class="stat-value">{{ userStats.userLevel }}</span>
                </div>
                <div class="stat-row">
                  <span class="stat-label">Punti Esperienza</span>
                  <span class="stat-value">{{ userStats.experiencePoints }}</span>
                </div>
                <div class="stat-row">
                  <span class="stat-label">Achievement</span>
                  <span class="stat-value">{{ userStats.achievements }}</span>
                </div>
              </div>
            </div>
          </div>
        </BaseCard>
      </div>

      <!-- Danger Zone -->
      <div class="profile-section">
        <BaseCard
          title="Zona Pericolosa"
          icon="⚠️"
          variant="danger"
          class="danger-card"
        >
          <div class="danger-content">
            <p class="danger-description">
              Le azioni in questa sezione sono irreversibili. Procedi con cautela.
            </p>

            <div class="danger-actions">
              <BaseButton
                variant="outline"
                @click="showDeleteModal = true"
                class="danger-button"
              >
                🗑️ Elimina Account
              </BaseButton>
            </div>
          </div>
        </BaseCard>
      </div>
    </div>

    <!-- Delete Account Modal -->
    <BaseModal
      :is-open="showDeleteModal"
      title="Elimina Account"
      variant="danger"
      @close="showDeleteModal = false"
      @confirm="handleDeleteAccount"
      confirm-text="Elimina Account"
      confirm-variant="danger"
      :loading="isDeletingAccount"
    >
      <div class="delete-modal-content">
        <div class="delete-warning">
          <span class="warning-icon">⚠️</span>
          <div class="warning-text">
            <h4>Attenzione: Azione Irreversibile</h4>
            <p>Eliminando il tuo account:</p>
            <ul class="warning-list">
              <li>Tutte le tue partite verranno eliminate definitivamente</li>
              <li>I tuoi progressi e statistiche andranno persi</li>
              <li>Non potrai più accedere a questo account</li>
              <li>Questa azione non può essere annullata</li>
            </ul>
          </div>
        </div>

        <BaseInput
          v-model="deleteConfirmation"
          label="Per confermare, scrivi 'ELIMINA' qui sotto:"
          placeholder="ELIMINA"
          :error-message="deleteError"
        />
      </div>
    </BaseModal>
  </div>
</template>

<script setup>
import { ref, computed, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/js/stores/auth'
import { useNotificationStore } from '@/js/stores/notifications'
import { formatCurrency } from '@/js/utils/helpers'

// Stores
const authStore = useAuthStore()
const notificationStore = useNotificationStore()
const router = useRouter()

// Reactive state
const isUpdating = ref(false)
const isChangingPassword = ref(false)
const isDeletingAccount = ref(false)
const uploadingAvatar = ref(false)
const successMessage = ref('')
const errorMessage = ref('')
const showDeleteModal = ref(false)
const deleteConfirmation = ref('')
const deleteError = ref('')
const fileInput = ref(null)

// Forms
const profileForm = reactive({
  name: '',
  email: '',
  bio: ''
})

const passwordForm = reactive({
  currentPassword: '',
  newPassword: '',
  confirmPassword: ''
})

const validationErrors = reactive({
  name: '',
  email: '',
  bio: ''
})

const passwordErrors = reactive({
  currentPassword: '',
  newPassword: '',
  confirmPassword: ''
})

// Mock user stats (in realtà verrebbero dall'API)
const userStats = reactive({
  accountAge: 45,
  totalGames: 12,
  completedGames: 8,
  hoursPlayed: 87,
  bestMoney: 45000,
  userLevel: 5,
  experiencePoints: 2340,
  achievements: 15
})

// Computed
const user = computed(() => authStore.user)

const userInitials = computed(() => {
  if (!user.value?.name) return 'U'
  return user.value.name
    .split(' ')
    .map(n => n.charAt(0))
    .slice(0, 2)
    .join('')
    .toUpperCase()
})

const isFormChanged = computed(() => {
  if (!user.value) return false
  return profileForm.name !== user.value.name ||
         profileForm.email !== user.value.email ||
         profileForm.bio !== (user.value.bio || '')
})

const isPasswordFormValid = computed(() => {
  return passwordForm.currentPassword &&
         passwordForm.newPassword &&
         passwordForm.confirmPassword &&
         passwordForm.newPassword === passwordForm.confirmPassword &&
         passwordForm.newPassword.length >= 8
})

const passwordStrength = computed(() => {
  const password = passwordForm.newPassword
  if (!password) return { level: 'none', percentage: 0, text: '' }

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
    0: { level: 'none', percentage: 0, text: '' },
    1: { level: 'weak', percentage: 20, text: 'Molto Debole' },
    2: { level: 'weak', percentage: 40, text: 'Debole' },
    3: { level: 'medium', percentage: 60, text: 'Media' },
    4: { level: 'strong', percentage: 80, text: 'Forte' },
    5: { level: 'strong', percentage: 100, text: 'Molto Forte' }
  }

  return levels[score]
})

// Methods
const populateForm = () => {
  if (user.value) {
    profileForm.name = user.value.name || ''
    profileForm.email = user.value.email || ''
    profileForm.bio = user.value.bio || ''
  }
}

const resetForm = () => {
  populateForm()
  clearValidationErrors()
  clearMessages()
}

const resetPasswordForm = () => {
  passwordForm.currentPassword = ''
  passwordForm.newPassword = ''
  passwordForm.confirmPassword = ''
  clearPasswordErrors()
}

const clearValidationErrors = () => {
  Object.keys(validationErrors).forEach(key => {
    validationErrors[key] = ''
  })
}

const clearPasswordErrors = () => {
  Object.keys(passwordErrors).forEach(key => {
    passwordErrors[key] = ''
  })
}

const clearMessages = () => {
  successMessage.value = ''
  errorMessage.value = ''
}

const handleUpdateProfile = async () => {
  clearValidationErrors()
  clearMessages()

  isUpdating.value = true
  try {
    await authStore.updateProfile({
      name: profileForm.name,
      email: profileForm.email,
      bio: profileForm.bio
    })

    successMessage.value = 'Profilo aggiornato con successo!'
    setTimeout(() => {
      successMessage.value = ''
    }, 5000)

  } catch (error) {
    if (error.validationErrors) {
      Object.assign(validationErrors, error.validationErrors)
    } else {
      errorMessage.value = error.message || 'Errore nell\'aggiornamento del profilo'
    }
  } finally {
    isUpdating.value = false
  }
}

const handleChangePassword = async () => {
  clearPasswordErrors()
  clearMessages()

  // Client-side validation
  if (passwordForm.newPassword !== passwordForm.confirmPassword) {
    passwordErrors.confirmPassword = 'Le password non corrispondono'
    return
  }

  if (passwordForm.newPassword.length < 8) {
    passwordErrors.newPassword = 'La password deve essere di almeno 8 caratteri'
    return
  }

  isChangingPassword.value = true
  try {
    await authStore.changePassword({
      currentPassword: passwordForm.currentPassword,
      newPassword: passwordForm.newPassword,
      passwordConfirmation: passwordForm.confirmPassword
    })

    successMessage.value = 'Password cambiata con successo!'
    resetPasswordForm()

    setTimeout(() => {
      successMessage.value = ''
    }, 5000)

  } catch (error) {
    if (error.validationErrors) {
      Object.assign(passwordErrors, error.validationErrors)
    } else {
      errorMessage.value = error.message || 'Errore nel cambio password'
    }
  } finally {
    isChangingPassword.value = false
  }
}

const triggerFileInput = () => {
  fileInput.value?.click()
}

const handleFileChange = async (event) => {
  const file = event.target.files[0]
  if (!file) return

  // Validate file
  if (!file.type.startsWith('image/')) {
    errorMessage.value = 'Seleziona un file immagine valido'
    return
  }

  if (file.size > 5 * 1024 * 1024) { // 5MB
    errorMessage.value = 'L\'immagine deve essere più piccola di 5MB'
    return
  }

  uploadingAvatar.value = true
  try {
    // Simula upload (in realtà useresti authService.uploadAvatar)
    await new Promise(resolve => setTimeout(resolve, 2000))
    successMessage.value = 'Avatar aggiornato con successo!'

    setTimeout(() => {
      successMessage.value = ''
    }, 3000)

  } catch (error) {
    errorMessage.value = 'Errore nell\'upload dell\'avatar'
  } finally {
    uploadingAvatar.value = false
    event.target.value = '' // Reset input
  }
}

const removeAvatar = async () => {
  uploadingAvatar.value = true
  try {
    // Simula rimozione avatar
    await new Promise(resolve => setTimeout(resolve, 1000))
    successMessage.value = 'Avatar rimosso con successo!'

    setTimeout(() => {
      successMessage.value = ''
    }, 3000)

  } catch (error) {
    errorMessage.value = 'Errore nella rimozione dell\'avatar'
  } finally {
    uploadingAvatar.value = false
  }
}

const handleAvatarError = () => {
  console.warn('Errore caricamento avatar')
}

const handleDeleteAccount = async () => {
  deleteError.value = ''

  if (deleteConfirmation.value !== 'ELIMINA') {
    deleteError.value = 'Devi scrivere "ELIMINA" per confermare'
    return
  }

  isDeletingAccount.value = true
  try {
    await authStore.deleteAccount()
    notificationStore.success('Account eliminato. Ci dispiace vederti andare!')
    router.push('/welcome')

  } catch (error) {
    deleteError.value = error.message || 'Errore nell\'eliminazione dell\'account'
  } finally {
    isDeletingAccount.value = false
  }
}

const goToSettings = () => {
  router.push({ name: 'Settings' })
}

// Lifecycle
onMounted(() => {
  populateForm()
})
</script>

<style scoped>
.profile-view {
  @apply max-w-4xl mx-auto p-6 space-y-8;
}

/* Page Header */
.page-header {
  @apply flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8;
}

.header-content {
  @apply mb-4 sm:mb-0;
}

.page-title {
  @apply text-3xl font-bold text-neutral-900 flex items-center;
}

.title-icon {
  @apply text-4xl mr-3;
}

.page-subtitle {
  @apply text-neutral-600 mt-1;
}

.header-actions {
  @apply flex items-center space-x-3;
}

/* Profile Content */
.profile-content {
  @apply space-y-8;
}

.profile-section {
  @apply relative;
}

/* Profile Info */
.profile-info {
  @apply flex flex-col lg:flex-row lg:items-start lg:space-x-8 space-y-6 lg:space-y-0;
}

/* Avatar Section */
.avatar-section {
  @apply flex flex-col items-center lg:items-start;
}

.avatar-container {
  @apply space-y-4;
}

.avatar {
  @apply relative w-24 h-24 rounded-full overflow-hidden;
  @apply border-4 border-white shadow-lg;
}

.avatar--loading {
  @apply pointer-events-none;
}

.avatar-image {
  @apply w-full h-full object-cover;
}

.avatar-fallback {
  @apply w-full h-full flex items-center justify-center;
  @apply bg-brand-600 text-white text-2xl font-bold;
}

.avatar-loading {
  @apply absolute inset-0 bg-black bg-opacity-50;
  @apply flex items-center justify-center;
}

.avatar-actions {
  @apply flex space-x-2;
}

/* User Details */
.user-details {
  @apply flex-1 space-y-6;
}

.user-main {
  @apply space-y-2;
}

.user-name {
  @apply text-2xl font-bold text-neutral-900;
}

.user-email {
  @apply text-neutral-600;
}

.verification-status {
  @apply mt-2;
}

.status-badge {
  @apply inline-flex items-center px-3 py-1 rounded-full text-sm font-medium;
}

.status-badge--verified {
  @apply bg-success-100 text-success-800;
}

.status-badge--unverified {
  @apply bg-warning-100 text-warning-800;
}

/* User Stats */
.user-stats {
  @apply grid grid-cols-3 gap-4;
}

.stat-item {
  @apply text-center;
}

.stat-value {
  @apply block text-2xl font-bold text-brand-600;
}

.stat-label {
  @apply text-sm text-neutral-600;
}

/* Forms */
.profile-form,
.password-form {
  @apply space-y-6;
}

.form-actions {
  @apply flex justify-end space-x-3 pt-4 border-t border-neutral-200;
}

/* Alerts */
.success-alert,
.error-alert {
  @apply flex items-center justify-between p-4 rounded-lg mb-6;
}

.success-alert {
  @apply bg-success-50 text-success-800 border border-success-200;
}

.error-alert {
  @apply bg-danger-50 text-danger-800 border border-danger-200;
}

.success-icon,
.error-icon {
  @apply mr-3;
}

.success-text,
.error-text {
  @apply flex-1 text-sm;
}

.success-dismiss,
.error-dismiss {
  @apply ml-3 text-current opacity-50 hover:opacity-100;
  @apply transition-opacity duration-200;
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

.strength-fill--weak {
  @apply bg-danger-500;
}

.strength-fill--medium {
  @apply bg-warning-500;
}

.strength-fill--strong {
  @apply bg-success-500;
}

.strength-text {
  @apply text-sm text-neutral-600;
}

/* Stats Grid */
.stats-grid {
  @apply grid grid-cols-1 md:grid-cols-2 gap-6;
}

.stat-card {
  @apply bg-neutral-50 rounded-lg p-4;
}

.stat-header {
  @apply flex items-center mb-4;
}

.stat-icon {
  @apply text-2xl mr-3;
}

.stat-title {
  @apply font-semibold text-neutral-900;
}

.stat-content {
  @apply space-y-2;
}

.stat-row {
  @apply flex justify-between items-center;
}

.stat-label {
  @apply text-sm text-neutral-600;
}

.stat-value {
  @apply font-semibold text-neutral-900;
}

/* Danger Zone */
.danger-content {
  @apply space-y-4;
}

.danger-description {
  @apply text-danger-700;
}

.danger-actions {
  @apply flex justify-start;
}

.danger-button {
  @apply border-danger-300 text-danger-700 hover:bg-danger-50;
}

/* Delete Modal */
.delete-modal-content {
  @apply space-y-6;
}

.delete-warning {
  @apply flex space-x-4;
}

.warning-icon {
  @apply text-4xl text-danger-500 flex-shrink-0;
}

.warning-text h4 {
  @apply font-semibold text-danger-900 mb-2;
}

.warning-text p {
  @apply text-danger-700 mb-2;
}

.warning-list {
  @apply list-disc list-inside space-y-1 text-sm text-danger-600;
}

/* Responsive */
@media (max-width: 640px) {
  .profile-view {
    @apply p-4 space-y-6;
  }

  .page-title {
    @apply text-2xl;
  }

  .title-icon {
    @apply text-3xl mr-2;
  }

  .user-stats {
    @apply grid-cols-1 gap-3;
  }

  .stats-grid {
    @apply grid-cols-1;
  }

  .form-actions {
    @apply flex-col space-y-2 space-x-0;
  }
}
</style>