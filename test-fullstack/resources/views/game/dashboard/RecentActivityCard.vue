<!-- src/components/game/dashboard/RecentActivityCard.vue -->
<template>
  <BaseCard
    title="Attività Recenti"
    icon="📋"
    :loading="loading"
    class="recent-activity-card"
  >
    <template #actions>
      <BaseButton
        variant="ghost"
        size="sm"
        icon="🗂️"
        @click="viewAllActivity"
      >
        Vedi Tutto
      </BaseButton>
    </template>

    <!-- Empty State -->
    <div v-if="activities.length === 0" class="empty-state">
      <div class="empty-icon">📝</div>
      <h4 class="empty-title">Nessuna Attività</h4>
      <p class="empty-description">
        Non ci sono attività recenti da mostrare. Inizia ad assegnare progetti o assumere personale!
      </p>
    </div>

    <!-- Activities List -->
    <div v-else class="activities-list">
      <div
        v-for="activity in displayedActivities"
        :key="activity.id"
        class="activity-item"
        :class="`activity-item--${activity.type}`"
      >
        <!-- Activity Icon -->
        <div class="activity-icon" :class="`activity-icon--${activity.type}`">
          {{ activity.icon }}
        </div>

        <!-- Activity Content -->
        <div class="activity-content">
          <div class="activity-header">
            <span class="activity-title">{{ activity.title }}</span>
            <span class="activity-time">{{ formatRelativeTime(activity.timestamp) }}</span>
          </div>

          <div class="activity-description">{{ activity.description }}</div>

          <!-- Activity Details -->
          <div v-if="activity.details" class="activity-details">
            <span
              v-for="detail in activity.details"
              :key="detail.key"
              class="activity-detail"
              :class="`detail--${detail.type}`"
            >
              {{ detail.label }}: {{ detail.value }}
            </span>
          </div>
        </div>

        <!-- Activity Value (if any) -->
        <div v-if="activity.value" class="activity-value" :class="`value--${activity.valueType}`">
          {{ formatActivityValue(activity.value, activity.valueType) }}
        </div>
      </div>

      <!-- Show More Button -->
      <div v-if="activities.length > displayLimit" class="show-more">
        <BaseButton
          variant="ghost"
          size="sm"
          @click="showAll = !showAll"
        >
          {{ showAll ? 'Mostra meno' : `+${activities.length - displayLimit} altre attività` }}
        </BaseButton>
      </div>
    </div>

    <!-- Activity Summary -->
    <div v-if="activities.length > 0" class="activity-summary">
      <div class="summary-item">
        <span class="summary-icon">📊</span>
        <span class="summary-text">{{ activities.length }} attività nelle ultime 24h</span>
      </div>

      <div v-if="todayEarnings > 0" class="summary-item">
        <span class="summary-icon">💰</span>
        <span class="summary-text">{{ formatCurrency(todayEarnings) }} guadagnati oggi</span>
      </div>
    </div>
  </BaseCard>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useGameStore } from '@/stores/game'
import { formatCurrency, formatRelativeTime } from '@/utils/helpers'

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

<style scoped>
.recent-activity-card {
  @apply h-full;
}

/* Empty State */
.empty-state {
  @apply text-center py-8;
}

.empty-icon {
  @apply text-4xl mb-4;
}

.empty-title {
  @apply text-lg font-semibold text-neutral-900 mb-2;
}

.empty-description {
  @apply text-neutral-600 max-w-sm mx-auto;
}

/* Activities List */
.activities-list {
  @apply space-y-3;
}

.activity-item {
  @apply flex items-start space-x-3 p-3 rounded-lg;
  @apply transition-all duration-200 hover:shadow-sm;
}

.activity-item--success {
  @apply bg-success-50 hover:bg-success-100;
}

.activity-item--info {
  @apply bg-blue-50 hover:bg-blue-100;
}

.activity-item--warning {
  @apply bg-warning-50 hover:bg-warning-100;
}

.activity-item--danger {
  @apply bg-danger-50 hover:bg-danger-100;
}

/* Activity Icon */
.activity-icon {
  @apply w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0;
  @apply text-base font-medium;
}

.activity-icon--success {
  @apply bg-success-100 text-success-700;
}

.activity-icon--info {
  @apply bg-blue-100 text-blue-700;
}

.activity-icon--warning {
  @apply bg-warning-100 text-warning-700;
}

.activity-icon--danger {
  @apply bg-danger-100 text-danger-700;
}

/* Activity Content */
.activity-content {
  @apply flex-1 min-w-0;
}

.activity-header {
  @apply flex items-center justify-between mb-1;
}

.activity-title {
  @apply font-medium text-neutral-900;
}

.activity-time {
  @apply text-xs text-neutral-500 flex-shrink-0 ml-2;
}

.activity-description {
  @apply text-sm text-neutral-700 mb-2;
}

.activity-details {
  @apply flex flex-wrap gap-2;
}

.activity-detail {
  @apply text-xs px-2 py-1 rounded-full;
}

.detail--info {
  @apply bg-blue-100 text-blue-700;
}

.detail--success {
  @apply bg-success-100 text-success-700;
}

.detail--warning {
  @apply bg-warning-100 text-warning-700;
}

.detail--neutral {
  @apply bg-neutral-100 text-neutral-700;
}

/* Activity Value */
.activity-value {
  @apply flex-shrink-0 text-sm font-semibold;
}

.value--money {
  @apply text-success-600;
}

.value--cost {
  @apply text-danger-600;
}

/* Show More */
.show-more {
  @apply text-center pt-2;
}

/* Activity Summary */
.activity-summary {
  @apply border-t border-neutral-200 pt-4 mt-4 space-y-2;
}

.summary-item {
  @apply flex items-center text-sm text-neutral-600;
}

.summary-icon {
  @apply mr-2;
}

/* Responsive */
@media (max-width: 640px) {
  .activity-item {
    @apply flex-col space-y-2 space-x-0 p-2;
  }

  .activity-icon {
    @apply self-start;
  }

  .activity-header {
    @apply flex-col items-start space-y-1;
  }

  .activity-time {
    @apply ml-0;
  }

  .activity-details {
    @apply flex-col gap-1;
  }

  .activity-value {
    @apply self-start;
  }
}

/* Animations */
.activity-item {
  animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateX(-10px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

/* Hover effects */
.activity-item:hover .activity-icon {
  @apply transform scale-105;
}
</style>