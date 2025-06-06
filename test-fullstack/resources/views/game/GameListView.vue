<template>
  <div class="game-list-view">
    <!-- Page Header -->
    <div class="page-header">
      <div class="header-content">
        <h1 class="page-title">
          <span class="title-icon">🎮</span>
          Le Tue Partite
        </h1>
        <p class="page-subtitle">
          Gestisci e riprendi le tue partite salvate
        </p>
      </div>

      <div class="header-actions">
        <BaseButton
          variant="primary"
          icon="➕"
          @click="createNewGame"
          :disabled="isLoading"
        >
          Nuova Partita
        </BaseButton>
      </div>
    </div>

    <!-- Games Grid -->
    <div v-if="isLoading" class="loading-grid">
      <div v-for="i in 3" :key="i" class="game-card-skeleton">
        <div class="skeleton-header"></div>
        <div class="skeleton-content">
          <div class="skeleton-line"></div>
          <div class="skeleton-line short"></div>
        </div>
        <div class="skeleton-footer"></div>
      </div>
    </div>

    <div v-else-if="games.length === 0" class="empty-state">
      <div class="empty-icon">🎯</div>
      <h3 class="empty-title">Nessuna Partita Trovata</h3>
      <p class="empty-description">
        Non hai ancora creato nessuna partita. Inizia subito a gestire la tua software house!
      </p>
      <BaseButton
        variant="primary"
        icon="🚀"
        size="lg"
        @click="createNewGame"
        class="empty-cta"
      >
        Crea la Tua Prima Partita
      </BaseButton>
    </div>

    <div v-else class="games-grid">
      <BaseCard
        v-for="game in games"
        :key="game.id"
        class="game-card"
        :class="{ 'game-card--active': game.status === 'active' }"
        clickable
        @click="selectGame(game)"
      >
        <!-- Game Header -->
        <template #title>
          <div class="game-header">
            <div class="game-title">
              {{ game.name || `Partita del ${formatDate(game.created_at)}` }}
            </div>
            <StatusBadge
              :status="game.status"
              size="sm"
              :show-pulse="game.status === 'active'"
            />
          </div>
        </template>

        <!-- Game Stats -->
        <div class="game-stats">
          <div class="stat-row">
            <div class="stat-item">
              <span class="stat-icon">💰</span>
              <div class="stat-content">
                <span class="stat-label">Patrimonio</span>
                <span class="stat-value" :class="getMoneyClass(game.money)">
                  {{ formatCurrency(game.money) }}
                </span>
              </div>
            </div>

            <div class="stat-item">
              <span class="stat-icon">👥</span>
              <div class="stat-content">
                <span class="stat-label">Team</span>
                <span class="stat-value">{{ game.team_size }}</span>
              </div>
            </div>
          </div>

          <div class="stat-row">
            <div class="stat-item">
              <span class="stat-icon">✅</span>
              <div class="stat-content">
                <span class="stat-label">Progetti</span>
                <span class="stat-value">{{ game.projects_completed }}</span>
              </div>
            </div>

            <div class="stat-item">
              <span class="stat-icon">⏱️</span>
              <div class="stat-content">
                <span class="stat-label">Tempo</span>
                <span class="stat-value">{{ formatPlayTime(game.play_time_hours) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Game Actions -->
        <template #footer>
          <div class="game-actions">
            <BaseButton
              v-if="game.status === 'active'"
              variant="primary"
              size="sm"
              @click.stop="resumeGame(game)"
              :disabled="isLoading"
            >
              Continua
            </BaseButton>

            <BaseButton
              v-else-if="game.status === 'paused'"
              variant="secondary"
              size="sm"
              @click.stop="resumeGame(game)"
              :disabled="isLoading"
            >
              Riprendi
            </BaseButton>

            <BaseButton
              v-else
              variant="outline"
              size="sm"
              @click.stop="viewGameStats(game)"
              :disabled="isLoading"
            >
              Visualizza
            </BaseButton>

            <BaseButton
              variant="ghost"
              size="sm"
              icon="⚙️"
              @click.stop="showGameOptions(game)"
              :disabled="isLoading"
              aria-label="Opzioni partita"
            />
          </div>

          <div class="game-meta">
            <span class="last-played">{{ game.last_played }}</span>
          </div>
        </template>
      </BaseCard>
    </div>

    <!-- Game Options Modal -->
    <BaseModal
      :is-open="showOptionsModal"
      title="Opzioni Partita"
      @close="closeOptionsModal"
      @confirm="executeSelectedAction"
      :confirm-text="selectedAction?.confirmText || 'Conferma'"
      :confirm-variant="selectedAction?.variant || 'primary'"
      :loading="isExecutingAction"
    >
      <div class="modal-content">
        <div class="selected-game-info" v-if="selectedGame">
          <h4>{{ selectedGame.name || `Partita del ${formatDate(selectedGame.created_at)}` }}</h4>
          <p class="game-status">Stato: {{ getStatusText(selectedGame.status) }}</p>
        </div>

        <div class="action-options">
          <div class="option-group">
            <h5>Azioni Partita</h5>
            <label class="option-item">
              <input
                type="radio"
                v-model="selectedActionType"
                value="rename"
                name="gameAction"
              />
              <span class="option-content">
                <span class="option-icon">✏️</span>
                <span class="option-text">Rinomina Partita</span>
              </span>
            </label>

            <label class="option-item">
              <input
                type="radio"
                v-model="selectedActionType"
                value="duplicate"
                name="gameAction"
              />
              <span class="option-content">
                <span class="option-icon">📄</span>
                <span class="option-text">Duplica Partita</span>
              </span>
            </label>

            <label class="option-item">
              <input
                type="radio"
                v-model="selectedActionType"
                value="export"
                name="gameAction"
              />
              <span class="option-content">
                <span class="option-icon">📤</span>
                <span class="option-text">Esporta Dati</span>
              </span>
            </label>
          </div>

          <div class="option-group danger-zone">
            <h5>Zona Pericolosa</h5>
            <label class="option-item option-item--danger">
              <input
                type="radio"
                v-model="selectedActionType"
                value="delete"
                name="gameAction"
              />
              <span class="option-content">
                <span class="option-icon">🗑️</span>
                <span class="option-text">Elimina Partita</span>
              </span>
            </label>
          </div>
        </div>

        <!-- Rename Input -->
        <div v-if="selectedActionType === 'rename'" class="rename-section">
          <BaseInput
            v-model="newGameName"
            label="Nuovo Nome"
            placeholder="Inserisci il nuovo nome..."
            :error-message="renameError"
          />
        </div>
      </div>
    </BaseModal>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useGameStore } from '@/js/stores/game'
import { useNotificationStore } from '@/js/stores/notifications'
import { formatCurrency, formatDate } from '@/js/utils/helpers'
import StatusBadge from '@/js/components/shared/StatusBadge.vue'

// Stores
const gameStore = useGameStore()
const notificationStore = useNotificationStore()
const router = useRouter()

// Reactive state
const isLoading = ref(false)
const games = ref([])
const showOptionsModal = ref(false)
const selectedGame = ref(null)
const selectedActionType = ref('')
const newGameName = ref('')
const renameError = ref('')
const isExecutingAction = ref(false)

// Computed
const selectedAction = computed(() => {
  const actions = {
    rename: {
      confirmText: 'Rinomina',
      variant: 'primary'
    },
    duplicate: {
      confirmText: 'Duplica',
      variant: 'secondary'
    },
    export: {
      confirmText: 'Esporta',
      variant: 'secondary'
    },
    delete: {
      confirmText: 'Elimina',
      variant: 'danger'
    }
  }

  return actions[selectedActionType.value] || null
})

// Methods
const loadGames = async () => {
  isLoading.value = true
  try {
    const response = await gameStore.fetchGamesList()
    games.value = response.data || []
  } catch (error) {
    notificationStore.error('Errore nel caricamento delle partite')
    console.error('Error loading games:', error)
  } finally {
    isLoading.value = false
  }
}

const createNewGame = () => {
  router.push({ name: 'NewGame' })
}

const selectGame = (game) => {
  if (game.status === 'active' || game.status === 'paused') {
    resumeGame(game)
  } else {
    viewGameStats(game)
  }
}

const resumeGame = async (game) => {
  try {
    await gameStore.loadGame(game.id)
    router.push({ name: 'GameDashboard' })
  } catch (error) {
    notificationStore.error('Errore nel caricamento della partita')
    console.error('Error resuming game:', error)
  }
}

const viewGameStats = (game) => {
  router.push({
    name: 'GameStats',
    params: { id: game.id }
  })
}

const showGameOptions = (game) => {
  selectedGame.value = game
  selectedActionType.value = ''
  newGameName.value = game.name || ''
  renameError.value = ''
  showOptionsModal.value = true
}

const closeOptionsModal = () => {
  showOptionsModal.value = false
  selectedGame.value = null
  selectedActionType.value = ''
  newGameName.value = ''
  renameError.value = ''
}

const executeSelectedAction = async () => {
  if (!selectedActionType.value || !selectedGame.value) return

  isExecutingAction.value = true
  try {
    switch (selectedActionType.value) {
      case 'rename':
        await renameGame()
        break
      case 'duplicate':
        await duplicateGame()
        break
      case 'export':
        await exportGame()
        break
      case 'delete':
        await deleteGame()
        break
    }

    closeOptionsModal()
    await loadGames() // Refresh list
  } catch (error) {
    console.error('Error executing action:', error)
  } finally {
    isExecutingAction.value = false
  }
}

const renameGame = async () => {
  if (!newGameName.value.trim()) {
    renameError.value = 'Il nome non può essere vuoto'
    return
  }

  try {
    await gameStore.updateGame(selectedGame.value.id, {
      name: newGameName.value.trim()
    })
    notificationStore.success('Partita rinominata con successo')
  } catch (error) {
    renameError.value = 'Errore nel rinominare la partita'
    throw error
  }
}

const duplicateGame = async () => {
  try {
    await gameStore.duplicateGame(selectedGame.value.id)
    notificationStore.success('Partita duplicata con successo')
  } catch (error) {
    notificationStore.error('Errore nella duplicazione della partita')
    throw error
  }
}

const exportGame = async () => {
  try {
    await gameStore.exportGame(selectedGame.value.id)
    notificationStore.success('Dati esportati con successo')
  } catch (error) {
    notificationStore.error('Errore nell\'esportazione dei dati')
    throw error
  }
}

const deleteGame = async () => {
  try {
    await gameStore.deleteGame(selectedGame.value.id)
    notificationStore.success('Partita eliminata con successo')
  } catch (error) {
    notificationStore.error('Errore nell\'eliminazione della partita')
    throw error
  }
}

const getMoneyClass = (money) => {
  if (money < 0) return 'stat-value--danger'
  if (money < 1000) return 'stat-value--warning'
  return 'stat-value--success'
}

const getStatusText = (status) => {
  const statusMap = {
    active: 'Attiva',
    paused: 'In Pausa',
    game_over: 'Terminata'
  }
  return statusMap[status] || status
}

const formatPlayTime = (hours) => {
  if (hours < 1) return `${Math.round(hours * 60)}m`
  return `${Math.round(hours * 10) / 10}h`
}

// Lifecycle
onMounted(() => {
  loadGames()
})
</script>

<style scoped>
.game-list-view {
  @apply min-h-screen p-6;
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

/* Loading Grid */
.loading-grid {
  @apply grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6;
}

.game-card-skeleton {
  @apply bg-white rounded-lg border border-neutral-200 p-6 animate-pulse;
}

.skeleton-header {
  @apply h-6 bg-neutral-200 rounded mb-4;
}

.skeleton-content {
  @apply space-y-2 mb-4;
}

.skeleton-line {
  @apply h-4 bg-neutral-200 rounded;
}

.skeleton-line.short {
  @apply w-3/4;
}

.skeleton-footer {
  @apply h-8 bg-neutral-200 rounded;
}

/* Empty State */
.empty-state {
  @apply text-center py-16;
}

.empty-icon {
  @apply text-6xl mb-4;
}

.empty-title {
  @apply text-2xl font-bold text-neutral-900 mb-2;
}

.empty-description {
  @apply text-neutral-600 mb-8 max-w-md mx-auto;
}

.empty-cta {
  @apply mx-auto;
}

/* Games Grid */
.games-grid {
  @apply grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6;
}

.game-card {
  @apply transition-all duration-200;
}

.game-card--active {
  @apply ring-2 ring-brand-500 ring-opacity-50;
}

.game-card:hover {
  @apply shadow-lg transform -translate-y-1;
}

/* Game Header */
.game-header {
  @apply flex items-center justify-between;
}

.game-title {
  @apply font-semibold text-neutral-900 truncate flex-1 mr-2;
}

/* Game Stats */
.game-stats {
  @apply space-y-3;
}

.stat-row {
  @apply grid grid-cols-2 gap-3;
}

.stat-item {
  @apply flex items-center space-x-2;
}

.stat-icon {
  @apply text-lg;
}

.stat-content {
  @apply flex flex-col;
}

.stat-label {
  @apply text-xs text-neutral-600;
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

/* Game Actions */
.game-actions {
  @apply flex items-center justify-between mb-2;
}

.game-meta {
  @apply text-xs text-neutral-500;
}

/* Modal Content */
.modal-content {
  @apply space-y-6;
}

.selected-game-info {
  @apply bg-neutral-50 rounded-lg p-4;
}

.selected-game-info h4 {
  @apply font-semibold text-neutral-900 mb-1;
}

.game-status {
  @apply text-sm text-neutral-600;
}

.action-options {
  @apply space-y-6;
}

.option-group {
  @apply space-y-3;
}

.option-group h5 {
  @apply font-semibold text-neutral-900 mb-3;
}

.option-group.danger-zone {
  @apply border-t border-neutral-200 pt-6;
}

.option-group.danger-zone h5 {
  @apply text-danger-700;
}

.option-item {
  @apply flex items-center p-3 rounded-lg cursor-pointer;
  @apply border border-neutral-200 hover:bg-neutral-50;
  @apply transition-colors duration-200;
}

.option-item--danger {
  @apply border-danger-200 hover:bg-danger-50;
}

.option-item input[type="radio"] {
  @apply mr-3;
}

.option-content {
  @apply flex items-center flex-1;
}

.option-icon {
  @apply mr-2;
}

.option-text {
  @apply text-sm font-medium;
}

.option-item--danger .option-text {
  @apply text-danger-700;
}

.rename-section {
  @apply border-t border-neutral-200 pt-6;
}

/* Responsive */
@media (max-width: 768px) {
  .games-grid {
    @apply grid-cols-1 gap-4;
  }

  .game-list-view {
    @apply p-4;
  }

  .page-header {
    @apply mb-6;
  }

  .page-title {
    @apply text-2xl;
  }

  .title-icon {
    @apply text-3xl mr-2;
  }
}
</style>