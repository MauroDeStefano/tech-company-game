<!-- src/components/game/dashboard/GameStatsOverview.vue -->
<template>
  <BaseCard
    title="Statistiche Principali"
    icon="📊"
    :loading="loading"
    class="stats-overview"
  >
    <div class="stats-grid">
      <!-- Patrimonio -->
      <div class="stat-item stat-item--primary">
        <div class="stat-header">
          <span class="stat-icon">💰</span>
          <span class="stat-label">Patrimonio</span>
        </div>
        <div class="stat-value" :class="moneyColorClass">
          {{ formatCurrency(gameStats.money) }}
        </div>
        <div class="stat-change" :class="moneyTrendClass">
          <span class="change-icon">{{ moneyTrendIcon }}</span>
          <span class="change-text">{{ moneyChangeText }}</span>
        </div>
      </div>

      <!-- Progetti Completati -->
      <div class="stat-item">
        <div class="stat-header">
          <span class="stat-icon">✅</span>
          <span class="stat-label">Progetti Completati</span>
        </div>
        <div class="stat-value">
          {{ gameStats.projectsCompleted }}
        </div>
        <div class="stat-description">
          {{ projectsCompletedText }}
        </div>
      </div>

      <!-- Revenue Totale -->
      <div class="stat-item">
        <div class="stat-header">
          <span class="stat-icon">💵</span>
          <span class="stat-label">Revenue Totale</span>
        </div>
        <div class="stat-value">
          {{ formatCurrency(gameStats.totalRevenue) }}
        </div>
        <div class="stat-description">
          Da {{ gameStats.projectsCompleted }} progetti
        </div>
      </div>

      <!-- Efficienza Team -->
      <div class="stat-item">
        <div class="stat-header">
          <span class="stat-icon">⚡</span>
          <span class="stat-label">Efficienza</span>
        </div>
        <div class="stat-value">
          {{ efficiencyPercentage }}%
        </div>
        <div class="stat-description">
          {{ efficiencyDescription }}
        </div>
      </div>
    </div>

    <!-- Progress Bars -->
    <div class="progress-section">
      <h4 class="progress-title">Progressi Attuali</h4>

      <div class="progress-list">
        <!-- Progetti in corso -->
        <div v-if="activeProjects.length > 0" class="progress-item">
          <div class="progress-header">
            <span class="progress-label">Progetti in Corso</span>
            <span class="progress-count">{{ activeProjects.length }}</span>
          </div>
          <div class="progress-bars">
            <div
              v-for="project in activeProjects.slice(0, 3)"
              :key="project.id"
              class="project-progress"
            >
              <div class="project-info">
                <span class="project-name">{{ project.name }}</span>
                <span class="project-percentage">{{ Math.round(project.progress || 0) }}%</span>
              </div>
              <div class="progress-bar">
                <div
                  class="progress-fill"
                  :style="{ width: `${project.progress || 0}%` }"
                ></div>
              </div>
            </div>

            <div v-if="activeProjects.length > 3" class="more-projects">
              +{{ activeProjects.length - 3 }} altri progetti
            </div>
          </div>
        </div>

        <!-- Generazioni in corso -->
        <div v-if="activeGenerations.length > 0" class="progress-item">
          <div class="progress-header">
            <span class="progress-label">Generazioni in Corso</span>
            <span class="progress-count">{{ activeGenerations.length }}</span>
          </div>
          <div class="progress-bars">
            <div
              v-for="generation in activeGenerations.slice(0, 2)"
              :key="generation.id"
              class="project-progress"
            >
              <div class="project-info">
                <span class="project-name">{{ generation.sales_person_name }}</span>
                <span class="project-percentage">{{ Math.round(generation.progress || 0) }}%</span>
              </div>
              <div class="progress-bar">
                <div
                  class="progress-fill progress-fill--sales"
                  :style="{ width: `${generation.progress || 0}%` }"
                ></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Insights -->
    <div class="insights-section">
      <h4 class="insights-title">💡 Insights</h4>
      <div class="insights-list">
        <div
          v-for="insight in insights"
          :key="insight.id"
          class="insight-item"
          :class="`insight-item--${insight.type}`"
        >
          <span class="insight-icon">{{ insight.icon }}</span>
          <span class="insight-text">{{ insight.text }}</span>
        </div>
      </div>
    </div>
  </BaseCard>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useGameStore } from '@/stores/game'
import { formatCurrency } from '@/utils/helpers'

// Stores
const gameStore = useGameStore()

// Reactive state
const loading = ref(false)
const previousMoney = ref(0)

