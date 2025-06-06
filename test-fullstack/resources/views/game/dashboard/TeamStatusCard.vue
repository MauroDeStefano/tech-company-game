<!-- src/components/game/dashboard/TeamStatusCard.vue -->
<template>
  <BaseCard
    title="Team Status"
    icon="👥"
    :loading="loading"
    class="team-status-card"
  >
    <template #actions>
      <BaseButton
        variant="ghost"
        size="sm"
        icon="🧑‍💼"
        @click="goToHR"
      >
        Assumi
      </BaseButton>
    </template>

    <!-- Team Overview -->
    <div class="team-overview">
      <div class="overview-stat">
        <div class="stat-number">{{ totalTeamSize }}</div>
        <div class="stat-label">Membri Totali</div>
      </div>

      <div class="overview-stat">
        <div class="stat-number">{{ availableMembers }}</div>
        <div class="stat-label">Disponibili</div>
      </div>

      <div class="overview-stat">
        <div class="stat-number">{{ formatCurrency(monthlyTeamCosts) }}</div>
        <div class="stat-label">Costi/Mese</div>
      </div>
    </div>

    <!-- Developers Section -->
    <div class="team-section">
      <div class="section-header">
        <h4 class="section-title">
          <span class="section-icon">👨‍💻</span>
          Sviluppatori
          <span class="section-count">({{ developers.length }})</span>
        </h4>
        <BaseButton
          variant="ghost"
          size="sm"
          icon="🏗️"
          @click="goToProduction"
        >
          Gestisci
        </BaseButton>
      </div>

      <div v-if="developers.length === 0" class="empty-team-section">
        <span class="empty-icon">💻</span>
        <span class="empty-text">Nessuno sviluppatore nel team</span>
      </div>

      <div v-else class="team-members">
        <div
          v-for="developer in developers.slice(0, 3)"
          :key="developer.id"
          class="team-member"
          :class="{ 'team-member--busy': developer.is_busy }"
        >
          <div class="member-info">
            <div class="member-name">{{ developer.name }}</div>
            <div class="member-details">
              <span class="member-seniority">{{ getSeniorityText(developer.seniority) }}</span>
              <StatusBadge
                :status="developer.is_busy ? 'busy' : 'available'"
                size="sm"
                variant="minimal"
              />
            </div>
          </div>

          <div class="member-stats">
            <div class="member-projects">{{ developer.projects_completed || 0 }} progetti</div>
            <div class="member-cost">{{ formatCurrency(developer.monthly_salary || 2000) }}/mese</div>
          </div>
        </div>

        <div v-if="developers.length > 3" class="more-members">
          +{{ developers.length - 3 }} altri sviluppatori
        </div>
      </div>
    </div>

    <!-- Sales People Section -->
    <div class="team-section">
      <div class="section-header">
        <h4 class="section-title">
          <span class="section-icon">💼</span>
          Commerciali
          <span class="section-count">({{ salesPeople.length }})</span>
        </h4>
        <BaseButton
          variant="ghost"
          size="sm"
          icon="💼"
          @click="goToSales"
        >
          Gestisci
        </BaseButton>
      </div>

      <div v-if="salesPeople.length === 0" class="empty-team-section">
        <span class="empty-icon">💼</span>
        <span class="empty-text">Nessun commerciale nel team</span>
      </div>

      <div v-else class="team-members">
        <div
          v-for="salesPerson in salesPeople.slice(0, 3)"
          :key="salesPerson.id"
          class="team-member"
          :class="{ 'team-member--busy': salesPerson.is_busy }"
        >
          <div class="member-info">
            <div class="member-name">{{ salesPerson.name }}</div>
            <div class="member-details">
              <span class="member-experience">{{ getExperienceText(salesPerson.experience) }}</span>
              <StatusBadge
                :status="salesPerson.is_busy ? 'busy' : 'available'"
                size="sm"
                variant="minimal"
              />
            </div>
          </div>

          <div class="member-stats">
            <div class="member-projects">{{ salesPerson.projects_generated || 0 }} generati</div>
            <div class="member-cost">{{ formatCurrency(salesPerson.monthly_salary || 1500) }}/mese</div>
          </div>
        </div>

        <div v-if="salesPeople.length > 3" class="more-members">
          +{{ salesPeople.length - 3 }} altri commerciali
        </div>
      </div>
    </div>

    <!-- Team Efficiency -->
    <div class="team-efficiency">
      <h4 class="efficiency-title">⚡ Efficienza Team</h4>
      <div class="efficiency-metrics">
        <div class="efficiency-metric">
          <div class="metric-label">
            <span>Utilizzo</span>
            <span class="metric-value">{{ teamUtilization }}%</span>
          </div>
          <div class="metric-bar">
            <div
              class="metric-fill"
              :style="{ width: `${teamUtilization}%` }"
              :class="getUtilizationColorClass(teamUtilization)"
            ></div>
          </div>
        </div>

        <div class="efficiency-metric">
          <div class="metric-label">
            <span>Produttività</span>
            <span class="metric-value">{{ teamProductivity }}%</span>
          </div>
          <div class="metric-bar">
            <div
              class="metric-fill"
              :style="{ width: `${teamProductivity}%` }"
              :class="getProductivityColorClass(teamProductivity)"
            ></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Insights -->
    <div v-if="teamInsights.length > 0" class="team-insights">
      <h4 class="insights-title">💡 Suggerimenti</h4>
      <div class="insights-list">
        <div
          v-for="insight in teamInsights"
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
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useGameStore } from '@/js/stores/game'
import { formatCurrency } from '@/js/utils/helpers'
import StatusBadge from '@/js/components/shared/StatusBadge.vue'

