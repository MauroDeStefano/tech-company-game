<template>
  <div class="settings-view">
    <!-- Page Header -->
    <div class="page-header">
      <div class="header-content">
        <h1 class="page-title">
          <span class="title-icon">⚙️</span>
          Impostazioni
        </h1>
        <p class="page-subtitle">
          Personalizza la tua esperienza di gioco e gestisci le preferenze dell'account
        </p>
      </div>
    </div>

    <!-- Settings Content -->
    <div class="settings-content">
      <!-- Game Settings -->
      <section class="settings-section">
        <BaseCard
          title="Impostazioni di Gioco"
          icon="🎮"
          class="settings-card"
        >
          <div class="settings-grid">
            <!-- Auto-save Settings -->
            <div class="setting-item">
              <div class="setting-info">
                <label class="setting-label" for="auto-save">Salvataggio Automatico</label>
                <p class="setting-description">
                  Salva automaticamente i progressi ogni 30 secondi
                </p>
              </div>
              <div class="setting-control">
                <label class="toggle-switch">
                  <input
                    id="auto-save"
                    v-model="gameSettings.autoSave"
                    type="checkbox"
                    @change="updateGameSettings"
                  />
                  <span class="toggle-slider"></span>
                </label>
              </div>
            </div>

            <!-- Notifications -->
            <div class="setting-item">
              <div class="setting-info">
                <label class="setting-label" for="notifications">Notifiche di Gioco</label>
                <p class="setting-description">
                  Ricevi notifiche quando progetti e generazioni sono completati
                </p>
              </div>
              <div class="setting-control">
                <label class="toggle-switch">
                  <input
                    id="notifications"
                    v-model="gameSettings.notifications"
                    type="checkbox"
                    @change="updateGameSettings"
                  />
                  <span class="toggle-slider"></span>
                </label>
              </div>
            </div>

            <!-- Sound Effects -->
            <div class="setting-item">
              <div class="setting-info">
                <label class="setting-label" for="sound-effects">Effetti Sonori</label>
                <p class="setting-description">
                  Abilita suoni per azioni e completamenti
                </p>
              </div>
              <div class="setting-control">
                <label class="toggle-switch">
                  <input
                    id="sound-effects"
                    v-model="gameSettings.soundEffects"
                    type="checkbox"
                    @change="updateGameSettings"
                  />
                  <span class="toggle-slider"></span>
                </label>
              </div>
            </div>

            <!-- Animation Speed -->
            <div class="setting-item">
              <div class="setting-info">
                <label class="setting-label" for="animation-speed">Velocità Animazioni</label>
                <p class="setting-description">
                  Controlla la velocità delle animazioni nell'interfaccia
                </p>
              </div>
              <div class="setting-control">
                <select
                  id="animation-speed"
                  v-model="gameSettings.animationSpeed"
                  class="setting-select"
                  @change="updateGameSettings"
                >
                  <option value="slow">Lenta</option>
                  <option value="normal">Normale</option>
                  <option value="fast">Veloce</option>
                  <option value="disabled">Disabilitate</option>
                </select>
              </div>
            </div>

            <!-- Difficulty Level -->
            <div class="setting-item">
              <div class="setting-info">
                <label class="setting-label" for="difficulty">Livello Difficoltà</label>
                <p class="setting-description">
                  Modifica la difficoltà del gioco (richiede riavvio partita)
                </p>
              </div>
              <div class="setting-control">
                <select
                  id="difficulty"
                  v-model="gameSettings.difficulty"
                  class="setting-select"
                  @change="updateGameSettings"
                >
                  <option value="easy">Facile</option>
                  <option value="normal">Normale</option>
                  <option value="hard">Difficile</option>
                  <option value="expert">Esperto</option>
                </select>
              </div>
            </div>

            <!-- Timer Display -->
            <div class="setting-item">
              <div class="setting-info">
                <label class="setting-label" for="timer-display">Mostra Timer Dettagliati</label>
                <p class="setting-description">
                  Visualizza timer precisi per progetti e generazioni
                </p>
              </div>
              <div class="setting-control">
                <label class="toggle-switch">
                  <input
                    id="timer-display"
                    v-model="gameSettings.detailedTimers"
                    type="checkbox"
                    @change="updateGameSettings"
                  />
                  <span class="toggle-slider"></span>
                </label>
              </div>
            </div>
          </div>
        </BaseCard>
      </section>

      <!-- Interface Settings -->
      <section class="settings-section">
        <BaseCard
          title="Interfaccia e Visualizzazione"
          icon="🎨"
          class="settings-card"
        >
          <div class="settings-grid">
            <!-- Theme -->
            <div class="setting-item">
              <div class="setting-info">
                <label class="setting-label" for="theme">Tema</label>
                <p class="setting-description">
                  Scegli il tema dell'interfaccia
                </p>
              </div>
              <div class="setting-control">
                <select
                  id="theme"
                  v-model="interfaceSettings.theme"
                  class="setting-select"
                  @change="updateInterfaceSettings"
                >
                  <option value="light">Chiaro</option>
                  <option value="dark">Scuro</option>
                  <option value="auto">Automatico</option>
                </select>
              </div>
            </div>

            <!-- Language -->
            <div class="setting-item">
              <div class="setting-info">
                <label class="setting-label" for="language">Lingua</label>
                <p class="setting-description">
                  Cambia la lingua dell'interfaccia
                </p>
              </div>
              <div class="setting-control">
                <select
                  id="language"
                  v-model="interfaceSettings.language"
                  class="setting-select"
                  @change="updateInterfaceSettings"
                >
                  <option value="it">Italiano</option>
                  <option value="en">English</option>
                  <option value="es">Español</option>
                  <option value="fr">Français</option>
                </select>
              </div>
            </div>

            <!-- Currency Format -->
            <div class="setting-item">
              <div class="setting-info">
                <label class="setting-label" for="currency">Formato Valuta</label>
                <p class="setting-description">
                  Come visualizzare i valori monetari
                </p>
              </div>
              <div class="setting-control">
                <select
                  id="currency"
                  v-model="interfaceSettings.currencyFormat"
                  class="setting-select"
                  @change="updateInterfaceSettings"
                >
                  <option value="eur">Euro (€)</option>
                  <option value="usd">Dollaro ($)</option>
                  <option value="gbp">Sterlina (£)</option>
                </select>
              </div>
            </div>

            <!-- Compact Mode -->
            <div class="setting-item">
              <div class="setting-info">
                <label class="setting-label" for="compact-mode">Modalità Compatta</label>
                <p class="setting-description">
                  Riduci gli spazi nell'interfaccia per visualizzare più contenuti
                </p>
              </div>
              <div class="setting-control">
                <label class="toggle-switch">
                  <input
                    id="compact-mode"
                    v-model="interfaceSettings.compactMode"
                    type="checkbox"
                    @change="updateInterfaceSettings"
                  />
                  <span class="toggle-slider"></span>
                </label>
              </div>
            </div>

            <!-- Font Size -->
            <div class="setting-item">
              <div class="setting-info">
                <label class="setting-label" for="font-size">Dimensione Testo</label>
                <p class="setting-description">
                  Regola la dimensione del testo nell'interfaccia
                </p>
              </div>
              <div class="setting-control">
                <div class="font-size-control">
                  <input
                    id="font-size"
                    v-model="interfaceSettings.fontSize"
                    type="range"
                    min="80"
                    max="120"
                    step="10"
                    class="font-size-slider"
                    @input="updateInterfaceSettings"
                  />
                  <span class="font-size-value">{{ interfaceSettings.fontSize }}%</span>
                </div>
              </div>
            </div>

            <!-- Reduce Motion -->
            <div class="setting-item">
              <div class="setting-info">
                <label class="setting-label" for="reduce-motion">Riduci Movimento</label>
                <p class="setting-description">
                  Limita animazioni e transizioni per accessibilità
                </p>
              </div>
              <div class="setting-control">
                <label class="toggle-switch">
                  <input
                    id="reduce-motion"
                    v-model="interfaceSettings.reduceMotion"
                    type="checkbox"
                    @change="updateInterfaceSettings"
                  />
                  <span class="toggle-slider"></span>
                </label>
              </div>
            </div>
          </div>
        </BaseCard>
      </section>

      <!-- Account Settings -->
      <section class="settings-section">
        <BaseCard
          title="Account e Privacy"
          icon="👤"
          class="settings-card"
        >
          <div class="settings-grid">
            <!-- Email Notifications -->
            <div class="setting-item">
              <div class="setting-info">
                <label class="setting-label" for="email-notifications">Notifiche Email</label>
                <p class="setting-description">
                  Ricevi email per aggiornamenti importanti e promemoria
                </p>
              </div>
              <div class="setting-control">
                <label class="toggle-switch">
                  <input
                    id="email-notifications"
                    v-model="accountSettings.emailNotifications"
                    type="checkbox"
                    @change="updateAccountSettings"
                  />
                  <span class="toggle-slider"></span>
                </label>
              </div>
            </div>

            <!-- Analytics -->
            <div class="setting-item">
              <div class="setting-info">
                <label class="setting-label" for="analytics">Condivisione Dati Analitici</label>
                <p class="setting-description">
                  Aiutaci a migliorare il gioco condividendo dati di utilizzo anonimi
                </p>
              </div>
              <div class="setting-control">
                <label class="toggle-switch">
                  <input
                    id="analytics"
                    v-model="accountSettings.analytics"
                    type="checkbox"
                    @change="updateAccountSettings"
                  />
                  <span class="toggle-slider"></span>
                </label>
              </div>
            </div>

            <!-- Session Timeout -->
            <div class="setting-item">
              <div class="setting-info">
                <label class="setting-label" for="session-timeout">Timeout Sessione</label>
                <p class="setting-description">
                  Tempo di inattività prima del logout automatico
                </p>
              </div>
              <div class="setting-control">
                <select
                  id="session-timeout"
                  v-model="accountSettings.sessionTimeout"
                  class="setting-select"
                  @change="updateAccountSettings"
                >
                  <option value="30">30 minuti</option>
                  <option value="60">1 ora</option>
                  <option value="120">2 ore</option>
                  <option value="240">4 ore</option>
                  <option value="0">Mai</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Account Actions -->
          <div class="account-actions">
            <h4 class="actions-title">Azioni Account</h4>
            <div class="actions-grid">
              <BaseButton
                variant="secondary"
                icon="📄"
                @click="exportData"
                :loading="exporting"
              >
                Esporta Dati
              </BaseButton>

              <BaseButton
                variant="secondary"
                icon="🔄"
                @click="clearCache"
                :loading="clearingCache"
              >
                Pulisci Cache
              </BaseButton>

              <BaseButton
                variant="warning"
                icon="🚪"
                @click="logoutAllDevices"
                :loading="loggingOut"
              >
                Logout da Tutti i Dispositivi
              </BaseButton>

              <BaseButton
                variant="danger"
                icon="🗑️"
                @click="showDeleteAccountModal = true"
              >
                Elimina Account
              </BaseButton>
            </div>
          </div>
        </BaseCard>
      </section>

      <!-- Advanced Settings -->
      <section class="settings-section">
        <BaseCard
          title="Impostazioni Avanzate"
          icon="🔧"
          class="settings-card"
        >
          <div class="settings-grid">
            <!-- Debug Mode -->
            <div class="setting-item">
              <div class="setting-info">
                <label class="setting-label" for="debug-mode">Modalità Debug</label>
                <p class="setting-description">
                  Mostra informazioni tecniche per sviluppatori e debug
                </p>
              </div>
              <div class="setting-control">
                <label class="toggle-switch">
                  <input
                    id="debug-mode"
                    v-model="advancedSettings.debugMode"
                    type="checkbox"
                    @change="updateAdvancedSettings"
                  />
                  <span class="toggle-slider"></span>
                </label>
              </div>
            </div>

            <!-- Performance Mode -->
            <div class="setting-item">
              <div class="setting-info">
                <label class="setting-label" for="performance-mode">Modalità Performance</label>
                <p class="setting-description">
                  Ottimizza per prestazioni su dispositivi più lenti
                </p>
              </div>
              <div class="setting-control">
                <label class="toggle-switch">
                  <input
                    id="performance-mode"
                    v-model="advancedSettings.performanceMode"
                    type="checkbox"
                    @change="updateAdvancedSettings"
                  />
                  <span class="toggle-slider"></span>
                </label>
              </div>
            </div>

            <!-- Beta Features -->
            <div class="setting-item">
              <div class="setting-info">
                <label class="setting-label" for="beta-features">Funzionalità Beta</label>
                <p class="setting-description">
                  Abilita funzionalità sperimentali in fase di test
                </p>
              </div>
              <div class="setting-control">
                <label class="toggle-switch">
                  <input
                    id="beta-features"
                    v-model="advancedSettings.betaFeatures"
                    type="checkbox"
                    @change="updateAdvancedSettings"
                  />
                  <span class="toggle-slider"></span>
                </label>
              </div>
            </div>
          </div>

          <!-- Reset Settings -->
          <div class="reset-section">
            <h4 class="reset-title">Ripristina Impostazioni</h4>
            <p class="reset-description">
              Riporta tutte le impostazioni ai valori predefiniti
            </p>
            <BaseButton
              variant="outline"
              icon="🔄"
              @click="resetAllSettings"
              :loading="resetting"
            >
              Ripristina Tutto
            </BaseButton>
          </div>
        </BaseCard>
      </section>
    </div>

    <!-- Delete Account Modal -->
    <BaseModal
      :is-open="showDeleteAccountModal"
      @close="showDeleteAccountModal = false"
      title="Elimina Account"
      variant="danger"
    >
      <div class="delete-account-content">
        <div class="warning-box">
          <span class="warning-icon">⚠️</span>
          <div class="warning-text">
            <p class="warning-title">Azione Irreversibile</p>
            <p class="warning-description">
              L'eliminazione dell'account cancellerà permanentemente:
            </p>
            <ul class="warning-list">
              <li>Tutte le tue partite salvate</li>
              <li>Statistiche e progressi</li>
              <li>Impostazioni personalizzate</li>
              <li>Tutti i dati associati all'account</li>
            </ul>
          </div>
        </div>

        <div class="confirmation-input">
          <label for="delete-confirmation" class="input-label">
            Scrivi "ELIMINA" per confermare:
          </label>
          <BaseInput
            id="delete-confirmation"
            v-model="deleteConfirmation"
            placeholder="ELIMINA"
            :error-message="deleteConfirmationError"
          />
        </div>
      </div>

      <template #footer>
        <div class="modal-actions">
          <BaseButton
            variant="secondary"
            @click="cancelDeleteAccount"
          >
            Annulla
          </BaseButton>
          <BaseButton
            variant="danger"
            @click="confirmDeleteAccount"
            :disabled="!canDeleteAccount"
            :loading="deletingAccount"
          >
            Elimina Definitivamente
          </BaseButton>
        </div>
      </template>
    </BaseModal>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/js/stores/auth'
