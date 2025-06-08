<!-- src/components/game/dashboard/RecentActivityCard.vue -->
<template>
  <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
    <!-- Header -->
    <div class="mb-6">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
          <span class="text-2xl">📋</span>
          <h3 class="text-lg font-bold text-gray-900">Attività Recenti</h3>
          <div v-if="loading" class="ml-2">
            <svg class="animate-spin w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
          </div>
        </div>
        <button
          @click="viewAllActivity"
          class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 rounded-lg transition-colors duration-200"
        >
          <span>🗂️</span>
          Vedi Tutto
        </button>
      </div>
    </div>

    <!-- Content -->
    <div>
      <!-- Empty State -->
      <div v-if="activities.length === 0" class="text-center py-8">
        <div class="text-6xl mb-4">📝</div>
        <h4 class="text-lg font-semibold text-gray-900 mb-2">Nessuna Attività</h4>
        <p class="text-gray-600 max-w-sm mx-auto">
          Non ci sono attività recenti da mostrare. Inizia ad assegnare progetti o assumere personale!
        </p>
      </div>

      <!-- Activities List -->
      <div v-else class="space-y-4">
        <div
          v-for="activity in displayedActivities"
          :key="activity.id"
          class="flex items-start gap-3 p-3 rounded-lg border"
          :class="{
            'border-green-200 bg-green-50': activity.type === 'success',
            'border-blue-200 bg-blue-50': activity.type === 'info',
            'border-yellow-200 bg-yellow-50': activity.type === 'warning',
            'border-red-200 bg-red-50': activity.type === 'danger'
          }"
        >
          <!-- Activity Icon -->
          <div 
            class="flex items-center justify-center w-8 h-8 rounded-full text-sm"
            :class="{
              'bg-green-100': activity.type === 'success',
              'bg-blue-100': activity.type === 'info',
              'bg-yellow-100': activity.type === 'warning',
              'bg-red-100': activity.type === 'danger'
            }"
          >
            {{ activity.icon }}
          </div>

          <!-- Activity Content -->
          <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between mb-1">
              <span 
                class="font-medium text-sm"
                :class="{
                  'text-green-800': activity.type === 'success',
                  'text-blue-800': activity.type === 'info',
                  'text-yellow-800': activity.type === 'warning',
                  'text-red-800': activity.type === 'danger'
                }"
              >
                {{ activity.title }}
              </span>
              <span class="text-xs text-gray-500">{{ formatRelativeTime(activity.timestamp) }}</span>
            </div>

            <div class="text-sm text-gray-700 mb-2">{{ activity.description }}</div>

            <!-- Activity Details -->
            <div v-if="activity.details" class="flex flex-wrap gap-2">
              <span
                v-for="detail in activity.details"
                :key="detail.key"
                class="text-xs px-2 py-1 rounded-full"
                :class="{
                  'bg-green-100 text-green-700': detail.type === 'success',
                  'bg-blue-100 text-blue-700': detail.type === 'info',
                  'bg-yellow-100 text-yellow-700': detail.type === 'warning',
                  'bg-gray-100 text-gray-700': detail.type === 'neutral'
                }"
              >
                {{ detail.label }}: {{ detail.value }}
              </span>
            </div>
          </div>

          <!-- Activity Value (if any) -->
          <div v-if="activity.value" 
            class="text-sm font-semibold"
            :class="{
              'text-green-600': activity.valueType === 'money',
              'text-red-600': activity.valueType === 'cost'
            }"
          >
            {{ formatActivityValue(activity.value, activity.valueType) }}
          </div>
        </div>

        <!-- Show More Button -->
        <div v-if="activities.length > displayLimit" class="text-center pt-2">
          <button
            @click="showAll = !showAll"
            class="px-4 py-2 text-gray-600 hover:text-gray-800 hover:bg-gray-50 rounded-lg text-sm font-medium transition-colors duration-200"
          >
            {{ showAll ? 'Mostra meno' : `+${activities.length - displayLimit} altre attività` }}
          </button>
        </div>
      </div>
    </div>

    <!-- Activity Summary -->
    <div v-if="activities.length > 0" class="mt-6 pt-4 border-t border-gray-200">
      <div class="flex items-center justify-between text-sm">
        <div class="flex items-center gap-2 text-gray-600">
          <span>📊</span>
          <span>{{ activities.length }} attività nelle ultime 24h</span>
        </div>

        <div v-if="todayEarnings > 0" class="flex items-center gap-2 text-green-600 font-medium">
          <span>💰</span>
          <span>{{ formatCurrency(todayEarnings) }} guadagnati oggi</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useGameStore } from '@/js/stores/game'
