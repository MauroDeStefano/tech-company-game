<template>
  <nav class="navigation-bar" :class="navigationClasses">
    <!-- Desktop Sidebar -->
    <div class="nav-desktop">
      <!-- Navigation Links -->
      <ul class="nav-list">
        <li v-for="route in navigationRoutes" :key="route.name" class="nav-item">
          <router-link
            :to="{ name: route.name }"
            class="nav-link"
            :class="{ 'nav-link--active': isActiveRoute(route.name) }"
            :aria-current="isActiveRoute(route.name) ? 'page' : undefined"
          >
            <div class="nav-link-content">
              <span class="nav-icon">{{ route.icon }}</span>
              <span class="nav-label">{{ route.title }}</span>

              <!-- Badge per notifiche -->
              <span
                v-if="route.badge"
                class="nav-badge"
                :class="`nav-badge--${route.badgeVariant || 'primary'}`"
              >
                {{ route.badge }}
              </span>
            </div>

            <!-- Descrizione per tooltip -->
            <div v-if="route.description" class="nav-description">
              {{ route.description }}
            </div>
          </router-link>
        </li>
      </ul>

      <!-- Shortcuts Info -->
      <div class="nav-shortcuts">
        <h4 class="shortcuts-title">⌨️ Shortcuts</h4>
        <ul class="shortcuts-list">
          <li v-for="shortcut in keyboardShortcuts" :key="shortcut.key" class="shortcut-item">
            <kbd class="shortcut-key">{{ shortcut.key }}</kbd>
            <span class="shortcut-label">{{ shortcut.label }}</span>
          </li>
        </ul>
      </div>
    </div>

    <!-- Mobile Bottom Navigation -->
    <div class="nav-mobile">
      <div class="mobile-nav-container">
        <router-link
          v-for="route in mobileRoutes"
          :key="route.name"
          :to="{ name: route.name }"
          class="mobile-nav-item"
          :class="{ 'mobile-nav-item--active': isActiveRoute(route.name) }"
        >
          <div class="mobile-nav-content">
            <span class="mobile-nav-icon">{{ route.icon }}</span>
            <span class="mobile-nav-label">{{ route.title }}</span>

            <!-- Badge mobile -->
            <span
              v-if="route.badge"
              class="mobile-nav-badge"
            >
              {{ route.badge }}
            </span>
          </div>
        </router-link>

        <!-- Hamburger Menu Button -->
        <button
          class="mobile-menu-button"
          @click="toggleMobileMenu"
          :aria-expanded="isMobileMenuOpen"
          aria-label="Menu"
        >
          <div class="hamburger-icon" :class="{ 'hamburger-icon--open': isMobileMenuOpen }">
            <span></span>
            <span></span>
            <span></span>
          </div>
          <span class="mobile-nav-label">Menu</span>
        </button>
      </div>
    </div>

    <!-- Mobile Overlay Menu -->
    <transition name="mobile-overlay">
      <div
        v-if="isMobileMenuOpen"
        class="mobile-overlay"
        @click="closeMobileMenu"
      >
        <div class="mobile-overlay-content" @click.stop>
          <div class="mobile-overlay-header">
            <h3 class="overlay-title">Menu Gioco</h3>
            <button
              class="overlay-close"
              @click="closeMobileMenu"
              aria-label="Chiudi menu"
            >
              ✕
            </button>
          </div>

          <div class="mobile-overlay-body">
            <!-- Game Stats (Mobile) -->
            <div class="mobile-stats">
              <div class="mobile-stat">
                <span class="mobile-stat-icon">💰</span>
                <span class="mobile-stat-label">Patrimonio</span>
                <span class="mobile-stat-value" :class="moneyColorClass">
                  {{ formatCurrency(gameStore.currentGame?.money || 0) }}
                </span>
              </div>

              <div class="mobile-stat">
                <span class="mobile-stat-icon">👥</span>
                <span class="mobile-stat-label">Team</span>
                <span class="mobile-stat-value">{{ totalTeamSize }}</span>
              </div>
            </div>

            <!-- Navigation Links (Mobile) -->
            <ul class="mobile-nav-list">
              <li v-for="route in allNavigationRoutes" :key="route.name" class="mobile-nav-item-full">
                <router-link
                  :to="{ name: route.name }"
                  class="mobile-nav-link"
                  :class="{ 'mobile-nav-link--active': isActiveRoute(route.name) }"
                  @click="closeMobileMenu"
                >
                  <span class="mobile-nav-icon-full">{{ route.icon }}</span>
                  <div class="mobile-nav-text">
                    <span class="mobile-nav-title">{{ route.title }}</span>
                    <span v-if="route.description" class="mobile-nav-desc">{{ route.description }}</span>
                  </div>
                  <span v-if="route.badge" class="mobile-nav-badge-full">{{ route.badge }}</span>
                </router-link>
              </li>
            </ul>

            <!-- Quick Actions (Mobile) -->
            <div class="mobile-actions">
              <button class="mobile-action-btn" @click="quickSave">
                <span class="mobile-action-icon">💾</span>
                <span class="mobile-action-label">Salva</span>
              </button>

              <button class="mobile-action-btn" @click="quickPause">
                <span class="mobile-action-icon">⏸️</span>
                <span class="mobile-action-label">Pausa</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </transition>

    <!-- Progress Indicator (Global) -->
    <div v-if="hasActiveProjects" class="global-progress">
      <div class="progress-content">
        <span class="progress-icon">⚡</span>
        <span class="progress-text">{{ activeProjectsCount }} progetti in corso</span>
      </div>
      <div class="progress-bar">
        <div
          class="progress-fill"
          :style="{ width: `${overallProgress}%` }"
        ></div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { ref, computed, inject, onMounted, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'
import { useGameStore } from '@/js/stores/game'
import { useNotificationStore } from '@/js/stores/notifications'
import { formatCurrency } from '@/js/utils/helpers'

// Stores
const gameStore = useGameStore()
const notificationStore = useNotificationStore()
const route = useRoute()

// Injected navigation state
const navigation = inject('navigation', {
  isSidebarOpen: ref(false),
  toggleSidebar: () => {}
})

// Reactive state
const isMobileMenuOpen = ref(false)

// Navigation routes configuration
const navigationRoutes = computed(() => [
  {
    name: 'GameDashboard',
    title: 'Dashboard',
    icon: '🏠',
    description: 'Panoramica generale del gioco'
  },
  {
    name: 'Production',
    title: 'Produzione',
    icon: '🏗️',
    description: 'Gestisci sviluppatori e progetti',
    badge: pendingProjectsCount.value > 0 ? pendingProjectsCount.value : null,
    badgeVariant: 'warning'
  },
  {
    name: 'Sales',
    title: 'Sales',
    icon: '💼',
    description: 'Gestisci commerciali e vendite',
    badge: availableSalesCount.value > 0 ? availableSalesCount.value : null,
    badgeVariant: 'success'
  },
  {
    name: 'HR',
    title: 'Risorse Umane',
    icon: '🧑‍💼',
    description: 'Assumi nuovo personale'
  }
])

// Mobile routes (subset for bottom nav)
const mobileRoutes = computed(() =>
  navigationRoutes.value.slice(0, 3) // Mostra solo i primi 3 su mobile
)

const allNavigationRoutes = computed(() => navigationRoutes.value)

// Keyboard shortcuts
const keyboardShortcuts = [
  { key: 'Ctrl+1', label: 'Dashboard' },
  { key: 'Ctrl+2', label: 'Produzione' },
  { key: 'Ctrl+3', label: 'Sales' },
  { key: 'Ctrl+4', label: 'HR' },
  { key: 'Ctrl+S', label: 'Salva' }
]

// Computed properties
const navigationClasses = computed(() => ({
  'navigation-bar--mobile-open': isMobileMenuOpen.value
}))

const totalTeamSize = computed(() => {
  if (!gameStore.currentGame) return 0
  const developers = gameStore.currentGame.developers?.length || 0
  const salesPeople = gameStore.currentGame.sales_people?.length || 0
  return developers + salesPeople
})

const moneyColorClass = computed(() => {
  const money = gameStore.currentGame?.money || 0
  if (money < 0) return 'text-danger-600'
  if (money < 1000) return 'text-warning-600'
  return 'text-success-600'
})

const pendingProjectsCount = computed(() => {
  if (!gameStore.currentGame?.projects) return 0
  return gameStore.currentGame.projects.filter(p => p.status === 'pending').length
})

const availableSalesCount = computed(() => {
  if (!gameStore.currentGame?.sales_people) return 0
  return gameStore.currentGame.sales_people.filter(s => !s.is_busy).length
})

const activeProjectsCount = computed(() => {
  if (!gameStore.currentGame?.projects) return 0
  return gameStore.currentGame.projects.filter(p => p.status === 'in_progress').length
})

const hasActiveProjects = computed(() => activeProjectsCount.value > 0)

const overallProgress = computed(() => {
  if (!gameStore.currentGame?.projects) return 0
  const activeProjects = gameStore.currentGame.projects.filter(p => p.status === 'in_progress')
  if (activeProjects.length === 0) return 0

  const totalProgress = activeProjects.reduce((sum, project) => {
    return sum + (project.progress_percentage || 0)
  }, 0)

  return totalProgress / activeProjects.length
})

// Methods
const isActiveRoute = (routeName) => {
  return route.name === routeName
}

const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value
}

