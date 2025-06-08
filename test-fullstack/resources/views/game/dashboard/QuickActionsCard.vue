<!-- src/components/game/dashboard/QuickActionsCard.vue -->
<template>
  <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
    <!-- Header -->
    <div class="mb-6">
      <div class="flex items-center gap-2">
        <span class="text-2xl">⚡</span>
        <h3 class="text-lg font-bold text-gray-900">Azioni Rapide</h3>
      </div>
    </div>

    <!-- Actions Grid -->
    <div class="mb-6">
      <!-- Assegna Progetto -->
      <button
        @click="assignProject"
        :disabled="!canAssignProject"
        class="w-full flex items-center justify-between p-4 border rounded-xl transition-all duration-200"
        :class="{
          'border-blue-200 hover:border-blue-300 hover:bg-blue-50': canAssignProject,
          'border-gray-200 bg-gray-50 cursor-not-allowed opacity-50': !canAssignProject
        }"
      >
        <div class="flex items-center gap-3">
          <div class="text-2xl">🚀</div>
          <div class="text-left">
            <div class="font-semibold text-gray-900">Assegna Progetto</div>
            <div class="text-sm text-gray-600">
              {{ assignProjectDescription }}
            </div>
          </div>
        </div>
        <div v-if="pendingProjectsCount > 0" class="bg-blue-100 text-blue-800 text-sm font-semibold px-2 py-1 rounded-full">
          {{ pendingProjectsCount }}
        </div>
      </button>
    </div>

    <!-- Quick Stats -->
    <div class="mb-6">
      <div class="grid grid-cols-2 gap-4">
        <div class="bg-gray-50 rounded-lg p-3">
          <div class="flex items-center gap-2">
            <span class="text-lg">⏱️</span>
            <div>
              <div class="text-xs text-gray-600">Progetti Attivi</div>
              <div class="text-lg font-bold text-gray-900">{{ activeProjectsCount }}</div>
            </div>
          </div>
        </div>

        <div class="bg-gray-50 rounded-lg p-3">
          <div class="flex items-center gap-2">
            <span class="text-lg">👨‍💼</span>
            <div>
              <div class="text-xs text-gray-600">Team Disponibile</div>
              <div class="text-lg font-bold text-gray-900">{{ availableTeamCount }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Smart Suggestions -->
    <div v-if="suggestions.length > 0" class="mb-6">
      <h4 class="text-lg font-semibold text-gray-900 mb-3">💡 Suggerimenti</h4>
      <div class="space-y-2">
        <button
          v-for="suggestion in suggestions"
          :key="suggestion.id"
          @click="handleSuggestion(suggestion)"
          class="w-full flex items-center justify-between p-3 border rounded-lg hover:bg-gray-50 transition-all duration-200"
          :class="{
            'border-red-200 bg-red-50': suggestion.priority === 'high',
            'border-yellow-200 bg-yellow-50': suggestion.priority === 'medium',
            'border-gray-200': suggestion.priority === 'low'
          }"
        >
          <div class="flex items-center gap-3">
            <span class="text-lg">{{ suggestion.icon }}</span>
            <div class="text-left">
              <div 
                class="text-sm font-medium"
                :class="{
                  'text-red-800': suggestion.priority === 'high',
                  'text-yellow-800': suggestion.priority === 'medium',
                  'text-gray-800': suggestion.priority === 'low'
                }"
              >
                {{ suggestion.text }}
              </div>
              <div v-if="suggestion.actionText" 
                class="text-xs"
                :class="{
                  'text-red-600': suggestion.priority === 'high',
                  'text-yellow-600': suggestion.priority === 'medium',
                  'text-gray-600': suggestion.priority === 'low'
                }"
              >
                {{ suggestion.actionText }}
              </div>
            </div>
          </div>
          <span class="text-gray-400">→</span>
        </button>
      </div>
    </div>

    <!-- Hotkeys Info -->
    <div>
      <div class="text-sm font-semibold text-gray-900 mb-3">⌨️ Scorciatoie</div>
      <div class="grid grid-cols-2 gap-2">
        <div class="flex items-center justify-between">
          <kbd class="px-2 py-1 bg-gray-100 border border-gray-300 rounded text-xs font-mono">Ctrl+2</kbd>
          <span class="text-xs text-gray-600">Produzione</span>
        </div>
        <div class="flex items-center justify-between">
          <kbd class="px-2 py-1 bg-gray-100 border border-gray-300 rounded text-xs font-mono">Ctrl+3</kbd>
          <span class="text-xs text-gray-600">Sales</span>
        </div>
        <div class="flex items-center justify-between">
          <kbd class="px-2 py-1 bg-gray-100 border border-gray-300 rounded text-xs font-mono">Ctrl+4</kbd>
          <span class="text-xs text-gray-600">HR</span>
        </div>
        <div class="flex items-center justify-between">
          <kbd class="px-2 py-1 bg-gray-100 border border-gray-300 rounded text-xs font-mono">Ctrl+S</kbd>
          <span class="text-xs text-gray-600">Salva</span>
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

