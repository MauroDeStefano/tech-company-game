<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-white">
    <!-- Page Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
          <div>
            <h1 class="text-4xl font-bold text-gray-900 tracking-tight mb-2">
              <span class="text-2xl">🧑‍💼</span>
              Risorse Umane
            </h1>
            <p class="text-xl text-gray-600">
              Assumi nuovo personale per far crescere la tua software house
            </p>
          </div>

          <div class="grid grid-cols-3 gap-4">
            <div class="bg-gray-50 rounded-lg p-3 text-center">
              <span class="text-2xl block mb-1">💰</span>
              <div
                class="text-lg font-bold"
                :class="moneyColorClass"
              >
                {{ formatCurrency(currentMoney) }}
              </div>
              <div class="text-xs text-gray-600">Budget Disponibile</div>
            </div>

            <div class="bg-gray-50 rounded-lg p-3 text-center">
              <span class="text-2xl block mb-1">👥</span>
              <div class="text-lg font-bold text-gray-900">{{ currentTeamSize }}</div>
              <div class="text-xs text-gray-600">Team Attuale</div>
            </div>

            <div class="bg-gray-50 rounded-lg p-3 text-center">
              <span class="text-2xl block mb-1">📊</span>
              <div class="text-lg font-bold text-gray-900">{{ formatCurrency(monthlyCosts) }}</div>
              <div class="text-xs text-gray-600">Costi Mensili</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
      <!-- Current Team Overview -->
      <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
        <div class="flex items-center gap-2 mb-6">
          <span class="text-2xl">👥</span>
          <h3 class="text-lg font-bold text-gray-900">Il Tuo Team Attuale</h3>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Developers -->
          <div>
            <h3 class="flex items-center gap-2 font-semibold text-gray-900 mb-4">
              <span class="text-lg">👨‍💻</span>
              Sviluppatori ({{ currentDevelopers.length }})
            </h3>

            <div v-if="currentDevelopers.length === 0" class="text-center py-8 text-gray-500">
              <p>Nessuno sviluppatore nel team</p>
            </div>

            <div v-else class="space-y-3">
              <div
                v-for="developer in currentDevelopers"
                :key="developer.id"
                class="flex items-center justify-between p-3 border border-gray-200 rounded-lg"
              >
                <div class="flex-1">
                  <div class="font-medium text-gray-900">{{ developer.name }}</div>
                  <div class="flex items-center gap-2 mt-1">
                    <span class="text-sm text-gray-600">{{ getSeniorityText(developer.seniority?.level || developer.seniority) }}</span>
                    <span class="text-sm text-gray-600">{{ formatCurrency(developer.salary?.monthly || developer.monthly_salary) }}/mese</span>
                  </div>
                </div>
                <span
                  class="text-xs px-2 py-1 rounded-full"
                  :class="{
                    'bg-red-100 text-red-700': developer.status?.is_busy || developer.is_busy,
                    'bg-green-100 text-green-700': !(developer.status?.is_busy || developer.is_busy)
                  }"
                >
                  {{ (developer.status?.is_busy || developer.is_busy) ? 'Occupato' : 'Disponibile' }}
                </span>
              </div>
            </div>
          </div>

          <!-- Sales People -->
          <div>
            <h3 class="flex items-center gap-2 font-semibold text-gray-900 mb-4">
              <span class="text-lg">💼</span>
              Commerciali ({{ currentSalesPeople.length }})
            </h3>

            <div v-if="currentSalesPeople.length === 0" class="text-center py-8 text-gray-500">
              <p>Nessun commerciale nel team</p>
            </div>

            <div v-else class="space-y-3">
              <div
                v-for="salesPerson in currentSalesPeople"
                :key="salesPerson.id"
                class="flex items-center justify-between p-3 border border-gray-200 rounded-lg"
              >
                <div class="flex-1">
                  <div class="font-medium text-gray-900">{{ salesPerson.name }}</div>
                  <div class="flex items-center gap-2 mt-1">
                    <span class="text-sm text-gray-600">{{ getExperienceText(salesPerson.experience?.level || salesPerson.experience) }}</span>
                    <span class="text-sm text-gray-600">{{ formatCurrency(salesPerson.salary?.monthly || salesPerson.monthly_salary) }}/mese</span>
                  </div>
                </div>
                <span
                  class="text-xs px-2 py-1 rounded-full"
                  :class="{
                    'bg-red-100 text-red-700': salesPerson.status?.is_busy || salesPerson.is_busy,
                    'bg-green-100 text-green-700': !(salesPerson.status?.is_busy || salesPerson.is_busy)
                  }"
                >
                  {{ (salesPerson.status?.is_busy || salesPerson.is_busy) ? 'Occupato' : 'Disponibile' }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Developers Market -->
      <section>
        <div class="flex items-center justify-between mb-6">
          <h2 class="flex items-center gap-2 text-2xl font-bold text-gray-900">
            <span>👨‍💻</span>
            Sviluppatori Disponibili
          </h2>
          <select
            v-model="developerFilter"
            class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="all">Tutti i livelli</option>
            <option value="1">Junior</option>
            <option value="2">Junior-Mid</option>
            <option value="3">Mid</option>
            <option value="4">Senior</option>
            <option value="5">Lead</option>
          </select>
        </div>

        <div v-if="loadingDevelopers" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          <div v-for="i in 4" :key="i" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 animate-pulse">
            <div class="h-4 bg-gray-200 rounded mb-4"></div>
            <div class="space-y-3">
              <div class="h-3 bg-gray-200 rounded"></div>
              <div class="h-3 bg-gray-200 rounded w-2/3"></div>
            </div>
          </div>
        </div>

        <div v-else-if="filteredDevelopers.length === 0" class="text-center py-12">
          <div class="text-6xl mb-4">😔</div>
          <p class="text-gray-600">Nessuno sviluppatore disponibile</p>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          <div
            v-for="developer in filteredDevelopers"
            :key="developer.id || developer.name"
            class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200"
            :class="{ 'opacity-50': !canAfford(getHireCost(developer)) }"
          >
            <div class="mb-4">
              <h3 class="font-semibold text-gray-900 mb-2">{{ developer.name }}</h3>
              <div class="flex items-center gap-2">
                <span class="text-sm">{{ '⭐'.repeat(developer.seniority?.level || developer.seniority) }}</span>
                <span class="text-sm text-gray-600">{{ getSeniorityText(developer.seniority?.level || developer.seniority) }}</span>
              </div>
            </div>

            <div class="space-y-3 mb-4">
              <div v-if="developer.specialization" class="flex items-center gap-2">
                <span>🎯</span>
                <span class="text-sm text-gray-600">{{ getSpecializationText(developer.specialization?.name || developer.specialization) }}</span>
              </div>

              <div class="space-y-2">
                <div class="flex justify-between">
                  <span class="text-sm text-gray-600">Costo Assunzione</span>
                  <span
                    class="text-sm font-medium"
                    :class="{ 'text-red-600': !canAfford(getHireCost(developer)), 'text-gray-900': canAfford(getHireCost(developer)) }"
                  >
                    {{ formatCurrency(getHireCost(developer)) }}
                  </span>
                </div>
                <div class="flex justify-between">
                  <span class="text-sm text-gray-600">Stipendio Mensile</span>
                  <span class="text-sm font-medium text-gray-900">{{ formatCurrency(developer.salary?.monthly || developer.monthly_salary) }}</span>
                </div>
              </div>

              <div v-if="developer.skills && developer.skills.length > 0">
                <div class="text-sm text-gray-600 mb-1">Competenze:</div>
                <div class="flex flex-wrap gap-1">
                  <span
                    v-for="skill in developer.skills.slice(0, 3)"
                    :key="skill"
                    class="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded"
                  >
                    {{ skill }}
                  </span>
                  <span v-if="developer.skills.length > 3" class="text-xs text-gray-500">
                    +{{ developer.skills.length - 3 }}
                  </span>
                </div>
              </div>
            </div>

            <button
              v-if="canAfford(getHireCost(developer))"
              @click="hireDeveloper(developer)"
              :disabled="hiringId === `dev-${developer.id || developer.name}`"
              class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-medium rounded-lg transition-colors duration-200"
            >
              <span v-if="hiringId === `dev-${developer.id || developer.name}`">
                Assunzione...
              </span>
              <span v-else>Assumi</span>
            </button>
            <button
              v-else
              disabled
              class="w-full px-4 py-2 bg-gray-300 text-gray-500 font-medium rounded-lg cursor-not-allowed"
            >
              Budget Insufficiente
            </button>
          </div>
        </div>
      </section>

      <!-- Sales People Market -->
      <section>
        <div class="flex items-center justify-between mb-6">
          <h2 class="flex items-center gap-2 text-2xl font-bold text-gray-900">
            <span>💼</span>
            Commerciali Disponibili
          </h2>
          <select
            v-model="salesFilter"
            class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="all">Tutti i livelli</option>
            <option value="1">Trainee</option>
            <option value="2">Junior</option>
            <option value="3">Mid</option>
            <option value="4">Senior</option>
            <option value="5">Manager</option>
          </select>
        </div>

        <div v-if="loadingSalesPeople" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          <div v-for="i in 4" :key="i" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 animate-pulse">
            <div class="h-4 bg-gray-200 rounded mb-4"></div>
            <div class="space-y-3">
              <div class="h-3 bg-gray-200 rounded"></div>
              <div class="h-3 bg-gray-200 rounded w-2/3"></div>
            </div>
          </div>
        </div>

        <div v-else-if="filteredSalesPeople.length === 0" class="text-center py-12">
          <div class="text-6xl mb-4">😔</div>
          <p class="text-gray-600">Nessun commerciale disponibile</p>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          <div
            v-for="salesPerson in filteredSalesPeople"
            :key="salesPerson.id || salesPerson.name"
            class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200"
            :class="{ 'opacity-50': !canAfford(getHireCost(salesPerson)) }"
          >
            <div class="mb-4">
              <h3 class="font-semibold text-gray-900 mb-2">{{ salesPerson.name }}</h3>
              <div class="flex items-center gap-2">
                <span class="text-sm">{{ '⭐'.repeat(salesPerson.experience?.level || salesPerson.experience) }}</span>
                <span class="text-sm text-gray-600">{{ getExperienceText(salesPerson.experience?.level || salesPerson.experience) }}</span>
              </div>
            </div>

            <div class="space-y-3 mb-4">
              <div v-if="salesPerson.specialization" class="flex items-center gap-2">
                <span>🎯</span>
                <span class="text-sm text-gray-600">{{ getSalesSpecializationText(salesPerson.specialization?.name || salesPerson.specialization) }}</span>
              </div>

              <div class="space-y-2">
                <div class="flex justify-between">
                  <span class="text-sm text-gray-600">Costo Assunzione</span>
                  <span
                    class="text-sm font-medium"
                    :class="{ 'text-red-600': !canAfford(getHireCost(salesPerson)), 'text-gray-900': canAfford(getHireCost(salesPerson)) }"
                  >
                    {{ formatCurrency(getHireCost(salesPerson)) }}
                  </span>
                </div>
                <div class="flex justify-between">
                  <span class="text-sm text-gray-600">Stipendio Mensile</span>
                  <span class="text-sm font-medium text-gray-900">{{ formatCurrency(salesPerson.salary?.monthly || salesPerson.monthly_salary) }}</span>
                </div>
              </div>

              <div class="space-y-2">
                <div class="flex justify-between">
                  <span class="text-sm text-gray-600">Tempo Generazione</span>
                  <span class="text-sm font-medium text-gray-900">{{ salesPerson.generation_capabilities?.estimated_generation_time || salesPerson.estimated_generation_time }}min</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-sm text-gray-600">Valore Progetti</span>
                  <span class="text-sm font-medium text-gray-900">{{ formatCurrency(salesPerson.generation_capabilities?.estimated_project_value || salesPerson.estimated_project_value) }}</span>
                </div>
              </div>
            </div>

            <button
              v-if="canAfford(getHireCost(salesPerson))"
              @click="hireSalesPerson(salesPerson)"
              :disabled="hiringId === `sales-${salesPerson.id || salesPerson.name}`"
              class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-medium rounded-lg transition-colors duration-200"
            >
              <span v-if="hiringId === `sales-${salesPerson.id || salesPerson.name}`">
                Assunzione...
              </span>
              <span v-else>Assumi</span>
            </button>
            <button
              v-else
              disabled
              class="w-full px-4 py-2 bg-gray-300 text-gray-500 font-medium rounded-lg cursor-not-allowed"
            >
              Budget Insufficiente
            </button>
          </div>
        </div>
      </section>

      <!-- Budget Warning -->
      <div v-if="isLowBudget" class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
        <div class="flex items-center gap-3">
          <span class="text-2xl">⚠️</span>
          <div>
            <div class="font-semibold text-yellow-800">Budget limitato!</div>
            <div class="text-yellow-700 text-sm">
              Completa progetti per aumentare il patrimonio e assumere nuovo personale.
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useAuthStore } from '@/js/stores/auth'
import { useGameStore } from '@/js/stores/game'
import { useNotificationStore } from '@/js/stores/notifications'
import { formatCurrency } from '@/js/utils/helpers'