// Computed properties
const gameStats = computed(() => {
  const game = gameStore.currentGame
  if (!game) return {
    money: 0,
    projectsCompleted: 0,
    totalRevenue: 0,
    teamSize: 0
  }

  const completedProjects = game.projects?.filter(p => p.status === 'completed') || []

  return {
    money: game.money || 0,
    projectsCompleted: completedProjects.length,
    totalRevenue: completedProjects.reduce((sum, p) => sum + (p.value || 0), 0),
    teamSize: (game.developers?.length || 0) + (game.sales_people?.length || 0),
    monthlyCosts: game.monthly_costs || 0
  }
})

const activeProjects = computed(() => {
  return gameStore.currentGame?.projects?.filter(p => p.status === 'in_progress') || []
})

const activeGenerations = computed(() => {
  return gameStore.currentGame?.project_generations?.filter(g => g.status === 'in_progress') || []
})

const moneyColorClass = computed(() => {
  const money = gameStats.value.money
  if (money < 0) return 'stat-value--danger'
  if (money < 1000) return 'stat-value--warning'
  return 'stat-value--success'
})

const moneyTrendClass = computed(() => {
  const change = gameStats.value.money - previousMoney.value
  if (change > 0) return 'stat-change--positive'
  if (change < 0) return 'stat-change--negative'
  return 'stat-change--neutral'
})

const moneyTrendIcon = computed(() => {
  const change = gameStats.value.money - previousMoney.value
  if (change > 0) return '📈'
  if (change < 0) return '📉'
  return '➡️'
})

const moneyChangeText = computed(() => {
  const change = gameStats.value.money - previousMoney.value
  if (change === 0) return 'Stabile'
  const sign = change > 0 ? '+' : ''
  return `${sign}${formatCurrency(change)}`
})

const projectsCompletedText = computed(() => {
  const count = gameStats.value.projectsCompleted
  if (count === 0) return 'Nessun progetto completato'
  if (count === 1) return '1 progetto completato'
  return `${count} progetti completati`
})

const efficiencyPercentage = computed(() => {
  const stats = gameStats.value
  if (stats.teamSize === 0) return 0

  // Calcola efficienza basata su progetti completati vs dimensione team
  const efficiency = (stats.projectsCompleted / Math.max(stats.teamSize, 1)) * 100
  return Math.min(100, Math.round(efficiency))
})

const efficiencyDescription = computed(() => {
  const efficiency = efficiencyPercentage.value
  if (efficiency >= 80) return 'Eccellente'
  if (efficiency >= 60) return 'Buona'
  if (efficiency >= 40) return 'Sufficiente'
  return 'Da migliorare'
})

const insights = computed(() => {
  const insights = []
  const stats = gameStats.value

  // Insight sul patrimonio
  if (stats.money < 1000) {
    insights.push({
      id: 'low-money',
      type: 'warning',
      icon: '⚠️',
      text: 'Patrimonio basso: considera di completare progetti urgentemente'
    })
  } else if (stats.money > 10000) {
    insights.push({
      id: 'high-money',
      type: 'success',
      icon: '💎',
      text: 'Ottima situazione finanziaria: considera di espandere il team'
    })
  }

  // Insight sui progetti
  if (activeProjects.value.length === 0) {
    insights.push({
      id: 'no-projects',
      type: 'info',
      icon: '🎯',
      text: 'Nessun progetto attivo: fai generare nuovi progetti dai commerciali'
    })
  }

  // Insight sull'efficienza
  if (efficiencyPercentage.value < 40) {
    insights.push({
      id: 'low-efficiency',
      type: 'warning',
      icon: '🔧',
      text: 'Efficienza bassa: ottimizza l\'assegnazione dei progetti'
    })
  }

  // Insight sui costi
  const monthlyIncome = stats.totalRevenue / Math.max(1, gameStore.currentGame?.created_at ?
    new Date().getTime() - new Date(gameStore.currentGame.created_at).getTime() : 1) * 30 * 24 * 60 * 60 * 1000

  if (stats.monthlyCosts > monthlyIncome) {
    insights.push({
      id: 'high-costs',
      type: 'danger',
      icon: '📉',
      text: 'I costi mensili superano i ricavi: riduci il personale o aumenta i progetti'
    })
  }

  return insights.slice(0, 3) // Mostra max 3 insights
})

// Methods
const updatePreviousMoney = () => {
  previousMoney.value = gameStats.value.money
}

const refreshStats = async () => {
  loading.value = true
  try {
    await gameStore.refreshGameState()
  } catch (error) {
    console.error('Error refreshing stats:', error)
  } finally {
    loading.value = false
  }
}

