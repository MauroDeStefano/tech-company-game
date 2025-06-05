<!-- src/components/game/dashboard/ActiveProjectsCard.vue -->
<template>
  <BaseCard
    title="Progetti in Corso"
    icon="🚀"
    :loading="loading"
    class="active-projects-card"
  >
    <template #actions>
      <BaseButton
        variant="ghost"
        size="sm"
        icon="📊"
        @click="goToProduction"
      >
        Vedi Tutti
      </BaseButton>
    </template>

    <!-- Empty State -->
    <div v-if="activeProjects.length === 0" class="empty-state">
      <div class="empty-icon">💤</div>
      <h4 class="empty-title">Nessun Progetto Attivo</h4>
      <p class="empty-description">
        Non ci sono progetti in corso al momento. Assegna sviluppatori ai progetti in attesa per iniziare a produrre.
      </p>
      <BaseButton
        variant="primary"
        size="sm"
        icon="🎯"
        @click="goToProduction"
        class="empty-action"
      >
        Assegna Progetti
      </BaseButton>
    </div>

    <!-- Active Projects List -->
    <div v-else class="projects-list">
      <div
        v-for="project in displayedProjects"
        :key="project.id"
        class="project-item"
        :class="{ 'project-item--nearly-complete': project.isNearlyComplete }"
      >
        <!-- Project Header -->
        <div class="project-header">
          <div class="project-info">
            <h4 class="project-name">{{ project.name }}</h4>
            <div class="project-meta">
              <span class="project-developer">
                👨‍💻 {{ project.developer?.name || 'Sconosciuto' }}
              </span>
              <span class="project-complexity">
                {{ getComplexityBadge(project.complexity) }}
              </span>
            </div>
          </div>

          <div class="project-value">
            {{ formatCurrency(project.value) }}
          </div>
        </div>

        <!-- Progress Bar -->
        <div class="progress-section">
          <div class="progress-info">
            <span class="progress-label">Progresso</span>
            <span class="progress-percentage">{{ Math.round(project.progress || 0) }}%</span>
          </div>
          <div class="progress-bar">
            <div
              class="progress-fill"
              :class="getProgressColorClass(project.progress)"
              :style="{ width: `${project.progress || 0}%` }"
            ></div>
          </div>
        </div>

        <!-- Time Info -->
        <div class="time-info">
          <div class="time-item">
            <span class="time-icon">⏰</span>
            <span class="time-text">{{ getTimeRemaining(project) }}</span>
          </div>
          <div v-if="project.isNearlyComplete" class="completion-notice">
            <span class="notice-icon">🎉</span>
            <span class="notice-text">Quasi completato!</span>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="project-actions">
          <BaseButton
            v-if="project.isNearlyComplete"
            variant="success"
            size="sm"
            icon="✅"
            @click="completeProject(project)"
            :loading="completing[project.id]"
          >
            Completa
          </BaseButton>

          <BaseButton
            variant="ghost"
            size="sm"
            icon="👁️"
            @click="viewProjectDetails(project)"
          >
            Dettagli
          </BaseButton>
        </div>
      </div>

      <!-- Show More Button -->
      <div v-if="activeProjects.length > displayLimit" class="show-more">
        <BaseButton
          variant="ghost"
          size="sm"
          @click="showAll = !showAll"
        >
          {{ showAll ? 'Mostra meno' : `+${activeProjects.length - displayLimit} altri progetti` }}
        </BaseButton>
      </div>
    </div>

    <!-- Summary Stats -->
    <div v-if="activeProjects.length > 0" class="projects-summary">
      <div class="summary-stat">
        <span class="stat-value">{{ activeProjects.length }}</span>
        <span class="stat-label">Progetti Attivi</span>
      </div>
      <div class="summary-stat">
        <span class="stat-value">{{ formatCurrency(totalValue) }}</span>
        <span class="stat-label">Valore Totale</span>
      </div>
      <div class="summary-stat">
        <span class="stat-value">{{ Math.round(averageProgress) }}%</span>
        <span class="stat-label">Progresso Medio</span>
      </div>
    </div>
  </BaseCard>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useGameStore } from '@/stores/game'