// 🎯 STORES
const gameStore = useGameStore()
const notificationStore = useNotificationStore()
const authStore = useAuthStore()

// 🎯 REACTIVE STATE - Queste erano le variabili mancanti!
const loadingDevelopers = ref(false)
const loadingSalesPeople = ref(false)
const marketDevelopers = ref([])
const marketSalesPeople = ref([])
const developerFilter = ref('all')
const salesFilter = ref('all')
const hiringId = ref(null)

// 🎯 COMPUTED PROPERTIES - Anche questi mancavano!
const currentMoney = computed(() => gameStore.currentGameMoney || 0)

const currentTeamSize = computed(() => gameStore.currentGameTeamSize || 0)

const monthlyCosts = computed(() => {
  // Calcola i costi mensili basandosi sui dati del gioco
  const devCosts = currentDevelopers.value.reduce((sum, dev) => {
    return sum + (dev.salary?.monthly || dev.monthly_salary || 0)
  }, 0)

  const salesCosts = currentSalesPeople.value.reduce((sum, sales) => {
    return sum + (sales.salary?.monthly || sales.monthly_salary || 0)
  }, 0)

  return devCosts + salesCosts + 1500 // 1500 costi fissi
})

const moneyColorClass = computed(() => {
  const money = currentMoney.value
  if (money < 0) return 'text-red-600'
  if (money < 1000) return 'text-yellow-600'
  return 'text-green-600'
})