// Auto-refresh stats
let refreshInterval = null
onMounted(() => {
  updatePreviousMoney()

  // Refresh stats ogni 30 secondi
  refreshInterval = setInterval(refreshStats, 30000)
})

onUnmounted(() => {
  if (refreshInterval) {
    clearInterval(refreshInterval)
  }
})

// Watch for money changes to update trend
const unwatchMoney = computed(() => gameStats.value.money)
let lastMoney = gameStats.value.money

setInterval(() => {
  if (gameStats.value.money !== lastMoney) {
    updatePreviousMoney()
    lastMoney = gameStats.value.money
  }
}, 5000)
</script>

<style scoped>
.stats-overview {
  @apply h-full;
}

/* Stats Grid */
.stats-grid {
  @apply grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6;
}

.stat-item {
  @apply bg-neutral-50 rounded-lg p-4;
  @apply border border-neutral-200;
  @apply transition-all duration-200 hover:shadow-md;
}

.stat-item--primary {
  @apply bg-brand-50 border-brand-200;
}

.stat-header {
  @apply flex items-center mb-2;
}

.stat-icon {
  @apply text-lg mr-2;
}

.stat-label {
  @apply text-sm font-medium text-neutral-600;
}

.stat-value {
  @apply text-2xl font-bold text-neutral-900 mb-1;
}

.stat-value--success {
  @apply text-success-600;
}

.stat-value--warning {
  @apply text-warning-600;
}

.stat-value--danger {
  @apply text-danger-600;
}

.stat-change {
  @apply flex items-center text-xs;
}

.stat-change--positive {
  @apply text-success-600;
}

.stat-change--negative {
  @apply text-danger-600;
}

.stat-change--neutral {
  @apply text-neutral-500;
}

.change-icon {
  @apply mr-1;
}

.stat-description {
  @apply text-xs text-neutral-500;
}

/* Progress Section */
.progress-section {
  @apply mb-6;
}

.progress-title {
  @apply text-lg font-semibold text-neutral-900 mb-4;
}

.progress-list {
  @apply space-y-4;
}

.progress-item {
  @apply bg-neutral-50 rounded-lg p-4;
}

.progress-header {
  @apply flex items-center justify-between mb-3;
}

.progress-label {
  @apply font-medium text-neutral-700;
}

.progress-count {
  @apply text-sm text-neutral-500 bg-neutral-200 px-2 py-1 rounded-full;
}

.progress-bars {
  @apply space-y-3;
}

.project-progress {
  @apply space-y-1;
}

.project-info {
  @apply flex items-center justify-between text-sm;
}

.project-name {
  @apply text-neutral-700 truncate flex-1 mr-2;
}

.project-percentage {
  @apply text-neutral-500 font-medium;
}

.progress-bar {
  @apply w-full h-2 bg-neutral-200 rounded-full overflow-hidden;
}

.progress-fill {
  @apply h-full bg-brand-500 transition-all duration-500 ease-out;
}

.progress-fill--sales {
  @apply bg-success-500;
}

.more-projects {
  @apply text-xs text-neutral-500 text-center py-2;
}

/* Insights Section */
.insights-section {
  @apply border-t border-neutral-200 pt-4;
}

.insights-title {
  @apply text-lg font-semibold text-neutral-900 mb-3;
}

.insights-list {
  @apply space-y-2;
}

.insight-item {
  @apply flex items-start p-3 rounded-lg;
  @apply transition-colors duration-200;
}

.insight-item--info {
  @apply bg-blue-50 text-blue-800;
}

.insight-item--success {
  @apply bg-success-50 text-success-800;
}

.insight-item--warning {
  @apply bg-warning-50 text-warning-800;
}

.insight-item--danger {
  @apply bg-danger-50 text-danger-800;
}

.insight-icon {
  @apply mr-2 mt-0.5 flex-shrink-0;
}

.insight-text {
  @apply text-sm leading-relaxed;
}

/* Responsive */
@media (max-width: 640px) {
  .stats-grid {
    @apply grid-cols-1 gap-3;
  }

  .stat-value {
    @apply text-xl;
  }

  .progress-section {
    @apply mb-4;
  }

  .insights-section {
    @apply pt-3;
  }
}

/* Loading states */
.stats-overview.loading .stat-item {
  @apply animate-pulse;
}

.stats-overview.loading .progress-item {
  @apply animate-pulse;
}

/* Animation for stats updates */
.stat-value {
  @apply transition-all duration-300;
}

.progress-fill {
  animation: progressFill 1s ease-out;
}

@keyframes progressFill {
  from {
    width: 0%;
  }
}
</style>