import { useNotificationStore } from '@/js/stores/notifications'

// Stores
const authStore = useAuthStore()
const notificationStore = useNotificationStore()
const router = useRouter()

// Reactive state
const exporting = ref(false)
const clearingCache = ref(false)
const loggingOut = ref(false)
const resetting = ref(false)
const deletingAccount = ref(false)
const showDeleteAccountModal = ref(false)
const deleteConfirmation = ref('')
const deleteConfirmationError = ref('')

// Settings objects
const gameSettings = ref({
  autoSave: true,
  notifications: true,
  soundEffects: true,
  animationSpeed: 'normal',
  difficulty: 'normal',
  detailedTimers: false
})

const interfaceSettings = ref({
  theme: 'light',
  language: 'it',
  currencyFormat: 'eur',
  compactMode: false,
  fontSize: 100,
  reduceMotion: false
})

const accountSettings = ref({
  emailNotifications: true,
  analytics: true,
  sessionTimeout: 60
})

const advancedSettings = ref({
  debugMode: false,
  performanceMode: false,
  betaFeatures: false
})

// Computed properties
const canDeleteAccount = computed(() => {
  return deleteConfirmation.value === 'ELIMINA'
})

// Methods
const loadSettings = () => {
  try {
    // Load from localStorage or API
    const savedGameSettings = localStorage.getItem('gameSettings')
    if (savedGameSettings) {
      gameSettings.value = { ...gameSettings.value, ...JSON.parse(savedGameSettings) }
    }

    const savedInterfaceSettings = localStorage.getItem('interfaceSettings')
    if (savedInterfaceSettings) {
      interfaceSettings.value = { ...interfaceSettings.value, ...JSON.parse(savedInterfaceSettings) }
    }

    const savedAccountSettings = localStorage.getItem('accountSettings')
    if (savedAccountSettings) {
      accountSettings.value = { ...accountSettings.value, ...JSON.parse(savedAccountSettings) }
    }

    const savedAdvancedSettings = localStorage.getItem('advancedSettings')
    if (savedAdvancedSettings) {
      advancedSettings.value = { ...advancedSettings.value, ...JSON.parse(savedAdvancedSettings) }
    }
  } catch (error) {
    console.error('Error loading settings:', error)
  }
}

