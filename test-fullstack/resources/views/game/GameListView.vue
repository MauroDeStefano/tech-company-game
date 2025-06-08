<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-white">
    <!-- Page Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <!-- Header Content -->
          <div class="flex-1">
            <div class="flex items-center gap-3 mb-2">
              <span class="text-4xl">🎮</span>
              <h1 class="text-3xl font-bold text-gray-900 tracking-tight">
                Le Tue Partite
              </h1>
            </div>
            <p class="text-lg text-gray-600">
              Gestisci e riprendi le tue partite salvate
            </p>
          </div>

          <!-- Header Actions -->
          <div class="flex-shrink-0">
            <button
              @click="createNewGame"
              :disabled="isLoading"
              class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-semibold rounded-xl transition-all duration-200 transform hover:scale-105 disabled:hover:scale-100 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 shadow-lg"
            >
              <span class="text-xl">➕</span>
              Nuova Partita
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Loading Skeleton -->
      <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="i in 3" :key="i" class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 animate-pulse">
          <!-- Skeleton Header -->
          <div class="flex items-center justify-between mb-4">
            <div class="h-6 bg-gray-200 rounded-lg w-3/4"></div>
            <div class="h-5 w-16 bg-gray-200 rounded-full"></div>
          </div>
          
          <!-- Skeleton Content -->
          <div class="space-y-3 mb-6">
            <div class="flex items-center gap-3">
              <div class="w-6 h-6 bg-gray-200 rounded"></div>
              <div class="h-4 bg-gray-200 rounded w-1/2"></div>
            </div>
            <div class="flex items-center gap-3">
              <div class="w-6 h-6 bg-gray-200 rounded"></div>
              <div class="h-4 bg-gray-200 rounded w-2/3"></div>
            </div>
          </div>
          
          <!-- Skeleton Footer -->
          <div class="flex items-center justify-between">
            <div class="h-8 w-20 bg-gray-200 rounded-lg"></div>
            <div class="h-8 w-8 bg-gray-200 rounded-lg"></div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else-if="games.length === 0" class="text-center py-16">
        <div class="max-w-md mx-auto">
          <div class="text-8xl mb-6">🎯</div>
          <h3 class="text-2xl font-bold text-gray-900 mb-4">
            Nessuna Partita Trovata
          </h3>
          <p class="text-lg text-gray-600 mb-8 leading-relaxed">
            Non hai ancora creato nessuna partita. Inizia subito a gestire la tua software house!
          </p>
          <button
            @click="createNewGame"
            class="inline-flex items-center gap-3 px-8 py-4 bg-blue-600 hover:bg-blue-700 text-white font-bold text-lg rounded-xl transition-all duration-200 transform hover:scale-105 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 shadow-xl"
          >
            <span class="text-2xl">🚀</span>
            Crea la Tua Prima Partita
          </button>
        </div>
      </div>

      <!-- Games Grid -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <div
          v-for="game in games"
          :key="game.id"
          class="bg-white rounded-2xl shadow-lg border border-gray-100 hover:shadow-xl hover:border-gray-200 transition-all duration-300 cursor-pointer group"
          :class="{ 
            'ring-2 ring-blue-500 ring-offset-2': game.status === 'active',
            'hover:ring-2 hover:ring-gray-300 hover:ring-offset-2': game.status !== 'active'
          }"
          @click="selectGame(game)"
        >
          <!-- Game Header -->
          <div class="p-6 pb-4">
            <div class="flex items-start justify-between mb-4">
              <h3 class="text-lg font-bold text-gray-900 group-hover:text-blue-600 transition-colors duration-200 flex-1 pr-3">
                {{ game.name || `Partita del ${formatDate(game.created_at)}` }}
              </h3>
              
              <!-- Status Badge -->
              <span 
                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold"
                :class="{
                  'bg-green-100 text-green-800': game.status === 'active',
                  'bg-yellow-100 text-yellow-800': game.status === 'paused',
                  'bg-gray-100 text-gray-800': game.status === 'game_over'
                }"
              >
                <span 
                  v-if="game.status === 'active'"
                  class="w-2 h-2 bg-green-500 rounded-full mr-1.5 animate-pulse"
                ></span>
                {{ getStatusText(game.status) }}
              </span>
            </div>

            <!-- Game Stats Grid -->
            <div class="grid grid-cols-2 gap-4">
              <!-- Patrimonio -->
              <div class="flex items-center gap-2">
                <span class="text-xl">💰</span>
                <div class="min-w-0 flex-1">
                  <div class="text-xs text-gray-500 font-medium">Patrimonio</div>
                  <div 
                    class="font-bold text-sm truncate"
                    :class="{
                      'text-red-600': game.money < 0,
                      'text-yellow-600': game.money >= 0 && game.money < 1000,
                      'text-green-600': game.money >= 1000
                    }"
                  >
                    {{ formatCurrency(game.money) }}
                  </div>
                </div>
              </div>

              <!-- Team Size -->
              <div class="flex items-center gap-2">
                <span class="text-xl">👥</span>
                <div class="min-w-0 flex-1">
                  <div class="text-xs text-gray-500 font-medium">Team</div>
                  <div class="font-bold text-sm text-gray-900">
                    {{ game.team_size }}
                  </div>
                </div>
              </div>

              <!-- Progetti Completati -->
              <div class="flex items-center gap-2">
                <span class="text-xl">✅</span>
                <div class="min-w-0 flex-1">
                  <div class="text-xs text-gray-500 font-medium">Progetti</div>
                  <div class="font-bold text-sm text-gray-900">
                    {{ game.projects_completed }}
                  </div>
                </div>
              </div>

              <!-- Tempo di Gioco -->
              <div class="flex items-center gap-2">
                <span class="text-xl">⏱️</span>
                <div class="min-w-0 flex-1">
                  <div class="text-xs text-gray-500 font-medium">Tempo</div>
                  <div class="font-bold text-sm text-gray-900">
                    {{ formatPlayTime(game.play_time_hours) }}
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Game Actions Footer -->
          <div class="p-6 pt-0 border-t border-gray-100">
            <div class="flex items-center justify-between">
              <!-- Main Action Button -->
              <button
                v-if="game.status === 'active'"
                @click.stop="resumeGame(game)"
                :disabled="isLoading"
                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-300 text-white font-semibold text-sm rounded-lg transition-colors duration-200"
              >
                Continua
              </button>

              <button
                v-else-if="game.status === 'paused'"
                @click.stop="resumeGame(game)"
                :disabled="isLoading"
                class="px-4 py-2 bg-green-600 hover:bg-green-700 disabled:bg-gray-300 text-white font-semibold text-sm rounded-lg transition-colors duration-200"
              >
                Riprendi
              </button>

              <button
                v-else
                @click.stop="viewGameStats(game)"
                :disabled="isLoading"
                class="px-4 py-2 border border-gray-300 hover:border-gray-400 hover:bg-gray-50 text-gray-700 font-semibold text-sm rounded-lg transition-colors duration-200"
              >
                Visualizza
              </button>

              <!-- Options Button -->
              <button
                @click.stop="showGameOptions(game)"
                :disabled="isLoading"
                class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors duration-200"
                aria-label="Opzioni partita"
              >
                <span class="text-lg">⚙️</span>
              </button>
            </div>

            <!-- Last Played -->
            <div class="mt-3 text-xs text-gray-500">
              {{ game.last_played }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Game Options Modal -->
    <div 
      v-if="showOptionsModal" 
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
      @click="closeOptionsModal"
    >
      <div 
        class="bg-white rounded-2xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto"
        @click.stop
      >
        <!-- Modal Header -->
        <div class="p-6 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <h3 class="text-xl font-bold text-gray-900">Opzioni Partita</h3>
            <button
              @click="closeOptionsModal"
              class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors duration-200"
            >
              <span class="text-lg">✕</span>
            </button>
          </div>
          
          <!-- Selected Game Info -->
          <div v-if="selectedGame" class="mt-4 p-4 bg-gray-50 rounded-xl">
            <h4 class="font-semibold text-gray-900">
              {{ selectedGame.name || `Partita del ${formatDate(selectedGame.created_at)}` }}
            </h4>
            <p class="text-sm text-gray-600 mt-1">
              Stato: {{ getStatusText(selectedGame.status) }}
            </p>
          </div>
        </div>

        <!-- Modal Body -->
        <div class="p-6 space-y-6">
          <!-- Action Options -->
          <div class="space-y-4">
            <h5 class="font-semibold text-gray-900">Azioni Partita</h5>
            
            <!-- Rename Option -->
            <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-xl hover:bg-gray-50 cursor-pointer transition-colors duration-200">
              <input
                type="radio"
                v-model="selectedActionType"
                value="rename"
                name="gameAction"
                class="w-4 h-4 text-blue-600 focus:ring-blue-500"
              />
              <span class="text-xl">✏️</span>
              <span class="text-sm font-medium text-gray-900">Rinomina Partita</span>
            </label>

            <!-- Duplicate Option -->
            <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-xl hover:bg-gray-50 cursor-pointer transition-colors duration-200">
              <input
                type="radio"
                v-model="selectedActionType"
                value="duplicate"
                name="gameAction"
                class="w-4 h-4 text-blue-600 focus:ring-blue-500"
              />
              <span class="text-xl">📄</span>
              <span class="text-sm font-medium text-gray-900">Duplica Partita</span>
            </label>

            <!-- Export Option -->
            <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-xl hover:bg-gray-50 cursor-pointer transition-colors duration-200">
              <input
                type="radio"
                v-model="selectedActionType"
                value="export"
                name="gameAction"
                class="w-4 h-4 text-blue-600 focus:ring-blue-500"
              />
              <span class="text-xl">📤</span>
              <span class="text-sm font-medium text-gray-900">Esporta Dati</span>
            </label>
          </div>

          <!-- Danger Zone -->
          <div class="space-y-4">
            <h5 class="font-semibold text-red-700">Zona Pericolosa</h5>
            
            <!-- Delete Option -->
            <label class="flex items-center gap-3 p-3 border border-red-200 bg-red-50 rounded-xl hover:bg-red-100 cursor-pointer transition-colors duration-200">
              <input
                type="radio"
                v-model="selectedActionType"
                value="delete"
                name="gameAction"
                class="w-4 h-4 text-red-600 focus:ring-red-500"
              />
              <span class="text-xl">🗑️</span>
              <span class="text-sm font-medium text-red-700">Elimina Partita</span>
            </label>
          </div>

          <!-- Rename Input -->
          <div v-if="selectedActionType === 'rename'" class="space-y-2">
            <label class="block text-sm font-semibold text-gray-700">
              Nuovo Nome
            </label>
            <input
              v-model="newGameName"
              type="text"
              placeholder="Inserisci il nuovo nome..."
              class="block w-full px-4 py-3 border border-gray-300 rounded-xl bg-white text-gray-900 placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
              :class="{
                'border-red-300 focus:ring-red-500 focus:border-red-500': renameError
              }"
            />
            <p v-if="renameError" class="text-red-600 text-sm font-medium">
              {{ renameError }}
            </p>
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="p-6 border-t border-gray-200 flex items-center gap-3">
          <button
            @click="closeOptionsModal"
            class="flex-1 px-4 py-3 border border-gray-300 hover:border-gray-400 hover:bg-gray-50 text-gray-700 font-semibold rounded-xl transition-colors duration-200"
          >
            Annulla
          </button>
          <button
            @click="executeSelectedAction"
            :disabled="!selectedActionType || isExecutingAction"
            class="flex-1 px-4 py-3 font-semibold rounded-xl transition-all duration-200"
            :class="{
              'bg-blue-600 hover:bg-blue-700 text-white': selectedActionType && selectedActionType !== 'delete',
              'bg-red-600 hover:bg-red-700 text-white': selectedActionType === 'delete',
              'bg-gray-300 text-gray-500 cursor-not-allowed': !selectedActionType || isExecutingAction
            }"
          >
            <span v-if="isExecutingAction" class="flex items-center justify-center gap-2">
              <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Elaborazione...
            </span>
            <span v-else>
              {{ selectedAction?.confirmText || 'Conferma' }}
            </span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useGameStore } from '@/js/stores/game'
import { useNotificationStore } from '@/js/stores/notifications'
import { formatCurrency, formatDate } from '@/js/utils/helpers'

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