import { formatCurrency, formatRelativeTime } from '@/js/utils/helpers'

// Stores
const gameStore = useGameStore()

// Reactive state
const loading = ref(false)
const showAll = ref(false)
const displayLimit = 5

// Computed properties
const activities = computed(() => {
  // Generate activities from game data
  const game = gameStore.currentGame
  if (!game) return []

  const activityList = []
  const now = new Date()
  const last24Hours = new Date(now.getTime() - 24 * 60 * 60 * 1000)

  // Add completed projects
  const completedProjects = game.projects?.filter(p =>
    p.status === 'completed' &&
    p.completed_at &&
    new Date(p.completed_at) > last24Hours
  ) || []

  completedProjects.forEach(project => {
    activityList.push({
      id: `project-${project.id}`,
      type: 'success',
      icon: '✅',
      title: 'Progetto Completato',
      description: `${project.name} è stato completato con successo`,
      timestamp: new Date(project.completed_at),
      value: project.value,
      valueType: 'money',
      details: [
        { key: 'developer', label: 'Sviluppatore', value: project.developer?.name || 'Sconosciuto', type: 'info' },
        { key: 'complexity', label: 'Complessità', value: getComplexityText(project.complexity), type: 'neutral' }
      ]
    })
  })

  // Add project assignments
  const assignedProjects = game.projects?.filter(p =>
    p.status === 'in_progress' &&
    p.assigned_at &&
    new Date(p.assigned_at) > last24Hours
  ) || []

  assignedProjects.forEach(project => {
    activityList.push({
      id: `assignment-${project.id}`,
      type: 'info',
      icon: '🎯',
      title: 'Progetto Assegnato',
      description: `${project.name} assegnato a ${project.developer?.name || 'sviluppatore'}`,
      timestamp: new Date(project.assigned_at),
      details: [
        { key: 'value', label: 'Valore', value: formatCurrency(project.value), type: 'success' },
        { key: 'duration', label: 'Durata stimata', value: calculateEstimatedDuration(project), type: 'neutral' }
      ]
    })
  })

  // Add new hires (developers and sales people)
  const newDevelopers = game.developers?.filter(d =>
    d.hire_date &&
    new Date(d.hire_date) > last24Hours
  ) || []

  newDevelopers.forEach(developer => {
    activityList.push({
      id: `hire-dev-${developer.id}`,
      type: 'info',
      icon: '👨‍💻',
      title: 'Nuovo Sviluppatore',
      description: `${developer.name} è entrato nel team`,
      timestamp: new Date(developer.hire_date),
      value: -(developer.hiring_cost || developer.monthly_salary || 2000),
      valueType: 'cost',
      details: [
        { key: 'seniority', label: 'Seniority', value: getSeniorityText(developer.seniority), type: 'info' },
        { key: 'salary', label: 'Stipendio', value: formatCurrency(developer.monthly_salary || 2000) + '/mese', type: 'warning' }
      ]
    })
  })

  const newSalesPeople = game.sales_people?.filter(s =>
    s.hire_date &&
    new Date(s.hire_date) > last24Hours
  ) || []

  newSalesPeople.forEach(salesPerson => {
    activityList.push({
      id: `hire-sales-${salesPerson.id}`,
      type: 'info',
      icon: '💼',
      title: 'Nuovo Commerciale',
      description: `${salesPerson.name} è entrato nel team`,
      timestamp: new Date(salesPerson.hire_date),
      value: -(salesPerson.hiring_cost || salesPerson.monthly_salary || 1500),
      valueType: 'cost',
      details: [
        { key: 'experience', label: 'Esperienza', value: getExperienceText(salesPerson.experience), type: 'info' },
        { key: 'salary', label: 'Stipendio', value: formatCurrency(salesPerson.monthly_salary || 1500) + '/mese', type: 'warning' }
      ]
    })
  })

  // Add generated projects
  const generatedProjects = game.project_generations?.filter(g =>
    g.status === 'completed' &&
    g.completed_at &&
    new Date(g.completed_at) > last24Hours
  ) || []

  generatedProjects.forEach(generation => {
    activityList.push({
      id: `generation-${generation.id}`,
      type: 'success',
      icon: '💡',
      title: 'Progetto Generato',
      description: `Nuovo progetto generato da ${generation.sales_person?.name || 'commerciale'}`,
      timestamp: new Date(generation.completed_at),
      details: [
        { key: 'value', label: 'Valore stimato', value: formatCurrency(generation.estimated_value || 0), type: 'success' }
      ]
    })
  })

  // Sort by timestamp (most recent first)
  return activityList.sort((a, b) => b.timestamp - a.timestamp)
})