const closeMobileMenu = () => {
  isMobileMenuOpen.value = false
}

const quickSave = async () => {
  try {
    await gameStore.saveGame()
    notificationStore.success('Gioco salvato!')
    closeMobileMenu()
  } catch (error) {
    notificationStore.error('Errore nel salvataggio')
  }
}

const quickPause = async () => {
  try {
    await gameStore.pauseGame()
    notificationStore.info('Gioco in pausa')
    closeMobileMenu()
  } catch (error) {
    notificationStore.error('Errore nella pausa')
  }
}

// Keyboard shortcuts handler
const handleKeydown = (event) => {
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
        quickSave()
        break
    }
  }

  // ESC per chiudere menu mobile
  if (event.key === 'Escape' && isMobileMenuOpen.value) {
    closeMobileMenu()
  }
}

// Lifecycle
onMounted(() => {
  document.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown)
})
</script>

<style scoped>
.navigation-bar {
  @apply relative;
}

/* Desktop Navigation */
.nav-desktop {
  @apply hidden lg:flex flex-col h-full;
  @apply bg-white border-r border-neutral-200;
  @apply overflow-y-auto;
}

.nav-list {
  @apply flex-1 px-3 py-4 space-y-2;
}

.nav-item {
  @apply relative;
}

.nav-link {
  @apply flex items-center w-full px-3 py-3 rounded-lg;
  @apply text-neutral-700 hover:bg-neutral-100 hover:text-neutral-900;
  @apply transition-all duration-200 ease-in-out;
  @apply focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2;
  text-decoration: none;
}

