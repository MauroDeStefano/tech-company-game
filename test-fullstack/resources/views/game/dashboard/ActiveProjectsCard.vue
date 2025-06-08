<!-- src/components/game/dashboard/ActiveProjectsCard.vue -->
<template>
  <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
    <!-- Header -->
    <div class="mb-4">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
          <span class="text-2xl">🚀</span>
          <h3 class="text-lg font-bold text-gray-900">Progetti in Corso</h3>
          <div v-if="loading" class="ml-2">
            <svg class="animate-spin w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
          </div>
        </div>
        <button
          @click="goToProduction"
          class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 rounded-lg transition-colors duration-200"
        >
          <span>📊</span>
          Vedi Tutti
        </button>
      </div>
    </div>

    <!-- Content -->
    <div class="space-y-4">
      <!-- Empty State -->
      <div v-if="activeProjects.length === 0" class="text-center py-8">
        <div class="text-6xl mb-4">💤</div>
        <h4 class="text-lg font-semibold text-gray-900 mb-2">Nessun Progetto Attivo</h4>
        <p class="text-gray-600 mb-6 max-w-sm mx-auto">
          Non ci sono progetti in corso al momento. Assegna sviluppatori ai progetti in attesa per iniziare a produrre.
        </p>
        <button
          @click="goToProduction"
          class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors duration-200"
        >
          <span>🎯</span>
          Assegna Progetti
        </button>
      </div>

      <!-- Active Projects List -->
      <div v-else class="space-y-4">
        <div
          v-for="project in displayedProjects"
          :key="project.id"
          class="border border-gray-200 rounded-xl p-4 hover:shadow-md transition-shadow duration-200"
          :class="{ 'border-green-300 bg-green-50': project.isNearlyComplete }"
        >
          <!-- Project Header -->
          <div class="flex items-start justify-between mb-3">
            <div class="flex-1">
              <h4 class="font-semibold text-gray-900 mb-1">{{ project.name }}</h4>
              <div class="flex items-center gap-3 text-sm text-gray-600">
                <span class="flex items-center gap-1">
                  <span>👨‍💻</span>
                  {{ project.developer?.name || 'Sconosciuto' }}
                </span>
                <span class="text-xs px-2 py-1 rounded-full bg-gray-100">
                  {{ getComplexityBadge(project.complexity) }}
                </span>
              </div>
            </div>

            <div class="text-right">
              <div class="text-lg font-bold text-gray-900">
                {{ formatCurrency(project.value) }}
              </div>
            </div>
          </div>

          <!-- Progress Bar -->
          <div class="mb-3">
            <div class="flex items-center justify-between mb-1">
              <span class="text-sm font-medium text-gray-700">Progresso</span>
              <span class="text-sm font-semibold text-gray-900">{{ Math.round(project.progress || 0) }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div
                class="h-2 rounded-full transition-all duration-300"
                :class="{
                  'bg-green-500': project.progress >= 95,
                  'bg-blue-500': project.progress >= 75 && project.progress < 95,
                  'bg-yellow-500': project.progress >= 50 && project.progress < 75,
                  'bg-orange-500': project.progress >= 25 && project.progress < 50,
                  'bg-red-500': project.progress < 25
                }"
                :style="{ width: `${project.progress || 0}%` }"
              ></div>
            </div>
          </div>

          <!-- Time Info -->
          <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2 text-sm text-gray-600">
              <span>⏰</span>
              <span>{{ getTimeRemaining(project) }}</span>
            </div>
            <div v-if="project.isNearlyComplete" class="flex items-center gap-1 text-sm text-green-600 font-medium">
              <span>🎉</span>
              <span>Quasi completato!</span>
            </div>
          </div>

          <!-- Quick Actions -->
          <div class="flex items-center gap-2">
            <button
              v-if="project.isNearlyComplete"
              @click="completeProject(project)"
              :disabled="completing[project.id]"
              class="flex items-center gap-2 px-3 py-2 bg-green-600 hover:bg-green-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-medium rounded-lg text-sm transition-colors duration-200"
            >
              <span v-if="completing[project.id]">
                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
              </span>
              <span v-else>✅</span>
              Completa
            </button>

            <button
              @click="viewProjectDetails(project)"
              class="flex items-center gap-2 px-3 py-2 text-gray-600 hover:text-gray-800 hover:bg-gray-50 rounded-lg text-sm font-medium transition-colors duration-200"
            >
              <span>👁️</span>
              Dettagli
            </button>
          </div>
        </div>

        <!-- Show More Button -->
        <div v-if="activeProjects.length > displayLimit" class="text-center">
          <button
            @click="showAll = !showAll"
            class="px-4 py-2 text-gray-600 hover:text-gray-800 hover:bg-gray-50 rounded-lg text-sm font-medium transition-colors duration-200"
          >
            {{ showAll ? 'Mostra meno' : `+${activeProjects.length - displayLimit} altri progetti` }}
          </button>
        </div>
      </div>
    </div>

    <!-- Summary Stats -->
    <div v-if="activeProjects.length > 0" class="mt-6 pt-4 border-t border-gray-200">
      <div class="grid grid-cols-3 gap-4">
        <div class="text-center">
          <div class="text-2xl font-bold text-gray-900">{{ activeProjects.length }}</div>
          <div class="text-sm text-gray-600">Progetti Attivi</div>
        </div>
        <div class="text-center">
          <div class="text-2xl font-bold text-gray-900">{{ formatCurrency(totalValue) }}</div>
          <div class="text-sm text-gray-600">Valore Totale</div>
        </div>
        <div class="text-center">
          <div class="text-2xl font-bold text-gray-900">{{ Math.round(averageProgress) }}%</div>
          <div class="text-sm text-gray-600">Progresso Medio</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useGameStore } from '@/js/stores/game'