const currentDevelopers = computed(() => {
  return gameStore.developers?.data || gameStore.currentGame?.developers || []
})

const currentSalesPeople = computed(() => {
  return gameStore.salesPeople?.data || gameStore.currentGame?.sales_people || []
})

const isLowBudget = computed(() => {
  return currentMoney.value < 3000 // Budget limitato sotto 3000€
})

const filteredDevelopers = computed(() => {
  let developers = marketDevelopers.value || []

  if (developerFilter.value === 'all') return developers

  return developers.filter(dev => {
    const seniority = dev.seniority?.level || dev.seniority
    return seniority.toString() === developerFilter.value
  })
})

const filteredSalesPeople = computed(() => {
  let salesPeople = marketSalesPeople.value || []

  if (salesFilter.value === 'all') return salesPeople

  return salesPeople.filter(sales => {
    const experience = sales.experience?.level || sales.experience
    return experience.toString() === salesFilter.value
  })
})

// 🎯 HELPER METHODS - Anche questi mancavano!
const getSeniorityText = (level) => {
  const map = {
    1: 'Junior',
    2: 'Junior-Mid',
    3: 'Mid',
    4: 'Senior',
    5: 'Lead'
  }
  return map[level] || 'Unknown'
}

const getExperienceText = (level) => {
  const map = {
    1: 'Trainee',
    2: 'Junior',
    3: 'Mid',
    4: 'Senior',
    5: 'Manager'
  }
  return map[level] || 'Unknown'
}

