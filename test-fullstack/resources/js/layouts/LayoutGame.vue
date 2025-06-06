<template>
  <div class="game-layout">
    <!-- Game Header con stats e controlli -->
    <GameHeader class="game-layout__header" />

    <!-- Main game area -->
    <div class="game-layout__main">
      <!-- Desktop Sidebar / Mobile Bottom Navigation -->
      <NavigationBar class="game-layout__nav" />

      <!-- Content Area -->
      <main class="game-layout__content">
        <!-- Loading overlay quando cambia sezione -->
        <div v-if="isLoading" class="loading-overlay">
          <BaseSpinner size="large" />
          <p class="loading-text">Caricamento...</p>
        </div>

        <!-- Route content -->
        <router-view v-slot="{ Component, route }">
          <transition
            name="page-transition"
            mode="out-in"
            @before-enter="isLoading = true"
            @after-enter="isLoading = false"
          >
            <component :is="Component" :key="route.path" />
          </transition>
        </router-view>
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
    <div v-if="isGamePaused" class="pause-overlay">
      <div class="pause-content">
        <h2>⏸️ Gioco in Pausa</h2>
        <p>Il gioco è attualmente in pausa</p>
        <BaseButton @click="resumeGame" variant="primary">
          Riprendi Gioco
        </BaseButton>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
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
        router.push({ name: 'GameDashboard' })
        break
      case '2':
        event.preventDefault()
        router.push({ name: 'Production' })
        break
      case '3':
        event.preventDefault()
        router.push({ name: 'Sales' })
        break
      case '4':
        event.preventDefault()
        router.push({ name: 'HR' })
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

<style scoped>
.game-layout {
  @apply min-h-screen bg-neutral-50 flex flex-col;
}

.game-layout__header {
  @apply sticky top-0 z-30 bg-white border-b border-neutral-200 shadow-sm;
}

.game-layout__main {
  @apply flex-1 flex;
}

.game-layout__nav {
  /* Mobile: fixed bottom navigation */
  @apply fixed bottom-0 left-0 right-0 z-40 bg-white border-t border-neutral-200;

  /* Desktop: sidebar */
  @apply lg:relative lg:bottom-auto lg:w-64 lg:border-r lg:border-t-0;
}

.game-layout__content {
  @apply flex-1 relative;
  /* Add bottom padding on mobile for bottom nav */
  @apply pb-16 lg:pb-0;
  /* Add padding for content */
  @apply p-4 lg:p-6;
}

/* Loading overlay */
.loading-overlay {
  @apply absolute inset-0 bg-white bg-opacity-90 flex flex-col items-center justify-center z-20;
}

.loading-text {
  @apply mt-4 text-neutral-600 font-medium;
}

/* Page transitions */
.page-transition-enter-active,
.page-transition-leave-active {
  transition: opacity 0.2s ease-in-out, transform 0.2s ease-in-out;
}

.page-transition-enter-from {
  opacity: 0;
  transform: translateX(20px);
}

.page-transition-leave-to {
  opacity: 0;
  transform: translateX(-20px);
}

/* Pause overlay */
.pause-overlay {
  @apply fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50;
}

.pause-content {
  @apply bg-white rounded-lg p-8 text-center max-w-md mx-4;
}

.pause-content h2 {
  @apply text-2xl font-bold text-neutral-900 mb-4;
}

.pause-content p {
  @apply text-neutral-600 mb-6;
}

/* Mobile optimizations */
@media (max-width: 1023px) {
  .game-layout__content {
    /* Reduce padding on mobile */
    @apply p-3;
  }

  .loading-overlay {
    @apply pb-16; /* Account for bottom nav */
  }
}

/* Desktop optimizations */
@media (min-width: 1024px) {
  .game-layout__nav {
    @apply flex-shrink-0;
  }
}

/* Responsive text sizing */
.loading-text {
  @apply text-sm lg:text-base;
}

/* Focus management for accessibility */
.game-layout:focus-within .game-layout__content {
  @apply outline-none;
}

/* Smooth scrolling */
.game-layout__content {
  scroll-behavior: smooth;
}
</style>