.nav-link--active {
  @apply bg-brand-50 text-brand-700 border border-brand-200;
  @apply shadow-sm;
}

.nav-link-content {
  @apply flex items-center w-full;
}

.nav-icon {
  @apply text-xl mr-3 flex-shrink-0;
}

.nav-label {
  @apply flex-1 font-medium;
}

.nav-badge {
  @apply ml-2 px-2 py-1 text-xs font-bold rounded-full;
}

.nav-badge--primary {
  @apply bg-brand-100 text-brand-800;
}

.nav-badge--warning {
  @apply bg-warning-100 text-warning-800;
}

.nav-badge--success {
  @apply bg-success-100 text-success-800;
}

.nav-description {
  @apply mt-1 text-xs text-neutral-500 leading-tight;
}

/* Shortcuts Section */
.nav-shortcuts {
  @apply px-3 py-4 border-t border-neutral-200 bg-neutral-50;
}

.shortcuts-title {
  @apply text-xs font-semibold text-neutral-700 mb-3;
}

.shortcuts-list {
  @apply space-y-1;
}

.shortcut-item {
  @apply flex items-center justify-between text-xs;
}

.shortcut-key {
  @apply px-2 py-1 bg-neutral-200 text-neutral-700 rounded text-xs font-mono;
}

.shortcut-label {
  @apply text-neutral-600;
}

/* Mobile Navigation */
.nav-mobile {
  @apply lg:hidden fixed bottom-0 left-0 right-0 z-40;
  @apply bg-white border-t border-neutral-200 shadow-lg;
}

.mobile-nav-container {
  @apply flex items-center justify-around px-2 py-1;
}

.mobile-nav-item {
  @apply flex-1 flex flex-col items-center py-2 px-1;
  @apply text-neutral-600 hover:text-brand-600;
  @apply transition-colors duration-200;
  text-decoration: none;
}

.mobile-nav-item--active {
  @apply text-brand-600;
}

.mobile-nav-content {
  @apply relative flex flex-col items-center;
}

.mobile-nav-icon {
  @apply text-xl mb-1;
}

.mobile-nav-label {
  @apply text-xs font-medium leading-none;
}

.mobile-nav-badge {
  @apply absolute -top-1 -right-1 w-4 h-4;
  @apply bg-danger-500 text-white text-xs;
  @apply rounded-full flex items-center justify-center;
}

/* Mobile Menu Button */
.mobile-menu-button {
  @apply flex flex-col items-center py-2 px-1;
  @apply text-neutral-600 hover:text-brand-600;
  @apply transition-colors duration-200;
  @apply focus:outline-none focus:text-brand-600;
}

.hamburger-icon {
  @apply w-6 h-6 flex flex-col justify-center items-center mb-1;
}

.hamburger-icon span {
  @apply w-4 h-0.5 bg-current transition-all duration-300;
  @apply mb-1 last:mb-0;
}

.hamburger-icon--open span:nth-child(1) {
  @apply transform rotate-45 translate-y-1.5;
}