const getSpecializationText = (spec) => {
  const map = {
    'frontend': 'Frontend',
    'backend': 'Backend',
    'fullstack': 'Full Stack',
    'mobile': 'Mobile',
    'devops': 'DevOps'
  }
  return map[spec] || 'Generica'
}

const getSalesSpecializationText = (spec) => {
  const map = {
    'startup': 'Startup',
    'sme': 'PMI',
    'enterprise': 'Enterprise',
    'ecommerce': 'E-commerce',
    'consulting': 'Consulting'
  }
  return map[spec] || 'Generica'
}

const getHireCost = (person) => {
  return person.hire_cost?.amount || person.monthly_salary || 0
}

const canAfford = (cost) => {
  const actualCost = typeof cost === 'object' ? cost.amount : cost
  return currentMoney.value >= actualCost
}

// 🎯 API CONFIGURATION
const API_BASE = '/api'
const getHeaders = () => ({
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'Authorization': `Bearer ${authStore.token}`
})

// 🎯 API METHODS
const loadMarketDevelopers = async () => {
  try {
    loadingDevelopers.value = true

    const response = await fetch(`${API_BASE}/games/${gameStore.currentGameId}/market/developers`, {
      method: 'GET',
      headers: getHeaders(),
    })

    if (!response.ok) {
      const errorData = await response.json()
      throw new Error(errorData.message || `HTTP Error: ${response.status}`)
    }

    const result = await response.json()

    if (result.success) {
      marketDevelopers.value = result.data
      console.log('✅ Market developers loaded:', result.meta)
    } else {
      throw new Error(result.message || 'Errore sconosciuto')
    }

  } catch (error) {
    console.error('❌ Error loading market developers:', error)
    notificationStore.error(`Errore nel caricamento sviluppatori: ${error.message}`)
    marketDevelopers.value = []
  } finally {
    loadingDevelopers.value = false
  }
}

