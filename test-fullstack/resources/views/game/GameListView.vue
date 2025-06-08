<!-- GameListView.vue - Correzione completa per nuova struttura Resource -->

<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-white">
    <!-- Page Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
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
            <!-- 🎯 AGGIUNTA: Meta info da API -->
            <div v-if="meta" class="mt-2 flex gap-4 text-sm text-gray-500">
              <span>📊 {{ meta.total_games }} partite totali</span>
              <span v-if="meta.active_games > 0">🟢 {{ meta.active_games }} attive</span>
              <span v-if="meta.paused_games > 0">⏸️ {{ meta.paused_games }} in pausa</span>
            </div>
          </div>

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
          <div class="flex items-center justify-between mb-4">
            <div class="h-6 bg-gray-200 rounded-lg w-3/4"></div>
            <div class="h-5 w-16 bg-gray-200 rounded-full"></div>
          </div>
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
                {{ game.name }}
              </h3>
              
              <!-- 🎯 CORREZIONE: Status Badge con nuova struttura -->
              <span 
                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold"
                :class="game.status_badge_class"
              >
                <span 
                  v-if="game.status === 'active'"
                  class="w-2 h-2 bg-green-500 rounded-full mr-1.5 animate-pulse"
                ></span>
                {{ game.status_label }}
              </span>
            </div>

            <!-- Game Stats Grid -->
            <div class="grid grid-cols-2 gap-4">
              <!-- 🎯 CORREZIONE: Patrimonio con classe CSS da Resource -->
              <div class="flex items-center gap-2">
                <span class="text-xl">💰</span>
                <div class="min-w-0 flex-1">
                  <div class="text-xs text-gray-500 font-medium">Patrimonio</div>
                  <div 
                    class="font-bold text-sm truncate"
                    :class="game.money.status_class"
                  >
                    {{ game.money.formatted }}
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
                    {{ game.play_time_formatted }}
                  </div>
                </div>
              </div>
            </div>

            <!-- 🎯 AGGIUNTA: Quick Stats indicators -->
            <div v-if="game.quick_stats" class="mt-4 flex gap-2">
              <span 
                v-if="!game.quick_stats.is_profitable" 
                class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-red-100 text-red-700"
              >
                ⚠️ In perdita
              </span>
              <span 
                v-if="game.quick_stats.has_active_projects" 
                class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-green-100 text-green-700"
              >
                🚀 Progetti attivi
              </span>
              <span 
                v-if="game.quick_stats.team_needs_expansion" 
                class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-yellow-100 text-yellow-700"
              >
                👥 Espandi team
              </span>
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

    <!-- Game Options Modal (rimane uguale) -->
    <!-- ... Modal content unchanged ... -->
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useGameStore } from '@/js/stores/game'
import { useNotificationStore } from '@/js/stores/notifications'

// Stores
const gameStore = useGameStore()
const notificationStore = useNotificationStore()
const router = useRouter()

// Reactive state
const isLoading = ref(false)
const games = ref([])
const meta = ref(null) // 🎯 AGGIUNTA: Meta data from API
const showOptionsModal = ref(false)
const selectedGame = ref(null)
const selectedActionType = ref('')
const newGameName = ref('')
const renameError = ref('')
const isExecutingAction = ref(false)
const refreshInterval = ref(null)

// 🎯 CORREZIONE: loadGames con gestione nuova struttura API
const loadGames = async (showLoader = true) => {
  if (showLoader) isLoading.value = true
  
  try {
    const response = await gameStore.fetchGamesList()
    
    // 🎯 CORREZIONE: Gestione nuova struttura Resource Collection
    if (response && response.data) {
      games.value = response.data || []
      meta.value = response.meta || null
      
      console.log('📊 Games loaded:', games.value.length)
      console.log('📈 Meta info:', meta.value)
    } else {
      games.value = []
      meta.value = null
    }
    
    if (games.value.length === 0 && !showLoader) {
      notificationStore.info('Non hai ancora partite salvate. Creane una nuova!')
    }
    
  } catch (error) {
    notificationStore.error('Errore nel caricamento delle partite')
    console.error('❌ Error loading games:', error)
    games.value = []
    meta.value = null
  } finally {
    if (showLoader) isLoading.value = false
  }
}

// Resto dei metodi rimane uguale...
const createNewGame = async () => {
  try {
    if (gameStore.hasCurrentGame) {
      const confirmed = window.confirm(
        'Hai già una partita in corso. Vuoi davvero creare una nuova partita?'
      )
      if (!confirmed) return
    }
    
    router.push({ name: 'NewGame' })
  } catch (error) {
    notificationStore.error('Errore nella navigazione')
  }
}

const resumeGame = async (game) => {
  try {
    isLoading.value = true
    
    console.log('🎮 Resuming game:', game.id)
    
    // 🎯 FIX 1: Verifica che il game.id sia valido
    if (!game?.id) {
      throw new Error('Game ID non valido')
    }
    
    // 🎯 FIX 2: Carica il game nello store con error handling migliore
    const loadedGame = await gameStore.loadGame(game.id)
    
    if (!loadedGame) {
      throw new Error('Impossibile caricare i dati della partita')
    }
    
    console.log('✅ Game loaded successfully:', loadedGame.id)
    
    // 🎯 FIX 3: Verifica che il router sia disponibile
    if (!router) {
      throw new Error('Router non disponibile')
    }
    
    // 🎯 FIX 4: Usa path invece di name se la route name non è definita
    // Cambia da { name: 'game' } a '/game/dashboard' o simile
    await router.push('/game/dashboard') // O la tua route corretta
    
  } catch (error) {
    console.error('❌ Error resuming game:', error)
    
    // 🎯 FIX 5: Error handling specifico
    if (error.message.includes('Network Error') || error.response?.status >= 500) {
      notificationStore.error('Errore di connessione. Controlla la tua connessione internet.')
    } else if (error.response?.status === 404) {
      notificationStore.error('Partita non trovata. Potrebbe essere stata eliminata.')
    } else if (error.response?.status === 403) {
      notificationStore.error('Non hai i permessi per accedere a questa partita.')
    } else {
      notificationStore.error(`Errore nel caricamento della partita: ${error.message}`)
    }
    
    // 🎯 FIX 6: Fallback - ricarica la lista delle partite
    try {
      await loadGames(false)
    } catch (refreshError) {
      console.error('❌ Error refreshing games list:', refreshError)
    }
    
  } finally {
    isLoading.value = false
  }
}

const selectGame = (game) => {
  if (game.status === 'active' || game.status === 'paused') {
    resumeGame(game)
  } else {
    viewGameStats(game)
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

const setupAutoRefresh = () => {
  refreshInterval.value = setInterval(() => {
    loadGames(false)
  }, 30000)
}

const cleanup = () => {
  if (refreshInterval.value) {
    clearInterval(refreshInterval.value)
    refreshInterval.value = null
  }
}

// Lifecycle
onMounted(async () => {
  await loadGames(true)
  setupAutoRefresh()
})

onUnmounted(() => {
  cleanup()
})

// Visibility change handling
document.addEventListener('visibilitychange', () => {
  if (document.hidden) {
    cleanup()
  } else {
    loadGames(false)
    setupAutoRefresh()
  }
})
</script>