.hamburger-icon--open span:nth-child(2) {
  @apply opacity-0;
}

.hamburger-icon--open span:nth-child(3) {
  @apply transform -rotate-45 -translate-y-1.5;
}

/* Mobile Overlay */
.mobile-overlay {
  @apply fixed inset-0 bg-black bg-opacity-50 z-50;
  @apply lg:hidden;
}

.mobile-overlay-content {
  @apply absolute right-0 top-0 bottom-0 w-80 max-w-full;
  @apply bg-white shadow-xl;
  @apply flex flex-col;
}

.mobile-overlay-header {
  @apply flex items-center justify-between px-4 py-3;
  @apply border-b border-neutral-200 bg-neutral-50;
}

.overlay-title {
  @apply text-lg font-semibold text-neutral-900;
}

.overlay-close {
  @apply w-8 h-8 flex items-center justify-center;
  @apply text-neutral-500 hover:text-neutral-700;
  @apply rounded-full hover:bg-neutral-200;
  @apply transition-colors duration-200;
}

.mobile-overlay-body {
  @apply flex-1 overflow-y-auto;
}

/* Mobile Stats */
.mobile-stats {
  @apply px-4 py-3 bg-neutral-50 border-b border-neutral-200;
  @apply grid grid-cols-2 gap-3;
}

.mobile-stat {
  @apply flex items-center space-x-2;
  @apply px-3 py-2 bg-white rounded-md;
}

.mobile-stat-icon {
  @apply text-lg;
}

.mobile-stat-label {
  @apply text-xs text-neutral-600;
}

.mobile-stat-value {
  @apply text-sm font-semibold;
}

/* Mobile Navigation List */
.mobile-nav-list {
  @apply px-4 py-2;
}

.mobile-nav-item-full {
  @apply border-b border-neutral-100 last:border-b-0;
}

.mobile-nav-link {
  @apply flex items-center py-3;
  @apply text-neutral-700 hover:text-brand-700;
  @apply transition-colors duration-200;
  text-decoration: none;
}

.mobile-nav-link--active {
  @apply text-brand-700 bg-brand-50;
  @apply -mx-4 px-4 rounded-md;
}

.mobile-nav-icon-full {
  @apply text-2xl mr-3;
}

.mobile-nav-text {
  @apply flex-1;
}

.mobile-nav-title {
  @apply block font-medium;
}

.mobile-nav-desc {
  @apply block text-xs text-neutral-500 mt-0.5;
}

.mobile-nav-badge-full {
  @apply ml-2 px-2 py-1 bg-brand-100 text-brand-800;
  @apply text-xs font-semibold rounded-full;
}

/* Mobile Actions */
.mobile-actions {
  @apply px-4 py-3 border-t border-neutral-200;
  @apply grid grid-cols-2 gap-3;
}

.mobile-action-btn {
  @apply flex items-center justify-center space-x-2;
  @apply px-4 py-3 bg-neutral-100 hover:bg-neutral-200;
  @apply rounded-md transition-colors duration-200;
  @apply text-neutral-700 font-medium;
}

.mobile-action-icon {
  @apply text-lg;
}

.mobile-action-label {
  @apply text-sm;
}

/* Global Progress */
.global-progress {
  @apply fixed bottom-16 left-4 right-4 lg:left-64 lg:right-4;
  @apply bg-white border border-neutral-200 rounded-lg shadow-lg;
  @apply px-4 py-2 z-30;
}

.progress-content {
  @apply flex items-center space-x-2 mb-2;
}

.progress-icon {
  @apply text-brand-600;
}

.progress-text {
  @apply text-sm font-medium text-neutral-700;
}

.progress-bar {
  @apply w-full h-2 bg-neutral-200 rounded-full overflow-hidden;
}

.progress-fill {
  @apply h-full bg-brand-500 transition-all duration-500 ease-out;
}

/* Transitions */
.mobile-overlay-enter-active,
.mobile-overlay-leave-active {
  transition: opacity 0.3s ease;
}

.mobile-overlay-enter-from,
.mobile-overlay-leave-to {
  opacity: 0;
}

.mobile-overlay-content {
  transition: transform 0.3s ease;
}

.mobile-overlay-enter-from .mobile-overlay-content,
.mobile-overlay-leave-to .mobile-overlay-content {
  transform: translateX(100%);
}

/* Accessibility */
.nav-link:focus,
.mobile-nav-link:focus {
  @apply outline-none ring-2 ring-brand-500 ring-offset-2;
}

@media (max-width: 374px) {
  .mobile-nav-label {
    @apply hidden;
  }

  .mobile-overlay-content {
    @apply w-full;
  }
}
</style>