<template>
  <header class="game-header">
    <div class="game-header__container">
      <!-- Logo e titolo gioco -->
      <div class="game-header__brand">
        <div class="brand-logo">🏢</div>
        <div class="brand-info">
          <h1 class="brand-title">{{ gameStore.currentGame?.name || 'Tech Company' }}</h1>
          <p class="brand-subtitle">Software House Manager</p>
        </div>
      </div>

      <!-- Stats principali -->
      <div class="game-header__stats">
        <!-- Patrimonio -->
        <div class="stat-item stat-item--primary">
          <div class="stat-icon">💰</div>
          <div class="stat-content">
            <span class="stat-label">Patrimonio</span>
            <span class="stat-value" :class="moneyColorClass">
              {{ formatCurrency(gameStore.currentGame?.money || 0) }}
            </span>
          </div>
        </div>

        <!-- Costi mensili -->
        <div class="stat-item">
          <div class="stat-icon">📊</div>
          <div class="stat-content">
            <span class="stat-label">Costi/Mese</span>
            <span class="stat-value">
              {{ formatCurrency(monthlyCosts) }}
            </span>
          </div>
        </div>

        <!-- Team size -->
        <div class="stat-item">
          <div class="stat-icon">👥</div>
          <div class="stat-content">
            <span class="stat-label">Team</span>
            <span class="stat-value">
              {{ totalTeamSize }}
            </span>
          </div>
        </div>

        <!-- Progetti attivi -->
        <div class="stat-item">
          <div class="stat-icon">🚀</div>
          <div class="stat-content">
            <span class="stat-label">Progetti</span>
            <span class="stat-value">
              {{ activeProjects }}/{{ totalProjects }}
            </span>
          </div>
        </div>
      </div>

      <!-- Azioni rapide -->
      <div class="game-header__actions">
        <!-- Status del gioco -->
        <div class="game-status">
          <StatusBadge
            :status="gameStore.currentGame?.status"
            :show-icon="true"
          />
        </div>

        <!-- Salvataggio automatico -->
        <div class="autosave-indicator" :class="{ 'autosave-indicator--saving': isSaving }">
          <svg v-if="isSaving" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
          </svg>
          <span v-else class="autosave-icon">💾</span>
          <span class="autosave-text">
            {{ isSaving ? 'Salvando...' : 'Salvato' }}
          </span>
        </div>

        <!-- Menu azioni -->
        <div class="action-menu">
          <BaseButton
            variant="ghost"
            size="sm"
            icon="⚙️"
            @click="showGameMenu = !showGameMenu"
            aria-label="Menu gioco"
          />

          <!-- Dropdown menu -->
          <div v-if="showGameMenu" class="action-dropdown" @click.stop>
            <div class="dropdown-item" @click="saveGame">
              <span class="dropdown-icon">💾</span>
              Salva Gioco
            </div>
            <div class="dropdown-item" @click="pauseGame">
              <span class="dropdown-icon">⏸️</span>
              Metti in Pausa
            </div>
            <div class="dropdown-divider"></div>
            <div class="dropdown-item" @click="goToGameList">
              <span class="dropdown-icon">📋</span>
              Lista Partite
            </div>
            <div class="dropdown-item dropdown-item--danger" @click="confirmEndGame">
              <span class="dropdown-icon">🚪</span>
              Termina Partita
            </div>
          </div>
        </div>

        <!-- User menu -->
        <div class="user-menu">
          <div class="user-avatar" @click="showUserMenu = !showUserMenu">
            <img
              v-if="authStore.user?.avatar_url"
              :src="authStore.user.avatar_url"
              :alt="authStore.user.name"
              class="avatar-image"
            />
            <div v-else class="avatar-fallback">
              {{ authStore.userInitials }}
            </div>
          </div>

          <!-- User dropdown -->
          <div v-if="showUserMenu" class="user-dropdown" @click.stop>
            <div class="user-info">
              <p class="user-name">{{ authStore.user?.name }}</p>
              <p class="user-email">{{ authStore.user?.email }}</p>
            </div>
            <div class="dropdown-divider"></div>
            <div class="dropdown-item" @click="goToProfile">
              <span class="dropdown-icon">👤</span>
              Profilo
            </div>
            <div class="dropdown-item" @click="goToSettings">
              <span class="dropdown-icon">⚙️</span>
              Impostazioni
            </div>
            <div class="dropdown-divider"></div>
            <div class="dropdown-item" @click="logout">
              <span class="dropdown-icon">🚪</span>
              Logout
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Game Over Warning -->
    <div v-if="isNearBankruptcy" class="bankruptcy-warning">
      <div class="warning-content">
        <span class="warning-icon">⚠️</span>
        <span class="warning-text">
          Attenzione! Il patrimonio è in rosso. Completa progetti o riduci i costi per evitare il fallimento.
        </span>
        <BaseButton
          size="sm"
          variant="warning"
          @click="dismissWarning"
        >
          Chiudi
        </BaseButton>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useGameStore } from '@/js/stores/game'
