<template>
  <div class="min-h-screen bg-gray-100 flex flex-col">
    <!-- Game Header con stats e controlli -->
    <GameHeader class="relative z-40" />

    <!-- Main game area -->
    <div class="flex-1 flex overflow-hidden">
      <!-- Desktop Sidebar / Mobile Bottom Navigation -->
      <NavigationBar />

      <!-- Content Area -->
      <main class="flex-1 overflow-hidden lg:ml-64">
        <!-- Loading overlay quando cambia sezione -->
        <div v-if="isLoading" class="fixed inset-0 bg-white bg-opacity-75 flex items-center justify-center z-50">
          <div class="text-center">
            <svg class="animate-spin h-12 w-12 text-blue-600 mx-auto mb-4" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
            </svg>
            <p class="text-lg text-gray-600">Caricamento...</p>
          </div>
        </div>

        <!-- Route content -->
        <div class="h-full overflow-y-auto">
          <router-view v-slot="{ Component, route }">
            <transition
              enter-active-class="transition-all duration-300"
              leave-active-class="transition-all duration-300"
              enter-from-class="opacity-0 translate-y-4"
              enter-to-class="opacity-100 translate-y-0"
              leave-from-class="opacity-100 translate-y-0"
              leave-to-class="opacity-0 -translate-y-4"
              mode="out-in"
              @before-enter="isLoading = true"
              @after-enter="isLoading = false"
            >
              <component :is="Component" :key="route.path" />
            </transition>
          </router-view>
        </div>
      </main>
    </div>

    <!-- Mobile hamburger menu overlay -->
    <SidebarMenu
      v-if="isMobile"
      :is-open="isSidebarOpen"
      @close="isSidebarOpen = false"
    />

    <!-- Global notifications -->
    <NotificationContainer />

    <!-- Game over modal -->
    <GameOverModal
      :is-open="isGameOver"
      @restart="handleGameRestart"
      @new-game="handleNewGame"
    />

    <!-- Pause overlay -->
    <div v-if="isGamePaused" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-2xl shadow-2xl p-8 text-center max-w-md mx-4">
        <div class="text-6xl mb-4">⏸️</div>
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Gioco in Pausa</h2>
        <p class="text-gray-600 mb-6">Il gioco è attualmente in pausa</p>
        <button
          @click="resumeGame"
          class="w-full px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors duration-200"
        >
          Riprendi Gioco
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, provide } from 'vue'
import { useRouter } from 'vue-router'
import { useGameStore } from '@/js/stores/game'
import { useNotificationStore } from '@/js/stores/notifications'

// Components
import GameHeader from '@/js/components/layout/GameHeader.vue'
import NavigationBar from '@/js/components/layout/NavigationBar.vue'
import SidebarMenu from '@/js/components/layout/SidebarMenu.vue'
import NotificationContainer from '@/js/components/shared/NotificationContainer.vue'
import GameOverModal from '@/js/components/game/shared/GameOverModal.vue'

// Stores
const gameStore = useGameStore()
const notificationStore = useNotificationStore()
const router = useRouter()

// Reactive state
const isLoading = ref(false)
const isSidebarOpen = ref(false)
const isMobile = ref(false)

// Computed properties
const isGameOver = computed(() => gameStore.currentGame?.status === 'game_over')
const isGamePaused = computed(() => gameStore.currentGame?.status === 'paused')

// Methods
const checkMobile = () => {
  isMobile.value = window.innerWidth < 1024
}

const handleResize = () => {
  checkMobile()
  // Chiudi sidebar su desktop
  if (!isMobile.value && isSidebarOpen.value) {
    isSidebarOpen.value = false
  }
}

const handleGameRestart = async () => {
  try {
    await gameStore.restartGame()
    notificationStore.success('Gioco riavviato con successo!')
  } catch (error) {
    notificationStore.error('Errore nel riavvio del gioco')
  }
}

const handleNewGame = () => {
  router.push({ name: 'NewGame' })
}

const resumeGame = async () => {
  try {
    await gameStore.resumeGame()
    notificationStore.success('Gioco ripreso!')
  } catch (error) {
    notificationStore.error('Errore nel riprendere il gioco')
  }
}

// Auto-save functionality
const setupAutoSave = () => {
  const autoSaveInterval = setInterval(async () => {
    if (gameStore.currentGame && gameStore.currentGame.status === 'active') {
      try {
        await gameStore.saveGame()
        console.log('✅ Auto-save completed')
      } catch (error) {
        console.error('❌ Auto-save failed:', error)
      }
    }
  }, 30000) // Auto-save ogni 30 secondi

  return autoSaveInterval
}

// Keyboard shortcuts
const handleKeydown = (event) => {
  // ESC per aprire/chiudere menu mobile
  if (event.key === 'Escape' && isMobile.value) {
    isSidebarOpen.value = !isSidebarOpen.value
  }

  // Shortcuts per navigazione veloce
  if (event.ctrlKey || event.metaKey) {
    switch (event.key) {
      case '1':
        event.preventDefault()
        router.push('/game/dashboard')
        break
      case '2':
        event.preventDefault()
        router.push('/game/production')
        break
      case '3':
        event.preventDefault()
        router.push('/game/sales')
        break
      case '4':
        event.preventDefault()
        router.push('/game/hr')
        break
      case 's':
        event.preventDefault()
        gameStore.saveGame()
        break
    }
  }
}

// Page visibility API for auto-pause
const handleVisibilityChange = () => {
  if (document.hidden && gameStore.currentGame?.status === 'active') {
    gameStore.pauseGame()
    console.log('🔄 Game auto-paused (tab hidden)')
  }
}

// Lifecycle
let autoSaveInterval
onMounted(() => {
  checkMobile()
  window.addEventListener('resize', handleResize)
  window.addEventListener('keydown', handleKeydown)
  document.addEventListener('visibilitychange', handleVisibilityChange)

  // Setup auto-save
  autoSaveInterval = setupAutoSave()

  // Load current game if needed
  if (!gameStore.currentGame) {
    gameStore.loadCurrentGame()
  }
})

onUnmounted(() => {
  window.removeEventListener('resize', handleResize)
  window.removeEventListener('keydown', handleKeydown)
  document.removeEventListener('visibilitychange', handleVisibilityChange)

  if (autoSaveInterval) {
    clearInterval(autoSaveInterval)
  }
})

// Provide navigation state to child components
provide('navigation', {
  isSidebarOpen,
  toggleSidebar: () => isSidebarOpen.value = !isSidebarOpen.value
})
</script>