const updateGameSettings = () => {
  try {
    localStorage.setItem('gameSettings', JSON.stringify(gameSettings.value))
    notificationStore.success('Impostazioni di gioco aggiornate')
  } catch (error) {
    notificationStore.error('Errore nel salvataggio delle impostazioni')
  }
}

const updateInterfaceSettings = () => {
  try {
    localStorage.setItem('interfaceSettings', JSON.stringify(interfaceSettings.value))

    // Apply theme immediately
    applyTheme()

    // Apply font size
    applyFontSize()

    notificationStore.success('Impostazioni interfaccia aggiornate')
  } catch (error) {
    notificationStore.error('Errore nel salvataggio delle impostazioni')
  }
}

const updateAccountSettings = () => {
  try {
    localStorage.setItem('accountSettings', JSON.stringify(accountSettings.value))
    notificationStore.success('Impostazioni account aggiornate')
  } catch (error) {
    notificationStore.error('Errore nel salvataggio delle impostazioni')
  }
}

const updateAdvancedSettings = () => {
  try {
    localStorage.setItem('advancedSettings', JSON.stringify(advancedSettings.value))
    notificationStore.success('Impostazioni avanzate aggiornate')
  } catch (error) {
    notificationStore.error('Errore nel salvataggio delle impostazioni')
  }
}

