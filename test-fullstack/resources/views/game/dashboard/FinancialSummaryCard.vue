<!-- src/components/game/dashboard/FinancialSummaryCard.vue -->
<template>
  <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
    <!-- Header -->
    <div class="mb-6">
      <div class="flex items-center gap-2">
        <span class="text-2xl">💰</span>
        <h3 class="text-lg font-bold text-gray-900">Situazione Finanziaria</h3>
        <div v-if="loading" class="ml-2">
          <svg class="animate-spin w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        </div>
      </div>
    </div>

    <!-- Current Balance -->
    <div class="mb-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border border-blue-100">
      <div class="flex items-center justify-between mb-2">
        <h3 class="text-lg font-semibold text-gray-900">Patrimonio Attuale</h3>
        <span class="text-xl">{{ balanceTrendIcon }}</span>
      </div>
      <div 
        class="text-3xl font-bold mb-2"
        :class="{
          'text-red-600': currentBalance < 0,
          'text-yellow-600': currentBalance >= 0 && currentBalance < 1000,
          'text-green-600': currentBalance >= 1000
        }"
      >
        {{ formatCurrency(currentBalance) }}
      </div>
      <div 
        class="text-sm"
        :class="{
          'text-green-600': currentBalance - previousBalance > 0,
          'text-red-600': currentBalance - previousBalance < 0,
          'text-gray-600': currentBalance - previousBalance === 0
        }"
      >
        {{ balanceChangeText }}
      </div>
    </div>

    <!-- Financial Metrics -->
    <div class="mb-6">
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Monthly Revenue -->
        <div class="bg-green-50 border border-green-200 rounded-lg p-3">
          <div class="flex items-center gap-2 mb-2">
            <span class="text-lg">📈</span>
            <span class="text-sm font-medium text-gray-700">Ricavi Mese</span>
          </div>
          <div class="text-xl font-bold text-gray-900">{{ formatCurrency(monthlyRevenue) }}</div>
          <div class="text-xs text-gray-600 mt-1">
            Da {{ completedProjectsThisMonth }} progetti
          </div>
        </div>

        <!-- Monthly Costs -->
        <div class="bg-red-50 border border-red-200 rounded-lg p-3">
          <div class="flex items-center gap-2 mb-2">
            <span class="text-lg">📉</span>
            <span class="text-sm font-medium text-gray-700">Costi Mese</span>
          </div>
          <div class="text-xl font-bold text-gray-900">{{ formatCurrency(monthlyCosts) }}</div>
          <div class="text-xs text-gray-600 mt-1">
            {{ totalTeamSize }} membri del team
          </div>
        </div>

        <!-- Net Profit -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
          <div class="flex items-center gap-2 mb-2">
            <span class="text-lg">💎</span>
            <span class="text-sm font-medium text-gray-700">Profitto Netto</span>
          </div>
          <div 
            class="text-xl font-bold"
            :class="{
              'text-red-600': monthlyProfit < 0,
              'text-gray-600': monthlyProfit === 0,
              'text-green-600': monthlyProfit > 0
            }"
          >
            {{ formatCurrency(monthlyProfit) }}
          </div>
          <div class="text-xs text-gray-600 mt-1">
            {{ profitMargin }}% margine
          </div>
        </div>

        <!-- Runway -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
          <div class="flex items-center gap-2 mb-2">
            <span class="text-lg">⏱️</span>
            <span class="text-sm font-medium text-gray-700">Runway</span>
          </div>
          <div 
            class="text-xl font-bold"
            :class="{
              'text-green-600': runway === Infinity || runway > 3,
              'text-yellow-600': runway > 1 && runway <= 3,
              'text-red-600': runway <= 1
            }"
          >
            {{ runwayText }}
          </div>
          <div class="text-xs text-gray-600 mt-1">
            Con i costi attuali
          </div>
        </div>
      </div>
    </div>

    <!-- Cash Flow Chart -->
    <div class="mb-6">
      <h4 class="text-lg font-semibold text-gray-900 mb-4">Flusso di Cassa (Ultimi 7 Giorni)</h4>
      <div class="bg-gray-50 rounded-lg p-4">
        <div class="flex items-end justify-between gap-2 h-32 mb-4">
          <div
            v-for="(day, index) in cashFlowData"
            :key="index"
            class="flex-1 flex flex-col items-center"
          >
            <div class="flex flex-col items-center justify-end h-24 mb-2 relative">
              <!-- Positive bar (revenue) -->
              <div
                v-if="day.revenue > 0"
                class="w-full bg-green-500 rounded-t-sm"
                :style="{ height: `${(day.revenue / maxDayValue) * 80}px` }"
                :title="`Ricavi: ${formatCurrency(day.revenue)}`"
              ></div>

              <!-- Negative bar (costs) -->
              <div
                v-if="day.costs > 0"
                class="w-full bg-red-500 rounded-b-sm"
                :style="{ height: `${(day.costs / maxDayValue) * 80}px` }"
                :title="`Costi: ${formatCurrency(day.costs)}`"
              ></div>
            </div>

            <div class="text-xs text-gray-600 text-center mb-1">
              {{ day.label }}
            </div>

            <div 
              class="text-xs font-medium text-center"
              :class="{
                'text-green-600': day.net > 0,
                'text-red-600': day.net < 0,
                'text-gray-600': day.net === 0
              }"
            >
              {{ day.net > 0 ? '+' : '' }}{{ formatCurrency(day.net) }}
            </div>
          </div>
        </div>

        <!-- Chart Legend -->
        <div class="flex items-center justify-center gap-6">
          <div class="flex items-center gap-2">
            <div class="w-3 h-3 bg-green-500 rounded"></div>
            <span class="text-sm text-gray-700">Ricavi</span>
          </div>
          <div class="flex items-center gap-2">
            <div class="w-3 h-3 bg-red-500 rounded"></div>
            <span class="text-sm text-gray-700">Costi</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Financial Insights -->
    <div v-if="financialInsights.length > 0" class="mb-6">
      <h4 class="text-lg font-semibold text-gray-900 mb-3">💡 Analisi Finanziaria</h4>
      <div class="space-y-3">
        <div
          v-for="insight in financialInsights"
          :key="insight.id"
          class="flex items-start gap-3 p-3 rounded-lg border"
          :class="{
            'bg-red-50 border-red-200': insight.type === 'danger',
            'bg-yellow-50 border-yellow-200': insight.type === 'warning',
            'bg-green-50 border-green-200': insight.type === 'success'
          }"
        >
          <span class="text-lg">{{ insight.icon }}</span>
          <div class="flex-1">
            <span 
              class="text-sm"
              :class="{
                'text-red-800': insight.type === 'danger',
                'text-yellow-800': insight.type === 'warning',
                'text-green-800': insight.type === 'success'
              }"
            >
              {{ insight.text }}
            </span>
            <button
              v-if="insight.action"
              @click="handleInsightAction(insight)"
              class="ml-2 text-sm font-medium underline hover:no-underline transition-all duration-200"
              :class="{
                'text-red-600 hover:text-red-700': insight.type === 'danger',
                'text-yellow-600 hover:text-yellow-700': insight.type === 'warning',
                'text-green-600 hover:text-green-700': insight.type === 'success'
              }"
            >
              {{ insight.actionText }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Financial Actions -->
    <div>
      <h4 class="text-lg font-semibold text-gray-900 mb-4">Azioni Rapide</h4>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <button
          @click="expandTeam"
          :disabled="!canHire"
          class="flex items-center gap-3 p-3 border rounded-lg transition-all duration-200"
          :class="{
            'border-gray-200 hover:border-gray-300 hover:bg-gray-50': canHire,
            'border-gray-200 bg-gray-50 cursor-not-allowed opacity-50': !canHire
          }"
        >
          <span class="text-xl">👥</span>
          <div class="text-left">
            <div class="font-medium text-gray-900">Espandi Team</div>
            <div class="text-sm text-gray-600">{{ expandTeamDescription }}</div>
          </div>
        </button>

        <button
          @click="optimizeCosts"
          :disabled="totalTeamSize <= 2"
          class="flex items-center gap-3 p-3 border rounded-lg transition-all duration-200"
          :class="{
            'border-gray-200 hover:border-gray-300 hover:bg-gray-50': totalTeamSize > 2,
            'border-gray-200 bg-gray-50 cursor-not-allowed opacity-50': totalTeamSize <= 2
          }"
        >
          <span class="text-xl">⚡</span>
          <div class="text-left">
            <div class="font-medium text-gray-900">Ottimizza Costi</div>
            <div class="text-sm text-gray-600">{{ optimizeCostsDescription }}</div>
          </div>
        </button>

        <button
          @click="viewDetailedReport"
          class="flex items-center gap-3 p-3 border border-gray-200 hover:border-gray-300 hover:bg-gray-50 rounded-lg transition-all duration-200"
        >
          <span class="text-xl">📊</span>
          <div class="text-left">
            <div class="font-medium text-gray-900">Report Dettagliato</div>
            <div class="text-sm text-gray-600">Analisi completa</div>
          </div>
        </button>
      </div>
    </div>
  </div>
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

const balanceTrendIcon = computed(() => {
  const change = currentBalance.value - previousBalance.value
  if (change > 0) return '📈'
  if (change < 0) return '📉'
  return '➡️'
})

const balanceChangeText = computed(() => {
  const change = currentBalance.value - previousBalance.value
  if (change === 0) return 'Nessuna variazione'
  const sign = change > 0 ? '+' : ''
  return `${sign}${formatCurrency(change)} dall'ultimo aggiornamento`
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