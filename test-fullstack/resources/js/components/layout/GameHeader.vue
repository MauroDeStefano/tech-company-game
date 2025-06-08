<template>
  <nav class="relative">
    <!-- Desktop Sidebar - Aggiornato per header fisso -->
    <div class="hidden lg:flex lg:w-64 lg:flex-col lg:fixed lg:inset-y-0 lg:bg-white lg:border-r lg:border-gray-200 lg:z-40">
      <!-- Spacer per l'header fisso -->
      <div class="h-20 flex-shrink-0 bg-gray-50 border-b border-gray-200"></div>
      
      <!-- Navigation Links -->
      <div class="flex-1 overflow-y-auto pt-5 pb-4">
        <ul class="space-y-1 px-3">
          <li v-for="route in navigationRoutes" :key="route.name">
            <router-link
              :to="{ name: route.name }"
              class="group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200"
              :class="isActiveRoute(route.name) 
                ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-600' 
                : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900'"
              :aria-current="isActiveRoute(route.name) ? 'page' : undefined"
            >
              <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-3">
                  <span class="text-lg">{{ route.icon }}</span>
                  <div>
                    <span class="block">{{ route.title }}</span>
                    <span v-if="route.description" class="text-xs text-gray-500 block">
                      {{ route.description }}
                    </span>
                  </div>
                </div>

                <!-- Badge per notifiche -->
                <span
                  v-if="route.badge"
                  class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full"
                  :class="route.badgeVariant === 'warning' 
                    ? 'bg-yellow-100 text-yellow-800' 
                    : route.badgeVariant === 'success' 
                    ? 'bg-green-100 text-green-800' 
                    : 'bg-blue-100 text-blue-800'"
                >
                  {{ route.badge }}
                </span>
              </div>
            </router-link>
          </li>
        </ul>
      </div>

      <!-- Shortcuts Info -->
      <div class="border-t border-gray-200 p-4">
        <h4 class="text-sm font-medium text-gray-900 mb-3 flex items-center gap-2">
          ⌨️ Shortcuts
        </h4>
        <ul class="space-y-2">
          <li v-for="shortcut in keyboardShortcuts" :key="shortcut.key" class="flex items-center justify-between text-xs">
            <kbd class="px-2 py-1 bg-gray-100 border border-gray-300 rounded text-gray-700 font-mono">
              {{ shortcut.key }}
            </kbd>
            <span class="text-gray-600">{{ shortcut.label }}</span>
          </li>
        </ul>
      </div>
    </div>

    <!-- Mobile Bottom Navigation - Aggiornato z-index -->
    <div class="lg:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-40">
      <div class="flex items-center justify-around px-2 py-2">
        <router-link
          v-for="route in mobileRoutes"
          :key="route.name"
          :to="{ name: route.name }"
          class="flex flex-col items-center px-3 py-2 relative"
          :class="isActiveRoute(route.name) 
            ? 'text-blue-600' 
            : 'text-gray-600'"
        >
          <div class="relative">
            <span class="text-lg">{{ route.icon }}</span>
            <!-- Badge mobile -->
            <span
              v-if="route.badge"
              class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center"
            >
              {{ route.badge }}
            </span>
          </div>
          <span class="text-xs mt-1">{{ route.title }}</span>
        </router-link>

        <!-- Hamburger Menu Button -->
        <button
          class="flex flex-col items-center px-3 py-2 text-gray-600"
          @click="toggleMobileMenu"
          :aria-expanded="isMobileMenuOpen"
          aria-label="Menu"
        >
          <div class="relative w-6 h-6 flex flex-col justify-center items-center">
            <span class="block w-5 h-0.5 bg-current transition-all duration-300"
                  :class="isMobileMenuOpen ? 'rotate-45 translate-y-0.5' : ''"></span>
            <span class="block w-5 h-0.5 bg-current mt-1 transition-all duration-300"
                  :class="isMobileMenuOpen ? 'opacity-0' : ''"></span>
            <span class="block w-5 h-0.5 bg-current mt-1 transition-all duration-300"
                  :class="isMobileMenuOpen ? '-rotate-45 -translate-y-0.5' : ''"></span>
          </div>
          <span class="text-xs mt-1">Menu</span>
        </button>
      </div>
    </div>

    <!-- Mobile Overlay Menu - Aggiornato z-index e posizione -->
    <transition
      enter-active-class="transition-all duration-300"
      leave-active-class="transition-all duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="isMobileMenuOpen"
        class="lg:hidden fixed inset-0 bg-black bg-opacity-50 z-45"
        @click="closeMobileMenu"
      >
        <transition
          enter-active-class="transition-transform duration-300"
          leave-active-class="transition-transform duration-300"
          enter-from-class="translate-y-full"
          enter-to-class="translate-y-0"
          leave-from-class="translate-y-0"
          leave-to-class="translate-y-full"
        >
          <div
            v-if="isMobileMenuOpen"
            class="absolute bottom-0 left-0 right-0 bg-white rounded-t-2xl max-h-[80vh] overflow-y-auto"
            @click.stop
          >
            <div class="p-4 border-b border-gray-200">
              <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Menu Gioco</h3>
                <button
                  class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors duration-200"
                  @click="closeMobileMenu"
                  aria-label="Chiudi menu"
                >
                  ✕
                </button>
              </div>
            </div>

            <div class="p-4">
              <!-- Game Stats (Mobile) -->
              <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="bg-gray-50 rounded-lg p-3">
                  <div class="flex items-center gap-2">
                    <span class="text-lg">💰</span>
                    <div>
                      <span class="block text-xs text-gray-600">Patrimonio</span>
                      <span class="block text-sm font-semibold" :class="moneyColorClass">
                        {{ formatCurrency(gameStore.currentGameMoney) }}
                      </span>
                    </div>
                  </div>
                </div>

                <div class="bg-gray-50 rounded-lg p-3">
                  <div class="flex items-center gap-2">
                    <span class="text-lg">👥</span>
                    <div>
                      <span class="block text-xs text-gray-600">Team</span>
                      <span class="block text-sm font-semibold text-gray-900">{{ totalTeamSize }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Navigation Links (Mobile) -->
              <ul class="space-y-2 mb-6">
                <li v-for="route in allNavigationRoutes" :key="route.name">
                  <router-link
                    :to="{ name: route.name }"
                    class="flex items-center justify-between p-3 rounded-lg transition-colors duration-200"
                    :class="isActiveRoute(route.name) 
                      ? 'bg-blue-50 text-blue-700' 
                      : 'text-gray-700 hover:bg-gray-50'"
                    @click="closeMobileMenu"
                  >
                    <div class="flex items-center gap-3">
                      <span class="text-lg">{{ route.icon }}</span>
                      <div>
                        <span class="block font-medium">{{ route.title }}</span>
                        <span v-if="route.description" class="text-sm text-gray-500">{{ route.description }}</span>
                      </div>
                    </div>
                    <span v-if="route.badge" class="inline-flex items-center px-2 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded-full">
                      {{ route.badge }}
                    </span>
                  </router-link>
                </li>
              </ul>

              <!-- Quick Actions (Mobile) -->
              <div class="grid grid-cols-2 gap-3">
                <button 
                  class="flex items-center justify-center gap-2 p-3 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition-colors duration-200"
                  @click="quickSave"
                >
                  <span class="text-lg">💾</span>
                  <span class="font-medium">Salva</span>
                </button>

                <button 
                  class="flex items-center justify-center gap-2 p-3 bg-gray-50 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors duration-200"
                  @click="quickPause"
                >
                  <span class="text-lg">⏸️</span>
                  <span class="font-medium">Pausa</span>
                </button>
              </div>
            </div>
          </div>
        </transition>
      </div>
    </transition>

    <!-- Progress Indicator (Global) - Aggiornato per header fisso -->
    <div v-if="hasActiveProjects" class="fixed top-20 left-0 right-0 lg:left-64 bg-blue-50 border-b border-blue-200 z-30">
      <div class="px-4 py-2">
        <div class="flex items-center justify-between mb-2">
          <div class="flex items-center gap-2 text-sm text-blue-800">
            <span>⚡</span>
            <span>{{ activeProjectsCount }} progetti in corso</span>
          </div>
          <span class="text-xs text-blue-600">{{ Math.round(overallProgress) }}%</span>
        </div>
        <div class="w-full bg-blue-200 rounded-full h-1">
          <div
            class="bg-blue-600 h-1 rounded-full transition-all duration-300"
            :style="{ width: `${overallProgress}%` }"
          ></div>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { ref, computed, inject, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useGameStore } from '@/js/stores/game'
import { useNotificationStore } from '@/js/stores/notifications'
import { formatCurrency } from '@/js/utils/helpers'

// Stores
const gameStore = useGameStore()
const notificationStore = useNotificationStore()
const route = useRoute()
const router = useRouter()

// Injected navigation state
const navigation = inject('navigation', {
  isSidebarOpen: ref(false),
  toggleSidebar: () => {}
})

// Reactive state
const isMobileMenuOpen = ref(false)

// 🎯 CORREZIONE: Computed properties che usano i getters dello store
const totalTeamSize = computed(() => {
  return gameStore.currentGameTeamSize
})

const moneyColorClass = computed(() => {
  const money = gameStore.currentGameMoney
  if (money < 0) return 'text-red-600'
  if (money < 1000) return 'text-yellow-600'
  return 'text-green-600'
})

// 🎯 CORREZIONE: Usa i getters dello store invece di chiamare progetti come funzione
const pendingProjectsCount = computed(() => {
  // Usa direttamente i dati dal GameResource (più efficiente)
  return gameStore.currentGamePendingProjects
})

// 🎯 CORREZIONE: Usa i getters dello store per sales people disponibili
const availableSalesCount = computed(() => {
  // Usa il getter che simula Laravel Collection
  return gameStore.salesPeople.available().length
})

const activeProjectsCount = computed(() => {
  // Usa direttamente i dati dal GameResource (più efficiente)
  return gameStore.currentGameActiveProjects
})

const hasActiveProjects = computed(() => activeProjectsCount.value > 0)

// 🎯 CORREZIONE: Calcola progress usando i getters dello store
const overallProgress = computed(() => {
  // Se usi il getter projects che simula Laravel Collection
  const activeProjects = gameStore.projects.filter(p => p.status === 'in_progress')
  
  if (activeProjects.length === 0) return 0

  const totalProgress = activeProjects.reduce((sum, project) => {
    return sum + (project.progress_percentage || 0)
  }, 0)

  return totalProgress / activeProjects.length
})

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