const applyTheme = () => {
  const theme = interfaceSettings.value.theme
  if (theme === 'dark') {
    document.documentElement.classList.add('dark')
  } else if (theme === 'light') {
    document.documentElement.classList.remove('dark')
  } else {
    // Auto theme
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches
    if (prefersDark) {
      document.documentElement.classList.add('dark')
    } else {
      document.documentElement.classList.remove('dark')
    }
  }
}

const applyFontSize = () => {
  const fontSize = interfaceSettings.value.fontSize
  document.documentElement.style.fontSize = `${fontSize}%`
}

const exportData = async () => {
  exporting.value = true
  try {
    // Simulate data export
    await new Promise(resolve => setTimeout(resolve, 2000))

    const data = {
      settings: {
        game: gameSettings.value,
        interface: interfaceSettings.value,
        account: accountSettings.value,
        advanced: advancedSettings.value
      },
      user: authStore.user,
      timestamp: new Date().toISOString()
    }

    const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' })
    const url = URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = `tech-company-game-data-${Date.now()}.json`
    link.click()

    URL.revokeObjectURL(url)
    notificationStore.success('Dati esportati con successo')
  } catch (error) {
    notificationStore.error('Errore nell\'esportazione dei dati')
  } finally {
    exporting.value = false
  }
}