import { useAuthStore } from '@/js/stores/auth'
import { useNotificationStore } from '@/js/stores/notifications'
import { formatCurrency } from '@/js/utils/helpers'
import StatusBadge from '@/js/components/shared/StatusBadge.vue'

// Stores
const gameStore = useGameStore()
const authStore = useAuthStore()
const notificationStore = useNotificationStore()
const router = useRouter()

// Reactive state
const showGameMenu = ref(false)
const showUserMenu = ref(false)
const isSaving = ref(false)
const showBankruptcyWarning = ref(true)

// Computed properties
const monthlyCosts = computed(() => {
  if (!gameStore.currentGame) return 0
  return gameStore.currentGame.monthly_costs || 0
})

const totalTeamSize = computed(() => {
  if (!gameStore.currentGame) return 0
  const developers = gameStore.currentGame.developers?.length || 0
  const salesPeople = gameStore.currentGame.sales_people?.length || 0
  return developers + salesPeople
})

const activeProjects = computed(() => {
  if (!gameStore.currentGame?.projects) return 0
  return gameStore.currentGame.projects.filter(p => p.status === 'in_progress').length
})

const totalProjects = computed(() => {
  return gameStore.currentGame?.projects?.length || 0
})

const isNearBankruptcy = computed(() => {
  if (!gameStore.currentGame) return false
  return gameStore.currentGame.money < 1000 && showBankruptcyWarning.value
})

const moneyColorClass = computed(() => {
  const money = gameStore.currentGame?.money || 0
  if (money < 0) return 'stat-value--danger'
  if (money < 1000) return 'stat-value--warning'
  return 'stat-value--success'
})

// Methods
const saveGame = async () => {
  isSaving.value = true
  try {
    await gameStore.saveGame()
    notificationStore.success('Gioco salvato con successo!')
  } catch (error) {
    notificationStore.error('Errore nel salvataggio')
  } finally {
    isSaving.value = false
    showGameMenu.value = false
  }
}

const pauseGame = async () => {
  try {
    await gameStore.pauseGame()
    notificationStore.info('Gioco messo in pausa')
  } catch (error) {
    notificationStore.error('Errore nella pausa del gioco')
  }
  showGameMenu.value = false
}

const goToGameList = () => {
  router.push({ name: 'GameList' })
  showGameMenu.value = false
}

const confirmEndGame = () => {
  if (confirm('Sei sicuro di voler terminare questa partita? I progressi verranno salvati.')) {
    router.push({ name: 'GameList' })
  }
  showGameMenu.value = false
}

const goToProfile = () => {
  router.push({ name: 'Profile' })
  showUserMenu.value = false
}

const goToSettings = () => {
  router.push({ name: 'Settings' })
  showUserMenu.value = false
}

const logout = async () => {
  if (confirm('Sei sicuro di voler fare logout? Il gioco verrà salvato automaticamente.')) {
    await saveGame()
    await authStore.logout()
    router.push({ name: 'Login' })
  }
  showUserMenu.value = false
}

const dismissWarning = () => {
  showBankruptcyWarning.value = false
}

// Close dropdowns when clicking outside
const handleClickOutside = (event) => {
  if (!event.target.closest('.action-menu')) {
    showGameMenu.value = false
  }
  if (!event.target.closest('.user-menu')) {
    showUserMenu.value = false
  }
}

// Auto-save functionality
let autoSaveInterval = null
onMounted(() => {
  document.addEventListener('click', handleClickOutside)

  // Auto-save ogni 30 secondi
  autoSaveInterval = setInterval(async () => {
    if (gameStore.currentGame?.status === 'active') {
      isSaving.value = true
      try {
        await gameStore.saveGame()
      } catch (error) {
        console.error('Auto-save failed:', error)
      } finally {
        isSaving.value = false
      }
    }
  }, 30000)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
  if (autoSaveInterval) {
    clearInterval(autoSaveInterval)
  }
})
</script>

<style scoped>
.game-header {
  @apply bg-white border-b border-neutral-200 shadow-sm;
  @apply relative z-30;
}