// Stores
const gameStore = useGameStore()
const router = useRouter()

// Reactive state
const loading = ref(false)

// Computed properties
const developers = computed(() => gameStore.currentGame?.developers || [])
const salesPeople = computed(() => gameStore.currentGame?.sales_people || [])

const totalTeamSize = computed(() => developers.value.length + salesPeople.value.length)

const availableMembers = computed(() => {
  const availableDevelopers = developers.value.filter(d => !d.is_busy).length
  const availableSales = salesPeople.value.filter(s => !s.is_busy).length
  return availableDevelopers + availableSales
})

const monthlyTeamCosts = computed(() => {
  const devCosts = developers.value.reduce((sum, dev) => sum + (dev.monthly_salary || 2000), 0)
  const salesCosts = salesPeople.value.reduce((sum, sales) => sum + (sales.monthly_salary || 1500), 0)
  return devCosts + salesCosts
})

const teamUtilization = computed(() => {
  if (totalTeamSize.value === 0) return 0
  const busyMembers = totalTeamSize.value - availableMembers.value
  return Math.round((busyMembers / totalTeamSize.value) * 100)
})

const teamProductivity = computed(() => {
  // Base productivity calculation on average seniority/experience and projects completed
  if (totalTeamSize.value === 0) return 0

  const devProductivity = developers.value.reduce((sum, dev) => {
    const seniorityFactor = (dev.seniority || 1) / 5 * 100
    const projectsFactor = Math.min((dev.projects_completed || 0) * 10, 50)
    return sum + seniorityFactor + projectsFactor
  }, 0)

  const salesProductivity = salesPeople.value.reduce((sum, sales) => {
    const experienceFactor = (sales.experience || 1) / 5 * 100
    const projectsFactor = Math.min((sales.projects_generated || 0) * 10, 50)
    return sum + experienceFactor + projectsFactor
  }, 0)

  const totalProductivity = devProductivity + salesProductivity
  return Math.min(100, Math.round(totalProductivity / totalTeamSize.value))
})

const teamInsights = computed(() => {
  const insights = []

  // Team size insights
  if (totalTeamSize.value < 3) {
    insights.push({
      id: 'small-team',
      type: 'warning',
      icon: '👥',
      text: 'Team troppo piccolo per progetti complessi'
    })
  }

  // Balance insights
  const devCount = developers.value.length
  const salesCount = salesPeople.value.length

  if (devCount > 0 && salesCount === 0) {
    insights.push({
      id: 'no-sales',
      type: 'danger',
      icon: '💼',
      text: 'Nessun commerciale: non puoi generare nuovi progetti'
    })
  } else if (salesCount > 0 && devCount === 0) {
    insights.push({
      id: 'no-devs',
      type: 'danger',
      icon: '👨‍💻',
      text: 'Nessuno sviluppatore: non puoi completare progetti'
    })
  } else if (devCount > 0 && salesCount > 0) {
    const ratio = devCount / salesCount
    if (ratio > 3) {
      insights.push({
        id: 'few-sales',
        type: 'info',
        icon: '⚖️',
        text: 'Considera di assumere più commerciali'
      })
    } else if (ratio < 1.5) {
      insights.push({
        id: 'few-devs',
        type: 'info',
        icon: '⚖️',
        text: 'Considera di assumere più sviluppatori'
      })
    }
  }

  // Utilization insights
  if (teamUtilization.value < 30) {
    insights.push({
      id: 'low-utilization',
      type: 'warning',
      icon: '💤',
      text: 'Team sottoutilizzato: assegna più progetti'
    })
  } else if (teamUtilization.value > 90) {
    insights.push({
      id: 'high-utilization',
      type: 'success',
      icon: '🔥',
      text: 'Team molto produttivo!'
    })
  }

  // Cost insights
  const money = gameStore.currentGame?.money || 0
  const monthsAffordable = money / monthlyTeamCosts.value

  if (monthsAffordable < 2) {
    insights.push({
      id: 'high-costs',
      type: 'danger',
      icon: '💸',
      text: 'Costi team troppo alti rispetto al patrimonio'
    })
  }

  return insights.slice(0, 2) // Max 2 insights
})

// Methods
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