const clearCache = async () => {
  clearingCache.value = true
  try {
    // Clear various caches
    if ('caches' in window) {
      const cacheNames = await caches.keys()
      await Promise.all(
        cacheNames.map(cacheName => caches.delete(cacheName))
      )
    }

    // Clear localStorage (except auth and settings)
    const keysToKeep = ['auth-token', 'gameSettings', 'interfaceSettings', 'accountSettings', 'advancedSettings']
    Object.keys(localStorage).forEach(key => {
      if (!keysToKeep.includes(key)) {
        localStorage.removeItem(key)
      }
    })

    notificationStore.success('Cache pulita con successo')
  } catch (error) {
    notificationStore.error('Errore nella pulizia della cache')
  } finally {
    clearingCache.value = false
  }
}

const logoutAllDevices = async () => {
  loggingOut.value = true
  try {
    await authStore.logoutAll()
    notificationStore.success('Logout effettuato da tutti i dispositivi')
    router.push({ name: 'Login' })
  } catch (error) {
    notificationStore.error('Errore nel logout')
  } finally {
    loggingOut.value = false
  }
}

const resetAllSettings = async () => {
  resetting.value = true
  try {
    // Reset to defaults
    gameSettings.value = {
      autoSave: true,
      notifications: true,
      soundEffects: true,
      animationSpeed: 'normal',
      difficulty: 'normal',
      detailedTimers: false
    }

    interfaceSettings.value = {
      theme: 'light',
      language: 'it',
      currencyFormat: 'eur',
      compactMode: false,
      fontSize: 100,
      reduceMotion: false
    }

    accountSettings.value = {
      emailNotifications: true,
      analytics: true,
      sessionTimeout: 60
    }

    advancedSettings.value = {
      debugMode: false,
      performanceMode: false,
      betaFeatures: false
    }

    // Save all settings
    updateGameSettings()
    updateInterfaceSettings()
    updateAccountSettings()
    updateAdvancedSettings()

    notificationStore.success('Tutte le impostazioni sono state ripristinate')
  } catch (error) {
    notificationStore.error('Errore nel ripristino delle impostazioni')
  } finally {
    resetting.value = false
  }
}

