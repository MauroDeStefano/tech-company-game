<!-- src/components/game/dashboard/TeamStatusCard.vue -->
<template>
  <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
    <!-- Header -->
    <div class="mb-6">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
          <span class="text-2xl">👥</span>
          <h3 class="text-lg font-bold text-gray-900">Team Status</h3>
          <div v-if="loading" class="ml-2">
            <svg class="animate-spin w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
          </div>
        </div>
        <button
          @click="goToHR"
          class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 rounded-lg transition-colors duration-200"
        >
          <span>🧑‍💼</span>
          Assumi
        </button>
      </div>
    </div>

    <!-- Team Overview -->
    <div class="grid grid-cols-3 gap-4 mb-6">
      <div class="text-center p-3 bg-gray-50 rounded-lg">
        <div class="text-2xl font-bold text-gray-900">{{ totalTeamSize }}</div>
        <div class="text-sm text-gray-600">Membri Totali</div>
      </div>

      <div class="text-center p-3 bg-gray-50 rounded-lg">
        <div class="text-2xl font-bold text-gray-900">{{ availableMembers }}</div>
        <div class="text-sm text-gray-600">Disponibili</div>
      </div>

      <div class="text-center p-3 bg-gray-50 rounded-lg">
        <div class="text-lg font-bold text-gray-900">{{ formatCurrency(monthlyTeamCosts) }}</div>
        <div class="text-sm text-gray-600">Costi/Mese</div>
      </div>
    </div>

    <!-- Developers Section -->
    <div class="mb-6">
      <div class="flex items-center justify-between mb-3">
        <h4 class="flex items-center gap-2 font-semibold text-gray-900">
          <span class="text-lg">👨‍💻</span>
          Sviluppatori
          <span class="text-sm bg-gray-100 text-gray-700 px-2 py-1 rounded-full">({{ developers.length }})</span>
        </h4>
        <button
          @click="goToProduction"
          class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 rounded-lg transition-colors duration-200"
        >
          <span>🏗️</span>
          Gestisci
        </button>
      </div>

      <div v-if="developers.length === 0" class="flex items-center justify-center gap-2 py-4 text-gray-500">
        <span class="text-2xl">💻</span>
        <span class="text-sm">Nessuno sviluppatore nel team</span>
      </div>

      <div v-else class="space-y-3">
        <div
          v-for="developer in developers.slice(0, 3)"
          :key="developer.id"
          class="flex items-center justify-between p-3 rounded-lg border"
          :class="{
            'border-red-200 bg-red-50': developer.is_busy,
            'border-green-200 bg-green-50': !developer.is_busy
          }"
        >
          <div class="flex-1">
            <div class="font-medium text-gray-900">{{ developer.name }}</div>
            <div class="flex items-center gap-2 mt-1">
              <span class="text-sm text-gray-600">{{ getSeniorityText(developer.seniority) }}</span>
              <span 
                class="text-xs px-2 py-1 rounded-full"
                :class="{
                  'bg-red-100 text-red-700': developer.is_busy,
                  'bg-green-100 text-green-700': !developer.is_busy
                }"
              >
                {{ developer.is_busy ? 'Occupato' : 'Disponibile' }}
              </span>
            </div>
          </div>

          <div class="text-right">
            <div class="text-sm text-gray-600">{{ developer.projects_completed || 0 }} progetti</div>
            <div class="text-sm font-medium text-gray-900">{{ formatCurrency(developer.monthly_salary || 2000) }}/mese</div>
          </div>
        </div>

        <div v-if="developers.length > 3" class="text-center text-sm text-gray-600">
          +{{ developers.length - 3 }} altri sviluppatori
        </div>
      </div>
    </div>

    <!-- Sales People Section -->
    <div class="mb-6">
      <div class="flex items-center justify-between mb-3">
        <h4 class="flex items-center gap-2 font-semibold text-gray-900">
          <span class="text-lg">💼</span>
          Commerciali
          <span class="text-sm bg-gray-100 text-gray-700 px-2 py-1 rounded-full">({{ salesPeople.length }})</span>
        </h4>
        <button
          @click="goToSales"
          class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 rounded-lg transition-colors duration-200"
        >
          <span>💼</span>
          Gestisci
        </button>
      </div>

      <div v-if="salesPeople.length === 0" class="flex items-center justify-center gap-2 py-4 text-gray-500">
        <span class="text-2xl">💼</span>
        <span class="text-sm">Nessun commerciale nel team</span>
      </div>

      <div v-else class="space-y-3">
        <div
          v-for="salesPerson in salesPeople.slice(0, 3)"
          :key="salesPerson.id"
          class="flex items-center justify-between p-3 rounded-lg border"
          :class="{
            'border-red-200 bg-red-50': salesPerson.is_busy,
            'border-green-200 bg-green-50': !salesPerson.is_busy
          }"
        >
          <div class="flex-1">
            <div class="font-medium text-gray-900">{{ salesPerson.name }}</div>
            <div class="flex items-center gap-2 mt-1">
              <span class="text-sm text-gray-600">{{ getExperienceText(salesPerson.experience) }}</span>
              <span 
                class="text-xs px-2 py-1 rounded-full"
                :class="{
                  'bg-red-100 text-red-700': salesPerson.is_busy,
                  'bg-green-100 text-green-700': !salesPerson.is_busy
                }"
              >
                {{ salesPerson.is_busy ? 'Occupato' : 'Disponibile' }}
              </span>
            </div>
          </div>

          <div class="text-right">
            <div class="text-sm text-gray-600">{{ salesPerson.projects_generated || 0 }} generati</div>
            <div class="text-sm font-medium text-gray-900">{{ formatCurrency(salesPerson.monthly_salary || 1500) }}/mese</div>
          </div>
        </div>

        <div v-if="salesPeople.length > 3" class="text-center text-sm text-gray-600">
          +{{ salesPeople.length - 3 }} altri commerciali
        </div>
      </div>
    </div>

    <!-- Team Efficiency -->
    <div class="mb-6">
      <h4 class="text-lg font-semibold text-gray-900 mb-4">⚡ Efficienza Team</h4>
      <div class="space-y-4">
        <div>
          <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-gray-700">Utilizzo</span>
            <span class="text-sm font-semibold text-gray-900">{{ teamUtilization }}%</span>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-2">
            <div
              class="h-2 rounded-full transition-all duration-300"
              :style="{ width: `${teamUtilization}%` }"
              :class="{
                'bg-green-500': teamUtilization >= 80,
                'bg-blue-500': teamUtilization >= 50 && teamUtilization < 80,
                'bg-yellow-500': teamUtilization >= 25 && teamUtilization < 50,
                'bg-red-500': teamUtilization < 25
              }"
            ></div>
          </div>
        </div>

        <div>
          <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-gray-700">Produttività</span>
            <span class="text-sm font-semibold text-gray-900">{{ teamProductivity }}%</span>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-2">
            <div
              class="h-2 rounded-full transition-all duration-300"
              :style="{ width: `${teamProductivity}%` }"
              :class="{
                'bg-emerald-500': teamProductivity >= 80,
                'bg-green-500': teamProductivity >= 60 && teamProductivity < 80,
                'bg-yellow-500': teamProductivity >= 40 && teamProductivity < 60,
                'bg-red-500': teamProductivity < 40
              }"
            ></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Insights -->
    <div v-if="teamInsights.length > 0">
      <h4 class="text-lg font-semibold text-gray-900 mb-3">💡 Suggerimenti</h4>
      <div class="space-y-2">
        <div
          v-for="insight in teamInsights"
          :key="insight.id"
          class="flex items-center gap-3 p-3 rounded-lg border"
          :class="{
            'bg-red-50 border-red-200': insight.type === 'danger',
            'bg-yellow-50 border-yellow-200': insight.type === 'warning',
            'bg-blue-50 border-blue-200': insight.type === 'info',
            'bg-green-50 border-green-200': insight.type === 'success'
          }"
        >
          <span class="text-lg">{{ insight.icon }}</span>
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
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useGameStore } from '@/js/stores/game'
import { formatCurrency } from '@/js/utils/helpers'

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

const goToProduction = () => {
  router.push('/game/production')
}

const goToSales = () => {
  router.push('/game/sales')
}

const goToHR = () => {
  router.push('/game/hr')
}
</script>