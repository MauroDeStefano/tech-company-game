<!-- src/components/game/dashboard/FinancialSummaryCard.vue -->
<template>
  <BaseCard
    title="Situazione Finanziaria"
    icon="💰"
    :loading="loading"
    class="financial-summary-card"
  >
    <!-- Current Balance -->
    <div class="current-balance">
      <div class="balance-header">
        <h3 class="balance-title">Patrimonio Attuale</h3>
        <span class="balance-trend" :class="balanceTrendClass">
          {{ balanceTrendIcon }}
        </span>
      </div>
      <div class="balance-amount" :class="balanceColorClass">
        {{ formatCurrency(currentBalance) }}
      </div>
      <div class="balance-change" :class="balanceChangeClass">
        <span class="change-label">{{ balanceChangeText }}</span>
      </div>
    </div>

    <!-- Financial Metrics -->
    <div class="financial-metrics">
      <div class="metrics-grid">
        <!-- Monthly Revenue -->
        <div class="metric-item metric-item--revenue">
          <div class="metric-header">
            <span class="metric-icon">📈</span>
            <span class="metric-label">Ricavi Mese</span>
          </div>
          <div class="metric-value">{{ formatCurrency(monthlyRevenue) }}</div>
          <div class="metric-description">
            Da {{ completedProjectsThisMonth }} progetti
          </div>
        </div>

        <!-- Monthly Costs -->
        <div class="metric-item metric-item--costs">
          <div class="metric-header">
            <span class="metric-icon">📉</span>
            <span class="metric-label">Costi Mese</span>
          </div>
          <div class="metric-value">{{ formatCurrency(monthlyCosts) }}</div>
          <div class="metric-description">
            {{ totalTeamSize }} membri del team
          </div>
        </div>

        <!-- Net Profit -->
        <div class="metric-item metric-item--profit">
          <div class="metric-header">
            <span class="metric-icon">💎</span>
            <span class="metric-label">Profitto Netto</span>
          </div>
          <div class="metric-value" :class="profitColorClass">
            {{ formatCurrency(monthlyProfit) }}
          </div>
          <div class="metric-description">
            {{ profitMargin }}% margine
          </div>
        </div>

        <!-- Runway -->
        <div class="metric-item metric-item--runway">
          <div class="metric-header">
            <span class="metric-icon">⏱️</span>
            <span class="metric-label">Runway</span>
          </div>
          <div class="metric-value" :class="runwayColorClass">
            {{ runwayText }}
          </div>
          <div class="metric-description">
            Con i costi attuali
          </div>
        </div>
      </div>
    </div>

    <!-- Cash Flow Chart -->
    <div class="cash-flow-section">
      <h4 class="cash-flow-title">Flusso di Cassa (Ultimi 7 Giorni)</h4>
      <div class="cash-flow-chart">
        <div class="chart-container">
          <div
            v-for="(day, index) in cashFlowData"
            :key="index"
            class="chart-bar-container"
          >
            <div class="chart-bar-wrapper">
              <!-- Positive bar (revenue) -->
              <div
                v-if="day.revenue > 0"
                class="chart-bar chart-bar--positive"
                :style="{ height: `${(day.revenue / maxDayValue) * 100}%` }"
                :title="`Ricavi: ${formatCurrency(day.revenue)}`"
              ></div>

              <!-- Negative bar (costs) -->
              <div
                v-if="day.costs > 0"
                class="chart-bar chart-bar--negative"
                :style="{ height: `${(day.costs / maxDayValue) * 100}%` }"
                :title="`Costi: ${formatCurrency(day.costs)}`"
              ></div>
            </div>

            <div class="chart-label">
              {{ day.label }}
            </div>

            <div class="chart-value" :class="{
              'chart-value--positive': day.net > 0,
              'chart-value--negative': day.net < 0,
              'chart-value--neutral': day.net === 0
            }">
              {{ day.net > 0 ? '+' : '' }}{{ formatCurrency(day.net) }}
            </div>
          </div>
        </div>

        <!-- Chart Legend -->
        <div class="chart-legend">
          <div class="legend-item">
            <div class="legend-color legend-color--positive"></div>
            <span class="legend-label">Ricavi</span>
          </div>
          <div class="legend-item">
            <div class="legend-color legend-color--negative"></div>
            <span class="legend-label">Costi</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Financial Insights -->
    <div class="financial-insights">
      <h4 class="insights-title">💡 Analisi Finanziaria</h4>
      <div class="insights-list">
        <div
          v-for="insight in financialInsights"
          :key="insight.id"
          class="insight-item"
          :class="`insight-item--${insight.type}`"
        >
          <span class="insight-icon">{{ insight.icon }}</span>
          <div class="insight-content">
            <span class="insight-text">{{ insight.text }}</span>
            <span v-if="insight.action" class="insight-action" @click="handleInsightAction(insight)">
              {{ insight.actionText }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Financial Actions -->
    <div class="financial-actions">
      <h4 class="actions-title">Azioni Rapide</h4>
      <div class="actions-grid">
        <button
          class="action-button"
          :class="{ 'action-button--disabled': !canHire }"
          @click="expandTeam"
          :disabled="!canHire"
        >
          <span class="action-icon">👥</span>
          <div class="action-content">
            <span class="action-label">Espandi Team</span>
            <span class="action-description">{{ expandTeamDescription }}</span>
          </div>
        </button>

        <button
          class="action-button"
          :class="{ 'action-button--disabled': totalTeamSize <= 2 }"
          @click="optimizeCosts"
          :disabled="totalTeamSize <= 2"
        >
          <span class="action-icon">⚡</span>
          <div class="action-content">
            <span class="action-label">Ottimizza Costi</span>
            <span class="action-description">{{ optimizeCostsDescription }}</span>
          </div>
        </button>

        <button
          class="action-button"
          @click="viewDetailedReport"
        >
          <span class="action-icon">📊</span>
          <div class="action-content">
            <span class="action-label">Report Dettagliato</span>
            <span class="action-description">Analisi completa</span>
          </div>
        </button>
      </div>
    </div>
  </BaseCard>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
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
const previousBalance = ref(0)

// Computed properties
const currentBalance = computed(() => {
  return gameStore.currentGame?.money || 0
})

const monthlyCosts = computed(() => {
  return gameStore.currentGame?.monthly_costs || 0
})

const totalTeamSize = computed(() => {
  const developers = gameStore.currentGame?.developers?.length || 0
  const salesPeople = gameStore.currentGame?.sales_people?.length || 0
  return developers + salesPeople
})

const completedProjects = computed(() => {
  return gameStore.currentGame?.projects?.filter(p => p.status === 'completed') || []
})

const completedProjectsThisMonth = computed(() => {
  const now = new Date()
  return completedProjects.value.filter(project => {
    const completedDate = new Date(project.completed_at)
    return completedDate.getMonth() === now.getMonth() &&
           completedDate.getFullYear() === now.getFullYear()
  }).length
})

const monthlyRevenue = computed(() => {
  const now = new Date()
  return completedProjects.value
    .filter(project => {
      const completedDate = new Date(project.completed_at)
      return completedDate.getMonth() === now.getMonth() &&
             completedDate.getFullYear() === now.getFullYear()
    })
    .reduce((sum, project) => sum + (project.value || 0), 0)
})

const monthlyProfit = computed(() => {
  return monthlyRevenue.value - monthlyCosts.value
})

const profitMargin = computed(() => {
  if (monthlyRevenue.value === 0) return 0
  return Math.round((monthlyProfit.value / monthlyRevenue.value) * 100)
})

const runway = computed(() => {
  if (monthlyCosts.value <= 0) return Infinity
  return Math.floor(currentBalance.value / monthlyCosts.value)
})

const runwayText = computed(() => {
  if (runway.value === Infinity) return '∞'
  if (runway.value <= 0) return '0 mesi'
  if (runway.value === 1) return '1 mese'
  return `${runway.value} mesi`
})

const balanceColorClass = computed(() => {
  const balance = currentBalance.value
  if (balance < 0) return 'balance-amount--danger'
  if (balance < 1000) return 'balance-amount--warning'
  return 'balance-amount--success'
})

const balanceTrendClass = computed(() => {
  const change = currentBalance.value - previousBalance.value
  if (change > 0) return 'balance-trend--positive'
  if (change < 0) return 'balance-trend--negative'
  return 'balance-trend--neutral'
})

const balanceTrendIcon = computed(() => {
  const change = currentBalance.value - previousBalance.value
  if (change > 0) return '📈'
  if (change < 0) return '📉'
  return '➡️'
})

const balanceChangeClass = computed(() => {
  const change = currentBalance.value - previousBalance.value
  if (change > 0) return 'balance-change--positive'
  if (change < 0) return 'balance-change--negative'
  return 'balance-change--neutral'
})

const balanceChangeText = computed(() => {
  const change = currentBalance.value - previousBalance.value
  if (change === 0) return 'Nessuna variazione'
  const sign = change > 0 ? '+' : ''
  return `${sign}${formatCurrency(change)} dall'ultimo aggiornamento`
})

const profitColorClass = computed(() => {
  const profit = monthlyProfit.value
  if (profit < 0) return 'metric-value--danger'
  if (profit === 0) return 'metric-value--neutral'
  return 'metric-value--success'
})

const runwayColorClass = computed(() => {
  const months = runway.value
  if (months === Infinity) return 'metric-value--success'
  if (months <= 1) return 'metric-value--danger'
  if (months <= 3) return 'metric-value--warning'
  return 'metric-value--success'
})

const canHire = computed(() => {
  return currentBalance.value > 5000 && monthlyProfit.value > 0
})

const expandTeamDescription = computed(() => {
  if (!canHire.value) return 'Situazione finanziaria insufficiente'
  return 'Aumenta la capacità produttiva'
})

const optimizeCostsDescription = computed(() => {
  if (totalTeamSize.value <= 2) return 'Team già al minimo'
  return 'Riduci i costi del personale'
})

// Cash flow data (simulated for last 7 days)
const cashFlowData = computed(() => {
  const days = []
  const today = new Date()

  for (let i = 6; i >= 0; i--) {
    const date = new Date(today)
    date.setDate(date.getDate() - i)

    // Simulate daily data (in a real app, this would come from the backend)
    const dailyCosts = monthlyCosts.value / 30
    const dailyRevenue = i === 0 ? monthlyRevenue.value / 7 : Math.random() * (monthlyRevenue.value / 14)

    days.push({
      label: i === 0 ? 'Oggi' : i === 1 ? 'Ieri' : date.toLocaleDateString('it-IT', { weekday: 'short' }),
      revenue: Math.round(dailyRevenue),
      costs: Math.round(dailyCosts),
      net: Math.round(dailyRevenue - dailyCosts)
    })
  }

  return days
})

const maxDayValue = computed(() => {
  const maxRevenue = Math.max(...cashFlowData.value.map(d => d.revenue))
  const maxCosts = Math.max(...cashFlowData.value.map(d => d.costs))
  return Math.max(maxRevenue, maxCosts)
})

// Financial insights
const financialInsights = computed(() => {
  const insights = []

  // Cash flow insights
  if (monthlyProfit.value < 0) {
    insights.push({
      id: 'negative-profit',
      type: 'danger',
      icon: '⚠️',
      text: 'Profitto negativo: i costi superano i ricavi',
      action: () => router.push({ name: 'HR' }),
      actionText: 'Ottimizza team'
    })
  }

  // Runway insights
  if (runway.value <= 2 && runway.value !== Infinity) {
    insights.push({
      id: 'low-runway',
      type: 'danger',
      icon: '🚨',
      text: `Solo ${runway.value} ${runway.value === 1 ? 'mese' : 'mesi'} di liquidità rimanente`,
      action: () => router.push({ name: 'Production' }),
      actionText: 'Completa progetti'
    })
  }

  // Growth opportunities
  if (currentBalance.value > 10000 && monthlyProfit.value > 2000) {
    insights.push({
      id: 'growth-opportunity',
      type: 'success',
      icon: '🚀',
      text: 'Situazione finanziaria ottima: considera di espandere',
      action: () => router.push({ name: 'HR' }),
      actionText: 'Assumi personale'
    })
  }

  // Efficiency insights
  if (completedProjectsThisMonth.value === 0) {
    insights.push({
      id: 'no-revenue',
      type: 'warning',
      icon: '📊',
      text: 'Nessun progetto completato questo mese',
      action: () => router.push({ name: 'Sales' }),
      actionText: 'Genera progetti'
    })
  }

  return insights.slice(0, 3)
})

// Methods
const expandTeam = () => {
  if (canHire.value) {
    router.push({ name: 'HR' })
  }
}

const optimizeCosts = () => {
  if (totalTeamSize.value > 2) {
    notificationStore.info('Per ottimizzare i costi, considera di ridurre il team nella sezione HR')
    router.push({ name: 'HR' })
  }
}

const viewDetailedReport = () => {
  // This would open a detailed financial report modal or navigate to a report page
  notificationStore.info('Report dettagliato: funzionalità in arrivo!')
}

const handleInsightAction = (insight) => {
  if (insight.action) {
    insight.action()
  }
}

const updatePreviousBalance = () => {
  previousBalance.value = currentBalance.value
}

// Lifecycle
onMounted(() => {
  updatePreviousBalance()
})

// Watch for balance changes
let lastBalance = currentBalance.value
setInterval(() => {
  if (currentBalance.value !== lastBalance) {
    previousBalance.value = lastBalance
    lastBalance = currentBalance.value
  }
}, 5000)
</script>

<style scoped>
.financial-summary-card {
  @apply h-full;
}

/* Current Balance */
.current-balance {
  @apply mb-6 p-4 bg-gradient-to-r from-brand-50 to-brand-100 rounded-lg border border-brand-200;
}

.balance-header {
  @apply flex items-center justify-between mb-2;
}

.balance-title {
  @apply text-lg font-semibold text-neutral-800;
}

.balance-trend {
  @apply text-xl;
}

.balance-trend--positive {
  @apply text-success-600;
}

.balance-trend--negative {
  @apply text-danger-600;
}

.balance-trend--neutral {
  @apply text-neutral-500;
}

.balance-amount {
  @apply text-3xl font-bold mb-1;
}

.balance-amount--success {
  @apply text-success-600;
}

.balance-amount--warning {
  @apply text-warning-600;
}

.balance-amount--danger {
  @apply text-danger-600;
}

.balance-change {
  @apply text-sm;
}

.balance-change--positive {
  @apply text-success-600;
}

.balance-change--negative {
  @apply text-danger-600;
}

.balance-change--neutral {
  @apply text-neutral-500;
}

/* Financial Metrics */
.financial-metrics {
  @apply mb-6;
}

.metrics-grid {
  @apply grid grid-cols-2 lg:grid-cols-4 gap-4;
}

.metric-item {
  @apply bg-neutral-50 rounded-lg p-3 border border-neutral-200;
}

.metric-header {
  @apply flex items-center mb-2;
}

.metric-icon {
  @apply text-lg mr-2;
}

.metric-label {
  @apply text-sm font-medium text-neutral-600;
}

.metric-value {
  @apply text-lg font-bold text-neutral-900 mb-1;
}

.metric-value--success {
  @apply text-success-600;
}

.metric-value--warning {
  @apply text-warning-600;
}

.metric-value--danger {
  @apply text-danger-600;
}

.metric-value--neutral {
  @apply text-neutral-500;
}

.metric-description {
  @apply text-xs text-neutral-500;
}

/* Cash Flow Chart */
.cash-flow-section {
  @apply mb-6;
}

.cash-flow-title {
  @apply text-lg font-semibold text-neutral-900 mb-4;
}

.cash-flow-chart {
  @apply bg-neutral-50 rounded-lg p-4;
}

.chart-container {
  @apply flex items-end justify-between h-32 mb-4;
}

.chart-bar-container {
  @apply flex-1 flex flex-col items-center;
}

.chart-bar-wrapper {
  @apply relative w-8 h-full flex items-end justify-center;
}

.chart-bar {
  @apply w-full transition-all duration-500 ease-out;
  @apply border-t-2 border-l border-r;
}

.chart-bar--positive {
  @apply bg-success-500 border-success-600;
}

.chart-bar--negative {
  @apply bg-danger-500 border-danger-600;
  @apply absolute bottom-0;
}

.chart-label {
  @apply text-xs text-neutral-600 mt-2;
}

.chart-value {
  @apply text-xs font-medium mt-1;
}

.chart-value--positive {
  @apply text-success-600;
}

.chart-value--negative {
  @apply text-danger-600;
}

.chart-value--neutral {
  @apply text-neutral-500;
}

.chart-legend {
  @apply flex items-center justify-center space-x-4;
}

.legend-item {
  @apply flex items-center;
}

.legend-color {
  @apply w-3 h-3 rounded mr-2;
}

.legend-color--positive {
  @apply bg-success-500;
}

.legend-color--negative {
  @apply bg-danger-500;
}

.legend-label {
  @apply text-sm text-neutral-600;
}

/* Financial Insights */
.financial-insights {
  @apply mb-6;
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

.insight-content {
  @apply flex-1;
}

.insight-text {
  @apply text-sm block;
}

.insight-action {
  @apply text-xs opacity-75 block mt-0.5 cursor-pointer hover:opacity-100;
  @apply transition-opacity duration-200;
}

/* Financial Actions */
.financial-actions {
  @apply border-t border-neutral-200 pt-4;
}

.actions-title {
  @apply text-lg font-semibold text-neutral-900 mb-3;
}

.actions-grid {
  @apply space-y-3;
}

.action-button {
  @apply w-full flex items-center p-3 rounded-lg;
  @apply bg-neutral-50 hover:bg-neutral-100 border border-neutral-200;
  @apply transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-brand-500;
  @apply disabled:opacity-50 disabled:cursor-not-allowed;
}

.action-button:hover:not(:disabled) {
  @apply shadow-sm transform -translate-y-0.5;
}

.action-button--disabled {
  @apply opacity-50 cursor-not-allowed;
}

.action-icon {
  @apply text-xl mr-3;
}

.action-content {
  @apply flex-1 text-left;
}

.action-label {
  @apply block font-medium text-neutral-900;
}

.action-description {
  @apply text-sm text-neutral-600;
}

/* Responsive */
@media (max-width: 1023px) {
  .metrics-grid {
    @apply grid-cols-2 gap-3;
  }

  .balance-amount {
    @apply text-2xl;
  }
}

@media (max-width: 640px) {
  .metrics-grid {
    @apply grid-cols-1 gap-2;
  }

  .balance-amount {
    @apply text-xl;
  }

  .chart-container {
    @apply h-24;
  }

  .chart-bar-wrapper {
    @apply w-6;
  }
}

/* Animations */
.chart-bar {
  animation: barGrow 1s ease-out;
}

@keyframes barGrow {
  from {
    height: 0%;
  }
}

.balance-amount {
  @apply transition-all duration-300;
}

.insight-item {
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
</style>