const cancelDeleteAccount = () => {
  showDeleteAccountModal.value = false
  deleteConfirmation.value = ''
  deleteConfirmationError.value = ''
}

const confirmDeleteAccount = async () => {
  if (!canDeleteAccount.value) {
    deleteConfirmationError.value = 'Scrivi "ELIMINA" per confermare'
    return
  }

  deletingAccount.value = true
  try {
    await authStore.deleteAccount()
    notificationStore.success('Account eliminato con successo')
    router.push({ name: 'Welcome' })
  } catch (error) {
    notificationStore.error('Errore nell\'eliminazione dell\'account')
  } finally {
    deletingAccount.value = false
  }
}

// Lifecycle
onMounted(() => {
  loadSettings()
  applyTheme()
  applyFontSize()
})
</script>

<style scoped>
.settings-view {
  @apply max-w-4xl mx-auto space-y-8;
}

/* Page Header */
.page-header {
  @apply mb-8;
}

.page-title {
  @apply text-2xl sm:text-3xl font-bold text-neutral-900;
  @apply flex items-center;
}

.title-icon {
  @apply text-3xl sm:text-4xl mr-3;
}

.page-subtitle {
  @apply text-neutral-600 mt-1;
}

/* Settings Content */
.settings-content {
  @apply space-y-8;
}

.settings-section {
  @apply w-full;
}

.settings-card {
  @apply w-full;
}

/* Settings Grid */
.settings-grid {
  @apply space-y-6;
}

.setting-item {
  @apply flex flex-col sm:flex-row sm:items-center sm:justify-between;
  @apply py-4 border-b border-neutral-200 last:border-b-0;
}

.setting-info {
  @apply flex-1 mb-3 sm:mb-0 sm:mr-6;
}

.setting-label {
  @apply block text-base font-medium text-neutral-900 mb-1;
}

.setting-description {
  @apply text-sm text-neutral-600;
}

.setting-control {
  @apply flex-shrink-0;
}

/* Toggle Switch */
.toggle-switch {
  @apply relative inline-block w-12 h-6 cursor-pointer;
}

.toggle-switch input {
  @apply opacity-0 w-0 h-0;
}

.toggle-slider {
  @apply absolute inset-0 bg-neutral-300 rounded-full;
  @apply transition-colors duration-200 ease-in-out;
}

.toggle-slider:before {
  @apply absolute content-[''] h-5 w-5 left-0.5 top-0.5;
  @apply bg-white rounded-full shadow-md;
  @apply transition-transform duration-200 ease-in-out;
}

.toggle-switch input:checked + .toggle-slider {
  @apply bg-brand-600;
}