const loadMarketSalesPeople = async () => {
  try {
    loadingSalesPeople.value = true

    const response = await fetch(`${API_BASE}/games/${gameStore.currentGameId}/market/sales-people`, {
      method: 'GET',
      headers: getHeaders(),
    })

    if (!response.ok) {
      const errorData = await response.json()
      throw new Error(errorData.message || `HTTP Error: ${response.status}`)
    }

    const result = await response.json()

    if (result.success) {
      marketSalesPeople.value = result.data
      console.log('✅ Market sales people loaded:', result.meta)
    } else {
      throw new Error(result.message || 'Errore sconosciuto')
    }

  } catch (error) {
    console.error('❌ Error loading market sales people:', error)
    notificationStore.error(`Errore nel caricamento commerciali: ${error.message}`)
    marketSalesPeople.value = []
  } finally {
    loadingSalesPeople.value = false
  }
}

const hireDeveloper = async (developer) => {
  if (!canAfford(getHireCost(developer))) {
    notificationStore.error('Budget insufficiente per questa assunzione')
    return
  }

  const hireId = `dev-${developer.id}`
  hiringId.value = hireId

  try {
    const response = await fetch(`${API_BASE}/games/${gameStore.currentGameId}/hire/developer/${developer.id}`, {
      method: 'POST',
      headers: getHeaders(),
      body: JSON.stringify({
        developer_id: developer.id,
        hire_cost: getHireCost(developer)
      })
    })

    if (!response.ok) {
      const errorData = await response.json()
      throw new Error(errorData.message || `Errore HTTP: ${response.status}`)
    }

    const result = await response.json()

    if (result.success) {
      notificationStore.success(result.message)
      marketDevelopers.value = marketDevelopers.value.filter(d => d.id !== developer.id)
      await gameStore.loadGame(gameStore.currentGameId) // Ricarica il gioco
      console.log('✅ Developer hired successfully:', result.data)
    } else {
      throw new Error(result.message || 'Errore durante l\'assunzione')
    }

  } catch (error) {
    console.error('❌ Hiring error:', error)
    if (error.message.includes('Budget insufficiente')) {
      notificationStore.error('Budget insufficiente per questa assunzione')
    } else if (error.message.includes('401')) {
      notificationStore.error('Sessione scaduta. Effettua di nuovo il login.')
    } else {
      notificationStore.error(`Errore durante l'assunzione: ${error.message}`)
    }
  } finally {
    hiringId.value = null
  }
}