const getUtilizationColorClass = (utilization) => {
  if (utilization >= 80) return 'metric-fill--high'
  if (utilization >= 50) return 'metric-fill--medium'
  if (utilization >= 25) return 'metric-fill--low'
  return 'metric-fill--minimal'
}

const getProductivityColorClass = (productivity) => {
  if (productivity >= 80) return 'metric-fill--excellent'
  if (productivity >= 60) return 'metric-fill--good'
  if (productivity >= 40) return 'metric-fill--fair'
  return 'metric-fill--poor'
}

const goToProduction = () => {
  router.push({ name: 'Production' })
}

const goToSales = () => {
  router.push({ name: 'Sales' })
}

const goToHR = () => {
  router.push({ name: 'HR' })
}
</script>

<style scoped>
.team-status-card {
  @apply h-full;
}

/* Team Overview */
.team-overview {
  @apply grid grid-cols-3 gap-4 mb-6 p-4 bg-neutral-50 rounded-lg;
}

.overview-stat {
  @apply text-center;
}

.stat-number {
  @apply text-xl font-bold text-neutral-900;
}

.stat-label {
  @apply text-xs text-neutral-600 mt-1;
}

/* Team Sections */
.team-section {
  @apply mb-6 pb-4 border-b border-neutral-200 last:border-b-0;
}

.section-header {
  @apply flex items-center justify-between mb-3;
}

.section-title {
  @apply text-sm font-semibold text-neutral-900 flex items-center;
}

.section-icon {
  @apply text-base mr-2;
}

.section-count {
  @apply text-neutral-500 font-normal ml-1;
}

/* Empty Team Section */
.empty-team-section {
  @apply flex items-center justify-center py-4 text-neutral-500;
}

.empty-icon {
  @apply text-2xl mr-2;
}

.empty-text {
  @apply text-sm;
}

/* Team Members */
.team-members {
  @apply space-y-3;
}

.team-member {
  @apply flex items-center justify-between p-3 bg-white rounded-lg border border-neutral-200;
  @apply transition-all duration-200 hover:shadow-sm hover:border-brand-300;
}

.team-member--busy {
  @apply bg-warning-50 border-warning-200;
}

.team-member--busy:hover {
  @apply border-warning-300;
}

.member-info {
  @apply flex-1 min-w-0;
}

.member-name {
  @apply font-medium text-neutral-900 truncate;
}

.member-details {
  @apply flex items-center space-x-2 mt-1;
}

.member-seniority,
.member-experience {
  @apply text-xs text-neutral-600;
}

.member-stats {
  @apply text-right flex-shrink-0 ml-3;
}

.member-projects {
  @apply text-xs text-neutral-600;
}

.member-cost {
  @apply text-xs font-medium text-neutral-900;
}

.more-members {
  @apply text-center text-sm text-neutral-500 py-2 bg-neutral-50 rounded-lg;
}

/* Team Efficiency */
.team-efficiency {
  @apply mb-6;
}

.efficiency-title {
  @apply text-sm font-semibold text-neutral-900 mb-3;
}

.efficiency-metrics {
  @apply space-y-3;
}

.efficiency-metric {
  @apply space-y-1;
}

.metric-label {
  @apply flex items-center justify-between text-sm;
}

.metric-value {
  @apply font-semibold text-neutral-900;
}

.metric-bar {
  @apply w-full h-2 bg-neutral-200 rounded-full overflow-hidden;
}

.metric-fill {
  @apply h-full transition-all duration-500 ease-out;
}

.metric-fill--minimal {
  @apply bg-neutral-400;
}

.metric-fill--low {
  @apply bg-warning-500;
}

.metric-fill--medium {
  @apply bg-blue-500;
}

.metric-fill--high {
  @apply bg-brand-500;
}

.metric-fill--poor {
  @apply bg-danger-500;
}

.metric-fill--fair {
  @apply bg-warning-500;
}

.metric-fill--good {
  @apply bg-blue-500;
}

.metric-fill--excellent {
  @apply bg-success-500;
}

/* Team Insights */
.team-insights {
  @apply border-t border-neutral-200 pt-4;
}

.insights-title {
  @apply text-sm font-semibold text-neutral-900 mb-3;
}

.insights-list {
  @apply space-y-2;
}

.insight-item {
  @apply flex items-start p-2 rounded-lg text-sm;
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
  @apply leading-relaxed;
}

/* Responsive */
@media (max-width: 640px) {
  .team-overview {
    @apply grid-cols-1 gap-2 p-3;
  }

  .stat-number {
    @apply text-lg;
  }

  .section-header {
    @apply flex-col items-start space-y-2;
  }

  .team-member {
    @apply flex-col items-start space-y-2 p-2;
  }

  .member-stats {
    @apply text-left ml-0 w-full;
  }

  .efficiency-metrics {
    @apply space-y-2;
  }
}

/* Animations */
.team-member {
  animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(5px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.metric-fill {
  animation: metricGrow 1s ease-out;
}

@keyframes metricGrow {
  from {
    width: 0%;
  }
}
</style>