.toggle-switch input:checked + .toggle-slider:before {
  @apply transform translate-x-6;
}

/* Select */
.setting-select {
  @apply px-3 py-2 border border-neutral-300 rounded-md;
  @apply bg-white text-neutral-900 min-w-32;
  @apply focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500;
}

/* Font Size Control */
.font-size-control {
  @apply flex items-center space-x-3;
}

.font-size-slider {
  @apply flex-1 h-2 bg-neutral-200 rounded-lg appearance-none cursor-pointer;
}

.font-size-slider::-webkit-slider-thumb {
  @apply appearance-none w-4 h-4 bg-brand-600 rounded-full cursor-pointer;
}

.font-size-slider::-moz-range-thumb {
  @apply w-4 h-4 bg-brand-600 rounded-full cursor-pointer border-0;
}

.font-size-value {
  @apply text-sm font-medium text-neutral-700 min-w-12 text-right;
}

/* Account Actions */
.account-actions {
  @apply mt-8 pt-6 border-t border-neutral-200;
}

.actions-title {
  @apply text-lg font-semibold text-neutral-900 mb-4;
}

.actions-grid {
  @apply grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4;
}

/* Reset Section */
.reset-section {
  @apply mt-8 pt-6 border-t border-neutral-200 text-center;
}

.reset-title {
  @apply text-lg font-semibold text-neutral-900 mb-2;
}

.reset-description {
  @apply text-sm text-neutral-600 mb-4;
}

/* Modal Content */
.delete-account-content {
  @apply space-y-6;
}

.warning-box {
  @apply flex items-start space-x-3 p-4;
  @apply bg-danger-50 border border-danger-200 rounded-lg;
}

.warning-icon {
  @apply text-2xl text-danger-600 flex-shrink-0;
}

.warning-text {
  @apply flex-1;
}

.warning-title {
  @apply font-semibold text-danger-900 mb-2;
}

.warning-description {
  @apply text-danger-800 mb-3;
}

.warning-list {
  @apply list-disc list-inside space-y-1 text-sm text-danger-700;
}

.confirmation-input {
  @apply space-y-2;
}

.input-label {
  @apply block text-sm font-medium text-neutral-700;
}

.modal-actions {
  @apply flex items-center justify-end space-x-3;
}

/* Responsive Design */
@media (max-width: 640px) {
  .page-title {
    @apply text-xl;
  }

  .title-icon {
    @apply text-2xl mr-2;
  }

  .setting-item {
    @apply flex-col;
  }

  .setting-control {
    @apply w-full;
  }

  .setting-select {
    @apply w-full;
  }

  .actions-grid {
    @apply grid-cols-1;
  }

  .font-size-control {
    @apply flex-col space-y-2 space-x-0;
  }
}

/* Dark Mode Support */
@media (prefers-color-scheme: dark) {
  .setting-select {
    @apply bg-neutral-800 border-neutral-600 text-neutral-100;
  }

  .toggle-slider {
    @apply bg-neutral-600;
  }

  .font-size-slider {
    @apply bg-neutral-700;
  }
}

/* Focus Styles for Accessibility */
.toggle-switch:focus-within .toggle-slider {
  @apply ring-2 ring-brand-500 ring-offset-2;
}

.font-size-slider:focus {
  @apply outline-none ring-2 ring-brand-500 ring-offset-2;
}

/* Animation for settings changes */
.setting-item {
  @apply transition-colors duration-200;
}

.setting-item:hover {
  @apply bg-neutral-50;
}

/* Success feedback animation */
@keyframes settingsSaved {
  0% { @apply bg-green-100; }
  100% { @apply bg-transparent; }
}

.settings-saved {
  animation: settingsSaved 1s ease-out;
}

/* Loading states */
.settings-card.loading {
  @apply opacity-60 pointer-events-none;
}

/* High contrast mode support */
@media (prefers-contrast: high) {
  .toggle-slider {
    @apply border-2 border-neutral-900;
  }

  .setting-select {
    @apply border-2 border-neutral-900;
  }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
  .toggle-slider,
  .toggle-slider:before,
  .setting-item {
    @apply transition-none;
  }
}
</style>