const hireSalesPerson = async (salesPerson) => {
  if (!canAfford(getHireCost(salesPerson))) {
    notificationStore.error('Budget insufficiente per questa assunzione')
    return
  }

  const hireId = `sales-${salesPerson.id}`
  hiringId.value = hireId

  try {
    const response = await fetch(`${API_BASE}/games/${gameStore.currentGameId}/hire/sales-person/${salesPerson.id}`, {
      method: 'POST',
      headers: getHeaders(),
      body: JSON.stringify({
        sales_person_id: salesPerson.id,
        hire_cost: getHireCost(salesPerson)
      })
    })

    if (!response.ok) {
      const errorData = await response.json()
      throw new Error(errorData.message || `Errore HTTP: ${response.status}`)
    }

    const result = await response.json()

    if (result.success) {
      notificationStore.success(result.message)
      marketSalesPeople.value = marketSalesPeople.value.filter(s => s.id !== salesPerson.id)
      await gameStore.loadGame(gameStore.currentGameId) // Ricarica il gioco
      console.log('✅ Sales person hired successfully:', result.data)
    } else {
      throw new Error(result.message || 'Errore durante l\'assunzione')
    }

  } catch (error) {
    console.error('❌ Hiring sales person error:', error)
    if (error.message.includes('Budget insufficiente')) {
      notificationStore.error('Budget insufficiente per questa assunzione')
    } else if (error.message.includes('401')) {
      notificationStore.error('Sessione scaduta. Effettua di nuovo il login.')
    } else {
      notificationStore.error(`Errore durante l'assunzione: ${error.message}`)
    }
  } finally {
    hiringId.value = null
  }
}

// 🎯 LIFECYCLE HOOKS
onMounted(async () => {
  console.log('🔍 HR Component mounted - Debug info:')
  console.log('Current game ID:', gameStore.currentGameId)
  console.log('Current money:', currentMoney.value)
  console.log('API Base URL:', API_BASE)
  console.log('Auth token present:', !!authStore.token)

  // Carica i dati del mercato
  await Promise.all([
    loadMarketDevelopers(),
    loadMarketSalesPeople()
  ])

  console.log('Market data loaded')
})

// 🎯 WATCHERS
watch(() => gameStore.currentGameId, async (newGameId) => {
  if (newGameId) {
    console.log('Game changed, reloading market data for game:', newGameId)
    await Promise.all([
      loadMarketDevelopers(),
      loadMarketSalesPeople()
    ])
  }
})

// 🎯 ERROR HANDLING
window.addEventListener('unhandledrejection', (event) => {
  console.error('❌ Unhandled promise rejection in HR component:', event.reason)
  notificationStore.error('Si è verificato un errore imprevisto.')
  event.preventDefault()
})
</script>