// Stores
const gameStore = useGameStore()
const notificationStore = useNotificationStore()
const router = useRouter()

// Reactive state
const isSaving = ref(false)

// Computed properties
const pendingProjectsCount = computed(() => {
  return gameStore.currentGame?.projects?.filter(p => p.status === 'pending').length || 0
})

const availableDevelopersCount = computed(() => {
  return gameStore.currentGame?.developers?.filter(d => !d.is_busy).length || 0
})

const availableSalesCount = computed(() => {
  return gameStore.currentGame?.sales_people?.filter(s => !s.is_busy).length || 0
})

const activeProjectsCount = computed(() => {
  return gameStore.currentGame?.projects?.filter(p => p.status === 'in_progress').length || 0
})

const availableTeamCount = computed(() => {
  return availableDevelopersCount.value + availableSalesCount.value
})

const canAssignProject = computed(() => {
  return pendingProjectsCount.value > 0 && availableDevelopersCount.value > 0
})

const canGenerateProject = computed(() => {
  return availableSalesCount.value > 0
})

const assignProjectDescription = computed(() => {
  if (pendingProjectsCount.value === 0) return 'Nessun progetto in attesa'
  if (availableDevelopersCount.value === 0) return 'Nessun sviluppatore disponibile'
  return `${pendingProjectsCount.value} progetti in attesa`
})

const generateProjectDescription = computed(() => {
  if (availableSalesCount.value === 0) return 'Nessun commerciale disponibile'
  return `${availableSalesCount.value} commerciali disponibili`
})

const saveDescription = computed(() => {
  if (isSaving.value) return 'Salvataggio in corso...'
  return 'Backup dei progressi'
})

// Smart Suggestions
const suggestions = computed(() => {
  const suggestions = []

  // Suggerisci di assegnare progetti se ci sono progetti pending e sviluppatori liberi
  if (canAssignProject.value) {
    suggestions.push({
      id: 'assign-projects',
      icon: '🎯',
      text: `Hai ${pendingProjectsCount.value} progetti da assegnare`,
      actionText: 'Assegna ora',
      priority: 'high',
      action: () => router.push({ name: 'Production' })
    })
  }

  // Suggerisci di generare progetti se ci sono commerciali liberi ma pochi progetti
  if (availableSalesCount.value > 0 && pendingProjectsCount.value < 2) {
    suggestions.push({
      id: 'generate-projects',
      icon: '💼',
      text: 'Genera nuovi progetti per mantenere il flusso di lavoro',
      actionText: 'Vai ai Sales',
      priority: 'medium',
      action: () => router.push({ name: 'Sales' })
    })
  }

  // Suggerisci di assumere se il team è troppo piccolo
  const totalTeam = (gameStore.currentGame?.developers?.length || 0) + (gameStore.currentGame?.sales_people?.length || 0)
  if (totalTeam < 4 && gameStore.currentGame?.money > 5000) {
    suggestions.push({
      id: 'hire-team',
      icon: '👥',
      text: 'Il tuo team è piccolo, considera di assumere nuovo personale',
      actionText: 'Assumi ora',
      priority: 'medium',
      action: () => router.push({ name: 'HR' })
    })
  }

  // Suggerisci di salvare se ci sono molte attività
  if (activeProjectsCount.value > 2) {
    suggestions.push({
      id: 'save-progress',
      icon: '💾',
      text: 'Molti progetti attivi, salva i progressi',
      actionText: 'Salva ora',
      priority: 'low',
      action: saveGame
    })
  }

  return suggestions.slice(0, 3) // Max 3 suggestions
})

// Methods
const assignProject = () => {
  if (!canAssignProject.value) return
  router.push({ name: 'Production' })
}

const generateProject = () => {
  if (!canGenerateProject.value) return
  router.push({ name: 'Sales' })
}

const hirePersonnel = () => {
  router.push({ name: 'HR' })
}

const saveGame = async () => {
  if (isSaving.value) return

  isSaving.value = true
  try {
    await gameStore.saveGame()
    notificationStore.success('Gioco salvato con successo!')
  } catch (error) {
    notificationStore.error('Errore nel salvataggio del gioco')
  } finally {
    isSaving.value = false
  }
}

const handleSuggestion = (suggestion) => {
  if (suggestion.action) {
    suggestion.action()
  }
}
</script>