import { useNotificationStore } from '@/stores/notifications'
import { formatCurrency } from '@/utils/helpers'

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

const getProgressColorClass = (progress) => {
  if (progress >= 95) return 'progress-fill--complete'
  if (progress >= 75) return 'progress-fill--high'
  if (progress >= 50) return 'progress-fill--medium'
  if (progress >= 25) return 'progress-fill--low'
  return 'progress-fill--minimal'
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
  router.push({ name: 'Production' })
}
</script>

<style scoped>
.active-projects-card {
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
  @apply text-neutral-600 mb-4 max-w-sm mx-auto;
}

.empty-action {
  @apply mx-auto;
}

/* Projects List */
.projects-list {
  @apply space-y-4;
}

.project-item {
  @apply bg-neutral-50 rounded-lg p-4 border border-neutral-200;
  @apply transition-all duration-200 hover:shadow-md;
}

.project-item--nearly-complete {
  @apply bg-success-50 border-success-200;
}

/* Project Header */
.project-header {
  @apply flex items-start justify-between mb-3;
}

.project-info {
  @apply flex-1 min-w-0;
}

.project-name {
  @apply font-semibold text-neutral-900 mb-1 truncate;
}

.project-meta {
  @apply flex items-center space-x-3 text-sm text-neutral-600;
}

.project-developer {
  @apply truncate;
}

.project-complexity {
  @apply flex-shrink-0;
}

.project-value {
  @apply text-lg font-bold text-success-600 flex-shrink-0;
}

/* Progress Section */
.progress-section {
  @apply mb-3;
}

.progress-info {
  @apply flex items-center justify-between mb-2 text-sm;
}

.progress-label {
  @apply text-neutral-600;
}

.progress-percentage {
  @apply font-semibold text-neutral-900;
}

.progress-bar {
  @apply w-full h-2 bg-neutral-200 rounded-full overflow-hidden;
}

.progress-fill {
  @apply h-full transition-all duration-500 ease-out;
}

.progress-fill--minimal {
  @apply bg-neutral-400;
}

.progress-fill--low {
  @apply bg-warning-500;
}

.progress-fill--medium {
  @apply bg-blue-500;
}

.progress-fill--high {
  @apply bg-brand-500;
}

.progress-fill--complete {
  @apply bg-success-500;
}

/* Time Info */
.time-info {
  @apply flex items-center justify-between mb-3;
}

.time-item {
  @apply flex items-center space-x-1 text-sm text-neutral-600;
}

.time-icon {
  @apply text-base;
}

.completion-notice {
  @apply flex items-center space-x-1 text-sm text-success-700 bg-success-100 px-2 py-1 rounded-full;
}

.notice-icon {
  @apply text-base;
}

/* Project Actions */
.project-actions {
  @apply flex items-center justify-end space-x-2;
}

/* Show More */
.show-more {
  @apply text-center pt-2;
}

/* Summary Stats */
.projects-summary {
  @apply border-t border-neutral-200 pt-4 mt-4;
  @apply grid grid-cols-3 gap-4;
}

.summary-stat {
  @apply text-center;
}

.stat-value {
  @apply block text-lg font-bold text-neutral-900;
}

.stat-label {
  @apply text-xs text-neutral-600;
}

/* Responsive */
@media (max-width: 640px) {
  .project-header {
    @apply flex-col space-y-2;
  }

  .project-value {
    @apply text-base self-start;
  }

  .project-meta {
    @apply flex-col items-start space-y-1 space-x-0;
  }

  .time-info {
    @apply flex-col items-start space-y-2;
  }

  .completion-notice {
    @apply self-start;
  }

  .project-actions {
    @apply justify-start;
  }

  .projects-summary {
    @apply grid-cols-1 gap-2;
  }
}

/* Animations */
.project-item {
  animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.progress-fill {
  animation: progressGrow 1s ease-out;
}

@keyframes progressGrow {
  from {
    width: 0%;
  }
}

/* Hover effects */
.project-item:hover {
  @apply transform -translate-y-1;
}

.project-item--nearly-complete:hover {
  @apply shadow-success-200;
}
</style>