.game-header__container {
  @apply max-w-7xl mx-auto px-4 py-3;
  @apply flex items-center justify-between;
}

/* Brand Section */
.game-header__brand {
  @apply flex items-center space-x-3;
}

.brand-logo {
  @apply text-3xl;
}

.brand-info {
  @apply hidden sm:block;
}

.brand-title {
  @apply text-lg font-bold text-neutral-900 leading-none;
}

.brand-subtitle {
  @apply text-xs text-neutral-500;
}

/* Stats Section */
.game-header__stats {
  @apply hidden lg:flex items-center space-x-6;
}

.stat-item {
  @apply flex items-center space-x-2;
  @apply px-3 py-2 rounded-md bg-neutral-50;
  @apply transition-colors duration-200;
}

.stat-item--primary {
  @apply bg-brand-50 border border-brand-200;
}

.stat-icon {
  @apply text-lg;
}

.stat-content {
  @apply flex flex-col;
}

.stat-label {
  @apply text-xs text-neutral-600 leading-none;
}

.stat-value {
  @apply text-sm font-semibold text-neutral-900;
}

.stat-value--success {
  @apply text-success-600;
}

.stat-value--warning {
  @apply text-warning-600;
}

.stat-value--danger {
  @apply text-danger-600;
}

/* Actions Section */
.game-header__actions {
  @apply flex items-center space-x-4;
}

.game-status {
  @apply hidden sm:block;
}

/* Autosave Indicator */
.autosave-indicator {
  @apply flex items-center space-x-1 text-xs text-neutral-500;
  @apply px-2 py-1 rounded bg-neutral-100;
}

.autosave-indicator--saving {
  @apply text-brand-600 bg-brand-50;
}

.autosave-icon {
  @apply text-sm;
}

.autosave-text {
  @apply hidden sm:inline;
}

/* Action Menu */
.action-menu {
  @apply relative;
}

.action-dropdown {
  @apply absolute right-0 top-full mt-2 w-48;
  @apply bg-white border border-neutral-200 rounded-md shadow-lg;
  @apply py-1 z-50;
}

.dropdown-item {
  @apply flex items-center space-x-2 px-3 py-2;
  @apply text-sm text-neutral-700 hover:bg-neutral-100;
  @apply cursor-pointer transition-colors;
}

.dropdown-item--danger {
  @apply text-danger-600 hover:bg-danger-50;
}

.dropdown-icon {
  @apply text-base;
}

.dropdown-divider {
  @apply border-t border-neutral-200 my-1;
}

/* User Menu */
.user-menu {
  @apply relative;
}

.user-avatar {
  @apply w-8 h-8 rounded-full cursor-pointer;
  @apply border-2 border-transparent hover:border-brand-300;
  @apply transition-colors duration-200;
}

.avatar-image {
  @apply w-full h-full rounded-full object-cover;
}

.avatar-fallback {
  @apply w-full h-full rounded-full bg-brand-600 text-white;
  @apply flex items-center justify-center text-sm font-medium;
}

.user-dropdown {
  @apply absolute right-0 top-full mt-2 w-56;
  @apply bg-white border border-neutral-200 rounded-md shadow-lg;
  @apply py-1 z-50;
}

.user-info {
  @apply px-3 py-2 border-b border-neutral-200;
}

.user-name {
  @apply text-sm font-medium text-neutral-900 truncate;
}

.user-email {
  @apply text-xs text-neutral-500 truncate;
}

/* Bankruptcy Warning */
.bankruptcy-warning {
  @apply absolute top-full left-0 right-0;
  @apply bg-warning-50 border-b border-warning-200;
  @apply px-4 py-2;
}

.warning-content {
  @apply max-w-7xl mx-auto flex items-center justify-between;
}

.warning-icon {
  @apply text-lg text-warning-600 mr-2;
}

.warning-text {
  @apply flex-1 text-sm text-warning-800;
}

/* Responsive */
@media (max-width: 1024px) {
  .game-header__stats {
    @apply hidden;
  }
}

@media (max-width: 640px) {
  .game-header__container {
    @apply px-3 py-2;
  }

  .game-header__brand {
    @apply space-x-2;
  }

  .brand-logo {
    @apply text-2xl;
  }

  .game-header__actions {
    @apply space-x-2;
  }
}

/* Animations */
.action-dropdown,
.user-dropdown {
  animation: fadeIn 0.2s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-4px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Focus states for accessibility */
.user-avatar:focus {
  @apply outline-none ring-2 ring-brand-500 ring-offset-2;
}

.dropdown-item:focus {
  @apply outline-none bg-neutral-100;
}
</style>