import { useNotificationStore } from '@/js/stores/notifications'
import { formatCurrency } from '@/js/utils/helpers'

// Stores
const gameStore = useGameStore()
const notificationStore = useNotificationStore()
const router = useRouter()

// Reactive state
const loading = ref(false)
const completing = ref({})
const showAll = ref(false)
const displayLimit = 3

// Computed properties
const activeProjects = computed(() => {
  const projects = gameStore.currentGame?.projects?.filter(p => p.status === 'in_progress') || []

  // Add calculated properties to each project
  return projects.map(project => ({
    ...project,
    progress: calculateProgress(project),
    isNearlyComplete: isProjectNearlyComplete(project)
  })).sort((a, b) => {
    // Sort by completion percentage (nearly complete first)
    if (a.isNearlyComplete && !b.isNearlyComplete) return -1
    if (!a.isNearlyComplete && b.isNearlyComplete) return 1
    return (b.progress || 0) - (a.progress || 0)
  })
})

const displayedProjects = computed(() => {
  if (showAll.value) return activeProjects.value
  return activeProjects.value.slice(0, displayLimit)
})

const totalValue = computed(() => {
  return activeProjects.value.reduce((sum, project) => sum + (project.value || 0), 0)
})

const averageProgress = computed(() => {
  if (activeProjects.value.length === 0) return 0
  const totalProgress = activeProjects.value.reduce((sum, project) => sum + (project.progress || 0), 0)
  return totalProgress / activeProjects.value.length
})

// Methods
const calculateProgress = (project) => {
  if (!project.started_at || !project.estimated_completion_at) return 0

  const start = new Date(project.started_at)
  const end = new Date(project.estimated_completion_at)
  const now = new Date()

  if (now >= end) return 100
  if (now <= start) return 0

  const totalDuration = end - start
  const elapsed = now - start

  return Math.min(100, Math.max(0, (elapsed / totalDuration) * 100))
}

const isProjectNearlyComplete = (project) => {
  const progress = project.progress || 0
  return progress >= 95
}

const getComplexityBadge = (complexity) => {
  const badges = {
    1: '🟢 Facile',
    2: '🟡 Medio',
    3: '🟠 Difficile',
    4: '🔴 Complesso',
    5: '⚫ Esperto'
  }
  return badges[complexity] || '❓ Sconosciuto'
}

const getTimeRemaining = (project) => {
  if (!project.estimated_completion_at) return 'Tempo sconosciuto'

  const end = new Date(project.estimated_completion_at)
  const now = new Date()

  if (now >= end) return 'Completato'

  const remaining = end - now
  const hours = Math.floor(remaining / (1000 * 60 * 60))
  const minutes = Math.floor((remaining % (1000 * 60 * 60)) / (1000 * 60))

  if (hours > 24) {
    const days = Math.floor(hours / 24)
    return `${days}g ${hours % 24}h`
  } else if (hours > 0) {
    return `${hours}h ${minutes}m`
  } else {
    return `${minutes}m`
  }
}

const completeProject = async (project) => {
  if (completing.value[project.id]) return

  completing.value[project.id] = true

  try {
    await gameStore.completeProject(project.id)
    notificationStore.success(`Progetto "${project.name}" completato! +${formatCurrency(project.value)}`)
  } catch (error) {
    notificationStore.error('Errore nel completamento del progetto')
    console.error('Error completing project:', error)
  } finally {
    completing.value[project.id] = false
  }
}

const viewProjectDetails = (project) => {
  // TODO: Implement project details modal or page
  notificationStore.info(`Dettagli progetto: ${project.name}`)
}

const goToProduction = () => {
  router.push('/game/production')
}
</script>