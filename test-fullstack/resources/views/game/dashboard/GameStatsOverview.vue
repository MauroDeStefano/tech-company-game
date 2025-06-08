<template>
  <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
    <!-- Header -->
    <div class="mb-6">
      <div class="flex items-center gap-2">
        <span class="text-2xl">📊</span>
        <h3 class="text-lg font-bold text-gray-900">Statistiche Principali</h3>
        <div v-if="loading" class="ml-2">
          <svg class="animate-spin w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        </div>
      </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <!-- Patrimonio -->
      <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-4">
        <div class="flex items-center gap-2 mb-2">
          <span class="text-xl">💰</span>
          <span class="text-sm font-medium text-gray-700">Patrimonio</span>
        </div>
        <div 
          class="text-2xl font-bold mb-1"
          :class="{
            'text-red-600': gameStats.money < 0,
            'text-yellow-600': gameStats.money >= 0 && gameStats.money < 1000,
            'text-green-600': gameStats.money >= 1000
          }"
        >
          {{ formatCurrency(gameStats.money) }}
        </div>
        <div 
          class="flex items-center gap-1 text-sm"
          :class="{
            'text-green-600': gameStats.money - previousMoney > 0,
            'text-red-600': gameStats.money - previousMoney < 0,
            'text-gray-600': gameStats.money - previousMoney === 0
          }"
        >
          <span>{{ moneyTrendIcon }}</span>
          <span>{{ moneyChangeText }}</span>
        </div>
      </div>

      <!-- Progetti Completati -->
      <div class="bg-gradient-to-br from-green-50 to-emerald-50 border border-green-200 rounded-xl p-4">
        <div class="flex items-center gap-2 mb-2">
          <span class="text-xl">✅</span>
          <span class="text-sm font-medium text-gray-700">Progetti Completati</span>
        </div>
        <div class="text-2xl font-bold text-gray-900 mb-1">
          {{ gameStats.projectsCompleted }}
        </div>
        <div class="text-sm text-gray-600">
          {{ projectsCompletedText }}
        </div>
      </div>

      <!-- Revenue Totale -->
      <div class="bg-gradient-to-br from-purple-50 to-pink-50 border border-purple-200 rounded-xl p-4">
        <div class="flex items-center gap-2 mb-2">
          <span class="text-xl">💵</span>
          <span class="text-sm font-medium text-gray-700">Revenue Totale</span>
        </div>
        <div class="text-2xl font-bold text-gray-900 mb-1">
          {{ formatCurrency(gameStats.totalRevenue) }}
        </div>
        <div class="text-sm text-gray-600">
          Da {{ gameStats.projectsCompleted }} progetti
        </div>
      </div>

      <!-- Efficienza Team -->
      <div class="bg-gradient-to-br from-orange-50 to-red-50 border border-orange-200 rounded-xl p-4">
        <div class="flex items-center gap-2 mb-2">
          <span class="text-xl">⚡</span>
          <span class="text-sm font-medium text-gray-700">Efficienza</span>
        </div>
        <div class="text-2xl font-bold text-gray-900 mb-1">
          {{ efficiencyPercentage }}%
        </div>
        <div class="text-sm text-gray-600">
          {{ efficiencyDescription }}
        </div>
      </div>
    </div>

    <!-- Progress Bars -->
    <div class="mb-6">
      <h4 class="text-lg font-semibold text-gray-900 mb-4">Progressi Attuali</h4>

      <div class="space-y-4">
        <!-- Progetti in corso -->
        <div v-if="activeProjects.length > 0" class="bg-gray-50 rounded-lg p-4">
          <div class="flex items-center justify-between mb-3">
            <span class="font-medium text-gray-900">Progetti in Corso</span>
            <span class="text-sm font-semibold bg-blue-100 text-blue-800 px-2 py-1 rounded-full">
              {{ activeProjects.length }}
            </span>
          </div>
          <div class="space-y-3">
            <div
              v-for="project in activeProjects.slice(0, 3)"
              :key="project.id"
              class="space-y-2"
            >
              <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-700">{{ project.name }}</span>
                <span class="text-sm font-semibold text-gray-900">{{ Math.round(project.progress || 0) }}%</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div
                  class="bg-blue-500 h-2 rounded-full transition-all duration-300"
                  :style="{ width: `${project.progress || 0}%` }"
                ></div>
              </div>
            </div>

            <div v-if="activeProjects.length > 3" class="text-center pt-2">
              <span class="text-sm text-gray-600">
                +{{ activeProjects.length - 3 }} altri progetti
              </span>
            </div>
          </div>
        </div>

        <!-- Generazioni in corso -->
        <div v-if="activeGenerations.length > 0" class="bg-gray-50 rounded-lg p-4">
          <div class="flex items-center justify-between mb-3">
            <span class="font-medium text-gray-900">Generazioni in Corso</span>
            <span class="text-sm font-semibold bg-green-100 text-green-800 px-2 py-1 rounded-full">
              {{ activeGenerations.length }}
            </span>
          </div>
          <div class="space-y-3">
            <div
              v-for="generation in activeGenerations.slice(0, 2)"
              :key="generation.id"
              class="space-y-2"
            >
              <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-700">{{ generation.sales_person_name }}</span>
                <span class="text-sm font-semibold text-gray-900">{{ Math.round(generation.progress || 0) }}%</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div
                  class="bg-green-500 h-2 rounded-full transition-all duration-300"
                  :style="{ width: `${generation.progress || 0}%` }"
                ></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Insights -->
    <div v-if="insights.length > 0">
      <h4 class="text-lg font-semibold text-gray-900 mb-4">💡 Insights</h4>
      <div class="space-y-3">
        <div
          v-for="insight in insights"
          :key="insight.id"
          class="flex items-center gap-3 p-3 rounded-lg border"
          :class="{
            'bg-red-50 border-red-200': insight.type === 'danger',
            'bg-yellow-50 border-yellow-200': insight.type === 'warning',
            'bg-blue-50 border-blue-200': insight.type === 'info',
            'bg-green-50 border-green-200': insight.type === 'success'
          }"
        >
          <span class="text-lg flex-shrink-0">{{ insight.icon }}</span>
          <span 
            class="text-sm"
            :class="{
              'text-red-800': insight.type === 'danger',
              'text-yellow-800': insight.type === 'warning',
              'text-blue-800': insight.type === 'info',
              'text-green-800': insight.type === 'success'
            }"
          >
            {{ insight.text }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useGameStore } from '@/js/stores/game'
import { formatCurrency } from '@/js/utils/helpers'

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