const displayedActivities = computed(() => {
  if (showAll.value) return activities.value
  return activities.value.slice(0, displayLimit)
})

const todayEarnings = computed(() => {
  const today = new Date()
  today.setHours(0, 0, 0, 0)

  return activities.value
    .filter(activity =>
      activity.valueType === 'money' &&
      activity.timestamp >= today
    )
    .reduce((sum, activity) => sum + (activity.value || 0), 0)
})

// Methods
const getComplexityText = (complexity) => {
  const levels = {
    1: 'Facile',
    2: 'Medio',
    3: 'Difficile',
    4: 'Complesso',
    5: 'Esperto'
  }
  return levels[complexity] || 'Sconosciuto'
}

const getSeniorityText = (seniority) => {
  const levels = {
    1: 'Junior',
    2: 'Junior-Mid',
    3: 'Mid',
    4: 'Senior',
    5: 'Lead'
  }
  return levels[seniority] || 'Sconosciuto'
}

const getExperienceText = (experience) => {
  const levels = {
    1: 'Trainee',
    2: 'Junior',
    3: 'Mid',
    4: 'Senior',
    5: 'Manager'
  }
  return levels[experience] || 'Sconosciuto'
}

const calculateEstimatedDuration = (project) => {
  if (!project.estimated_completion_at || !project.started_at) return 'Sconosciuta'

  const start = new Date(project.started_at)
  const end = new Date(project.estimated_completion_at)
  const durationMs = end - start
  const hours = Math.round(durationMs / (1000 * 60 * 60))

  if (hours < 24) {
    return `${hours}h`
  } else {
    const days = Math.round(hours / 24)
    return `${days}g`
  }
}

const formatActivityValue = (value, type) => {
  switch (type) {
    case 'money':
      return `+${formatCurrency(value)}`
    case 'cost':
      return formatCurrency(value)
    default:
      return value
  }
}

const viewAllActivity = () => {
  // TODO: Navigate to full activity log page
  console.log('Navigate to activity log')
}

// Auto-refresh activities
let refreshInterval = null
onMounted(() => {
  // Refresh activities every minute
  refreshInterval = setInterval(() => {
    // Activities are computed from game state, which updates automatically
  }, 60000)
})

onUnmounted(() => {
  if (refreshInterval) {
    clearInterval(refreshInterval)
  }
})
</script>