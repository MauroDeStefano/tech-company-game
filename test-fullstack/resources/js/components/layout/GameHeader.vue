<template>
  <!-- Header fisso con z-index alto -->
  <header class="fixed top-0 left-0 right-0 bg-white shadow-sm border-b border-gray-200 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
      <!-- Logo e titolo gioco -->
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="text-2xl">🏢</div>
          <div>
            <h1 class="text-xl font-bold text-gray-900">{{ gameStore.currentGame?.name || 'Tech Company' }}</h1>
            <p class="text-sm text-gray-600">Software House Manager</p>
          </div>
        </div>

        <!-- Stats principali -->
        <div class="hidden lg:flex items-center gap-6">
          <!-- Patrimonio -->
          <div class="flex items-center gap-2 px-3 py-2 bg-blue-50 rounded-lg border border-blue-200">
            <div class="text-lg">💰</div>
            <div>
              <span class="block text-xs text-gray-600">Patrimonio</span>
              <span class="block text-sm font-semibold" :class="moneyColorClass">
                {{ formatCurrency(gameStore.currentGame?.money || 0) }}
              </span>
            </div>
          </div>

          <!-- Costi mensili -->
          <div class="flex items-center gap-2 px-3 py-2 bg-gray-50 rounded-lg">
            <div class="text-lg">📊</div>
            <div>
              <span class="block text-xs text-gray-600">Costi/Mese</span>
              <span class="block text-sm font-semibold text-gray-900">
                {{ formatCurrency(monthlyCosts) }}
              </span>
            </div>
          </div>

          <!-- Team size -->
          <div class="flex items-center gap-2 px-3 py-2 bg-gray-50 rounded-lg">
            <div class="text-lg">👥</div>
            <div>
              <span class="block text-xs text-gray-600">Team</span>
              <span class="block text-sm font-semibold text-gray-900">
                {{ totalTeamSize }}
              </span>
            </div>
          </div>

          <!-- Progetti attivi -->
          <div class="flex items-center gap-2 px-3 py-2 bg-gray-50 rounded-lg">
            <div class="text-lg">🚀</div>
            <div>
              <span class="block text-xs text-gray-600">Progetti</span>
              <span class="block text-sm font-semibold text-gray-900">
                {{ activeProjects }}/{{ totalProjects }}
              </span>
            </div>
          </div>
        </div>

        <!-- Azioni rapide -->
        <div class="flex items-center gap-4">
          <!-- Status del gioco -->
          <div>
            <StatusBadge
              :status="gameStore.currentGame?.status"
              :show-icon="true"
            />
          </div>

          <!-- Salvataggio automatico -->
          <div class="flex items-center gap-2 text-sm text-gray-600" :class="{ 'text-blue-600': isSaving }">
            <svg v-if="isSaving" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
            </svg>
            <span v-else>💾</span>
            <span class="hidden sm:inline">
              {{ isSaving ? 'Salvando...' : 'Salvato' }}
            </span>
          </div>

          <!-- Menu azioni -->
          <div class="relative action-menu">
            <button
              class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors duration-200"
              @click="showGameMenu = !showGameMenu"
              aria-label="Menu gioco"
            >
              ⚙️
            </button>

            <!-- Dropdown menu -->
            <div v-if="showGameMenu" class="absolute right-0 top-full mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-60" @click.stop>
              <button class="w-full flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-200" @click="saveGame">
                <span>💾</span>
                Salva Gioco
              </button>
              <button class="w-full flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-200" @click="pauseGame">
                <span>⏸️</span>
                Metti in Pausa
              </button>
              <div class="my-2 border-t border-gray-100"></div>
              <button class="w-full flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-200" @click="goToGameList">
                <span>📋</span>
                Lista Partite
              </button>
              <button class="w-full flex items-center gap-3 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors duration-200" @click="confirmEndGame">
                <span>🚪</span>
                Termina Partita
              </button>
            </div>
          </div>

          <!-- User menu -->
          <div class="relative user-menu">
            <button class="w-8 h-8 rounded-full overflow-hidden ring-2 ring-gray-200 hover:ring-gray-300 transition-all duration-200" @click="showUserMenu = !showUserMenu">
              <img
                v-if="authStore.user?.avatar_url"
                :src="authStore.user.avatar_url"
                :alt="authStore.user.name"
                class="w-full h-full object-cover"
              />
              <div v-else class="w-full h-full bg-gray-300 flex items-center justify-center text-sm font-medium text-gray-700">
                {{ authStore.userInitials }}
              </div>
            </button>

            <!-- User dropdown -->
            <div v-if="showUserMenu" class="absolute right-0 top-full mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-60" @click.stop>
              <div class="px-4 py-3 border-b border-gray-100">
                <p class="text-sm font-medium text-gray-900">{{ authStore.user?.name }}</p>
                <p class="text-xs text-gray-600">{{ authStore.user?.email }}</p>
              </div>
              <button class="w-full flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-200" @click="goToProfile">
                <span>👤</span>
                Profilo
              </button>
              <button class="w-full flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-200" @click="goToSettings">
                <span>⚙️</span>
                Impostazioni
              </button>
              <div class="my-2 border-t border-gray-100"></div>
              <button class="w-full flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-200" @click="logout">
                <span>🚪</span>
                Logout
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Game Over Warning -->
    <div v-if="isNearBankruptcy" class="bg-red-50 border-t border-red-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <span class="text-red-500">⚠️</span>
            <span class="text-sm text-red-800">
              Attenzione! Il patrimonio è in rosso. Completa progetti o riduci i costi per evitare il fallimento.
            </span>
          </div>
          <button
            class="px-3 py-1 text-xs font-medium text-red-800 bg-red-100 hover:bg-red-200 rounded-lg transition-colors duration-200"
            @click="dismissWarning"
          >
            Chiudi
          </button>
        </div>
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
  if (money < 0) return 'text-red-600'
  if (money < 1000) return 'text-yellow-600'
  return 'text-green-600'
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