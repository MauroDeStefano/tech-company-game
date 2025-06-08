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
            <!-- 🎯 CORREZIONE: Usa il getter dello store per il money -->
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
                    <!-- 🎯 CORREZIONE: Usa la struttura corretta per seniority e salary -->
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
                    <!-- 🎯 CORREZIONE: Usa la struttura corretta per experience e salary -->
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
            :class="{ 'opacity-50': !canAfford(developer.hiring_cost) }"
          >
            <div class="mb-4">
              <h3 class="font-semibold text-gray-900 mb-2">{{ developer.name }}</h3>
              <div class="flex items-center gap-2">
                <span class="text-sm">{{ '⭐'.repeat(developer.seniority) }}</span>
                <span class="text-sm text-gray-600">{{ getSeniorityText(developer.seniority) }}</span>
              </div>
            </div>

            <div class="space-y-3 mb-4">
              <div v-if="developer.specialization" class="flex items-center gap-2">
                <span>🎯</span>
                <span class="text-sm text-gray-600">{{ getSpecializationText(developer.specialization) }}</span>
              </div>

              <div class="space-y-2">
                <div class="flex justify-between">
                  <span class="text-sm text-gray-600">Costo Assunzione</span>
                  <span 
                    class="text-sm font-medium"
                    :class="{ 'text-red-600': !canAfford(developer.hiring_cost), 'text-gray-900': canAfford(developer.hiring_cost) }"
                  >
                    {{ formatCurrency(developer.hiring_cost) }}
                  </span>
                </div>
                <div class="flex justify-between">
                  <span class="text-sm text-gray-600">Stipendio Mensile</span>
                  <span class="text-sm font-medium text-gray-900">{{ formatCurrency(developer.monthly_salary) }}</span>
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
              v-if="canAfford(developer.hiring_cost)"
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
            :class="{ 'opacity-50': !canAfford(salesPerson.hiring_cost) }"
          >
            <div class="mb-4">
              <h3 class="font-semibold text-gray-900 mb-2">{{ salesPerson.name }}</h3>
              <div class="flex items-center gap-2">
                <span class="text-sm">{{ '⭐'.repeat(salesPerson.experience) }}</span>
                <span class="text-sm text-gray-600">{{ getExperienceText(salesPerson.experience) }}</span>
              </div>
            </div>

            <div class="space-y-3 mb-4">
              <div v-if="salesPerson.specialization" class="flex items-center gap-2">
                <span>🎯</span>
                <span class="text-sm text-gray-600">{{ getSalesSpecializationText(salesPerson.specialization) }}</span>
              </div>

              <div class="space-y-2">
                <div class="flex justify-between">
                  <span class="text-sm text-gray-600">Costo Assunzione</span>
                  <span 
                    class="text-sm font-medium"
                    :class="{ 'text-red-600': !canAfford(salesPerson.hiring_cost), 'text-gray-900': canAfford(salesPerson.hiring_cost) }"
                  >
                    {{ formatCurrency(salesPerson.hiring_cost) }}
                  </span>
                </div>
                <div class="flex justify-between">
                  <span class="text-sm text-gray-600">Stipendio Mensile</span>
                  <span class="text-sm font-medium text-gray-900">{{ formatCurrency(salesPerson.monthly_salary) }}</span>
                </div>
              </div>

              <div class="space-y-2">
                <div class="flex justify-between">
                  <span class="text-sm text-gray-600">Tempo Generazione</span>
                  <span class="text-sm font-medium text-gray-900">{{ salesPerson.estimated_generation_time }}min</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-sm text-gray-600">Valore Progetti</span>
                  <span class="text-sm font-medium text-gray-900">{{ formatCurrency(salesPerson.estimated_project_value) }}</span>
                </div>
              </div>
            </div>

            <button
              v-if="canAfford(salesPerson.hiring_cost)"
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
import { ref, computed, onMounted } from 'vue'
import { useGameStore } from '@/js/stores/game'
import { useNotificationStore } from '@/js/stores/notifications'
import { formatCurrency } from '@/js/utils/helpers'

// Stores
const gameStore = useGameStore()
const notificationStore = useNotificationStore()

// Reactive state
const loadingDevelopers = ref(true)
const loadingSalesPeople = ref(true)
const hiringId = ref(null)
const developerFilter = ref('all')
const salesFilter = ref('all')
const marketDevelopers = ref([])
const marketSalesPeople = ref([])

// 🎯 CORREZIONE: Computed properties che usano i getters dello store
const currentMoney = computed(() => {
  // Usa il getter dello store che gestisce la struttura corretta
  return gameStore.currentGameMoney || 0
})

const moneyColorClass = computed(() => {
  const money = currentMoney.value
  if (money < 0) return 'text-red-600'
  if (money < 2000) return 'text-yellow-600'
  return 'text-green-600'
})

const currentDevelopers = computed(() => {
  // Usa il getter developers che ritorna l'array
  return gameStore.developers.data || []
})

const currentSalesPeople = computed(() => {
  // Usa il getter salesPeople che ritorna l'array
  return gameStore.salesPeople.data || []
})

const currentTeamSize = computed(() => {
  return gameStore.currentGameTeamSize || 0
})

const monthlyCosts = computed(() => {
  // Usa il getter costs dal GameResource
  return gameStore.currentGame?.costs?.monthly || 0
})

const isLowBudget = computed(() => {
  return currentMoney.value < 3000
})

const filteredDevelopers = computed(() => {
  if (developerFilter.value === 'all') return marketDevelopers.value
  return marketDevelopers.value.filter(dev => dev.seniority.toString() === developerFilter.value)
})

const filteredSalesPeople = computed(() => {
  if (salesFilter.value === 'all') return marketSalesPeople.value
  return marketSalesPeople.value.filter(sales => sales.experience.toString() === salesFilter.value)
})

// Methods
const loadMarketDevelopers = async () => {
  try {
    loadingDevelopers.value = true
    // Simulated API call - replace with actual API
    await new Promise(resolve => setTimeout(resolve, 1000))

    // Mock data - replace with actual API call
    marketDevelopers.value = [
      {
        id: 1,
        name: 'Marco Rossi',
        seniority: 3,
        specialization: 'fullstack',
        hiring_cost: 3000,
        monthly_salary: 2500,
        skills: ['Vue.js', 'Laravel', 'Docker']
      },
      {
        id: 2,
        name: 'Anna Verdi',
        seniority: 4,
        specialization: 'frontend',
        hiring_cost: 4000,
        monthly_salary: 3200,
        skills: ['React', 'TypeScript', 'Design Systems']
      },
      {
        id: 3,
        name: 'Luca Bianchi',
        seniority: 2,
        specialization: 'backend',
        hiring_cost: 2000,
        monthly_salary: 1800,
        skills: ['PHP', 'MySQL', 'Redis']
      },
      {
        id: 4,
        name: 'Sofia Neri',
        seniority: 5,
        specialization: 'devops',
        hiring_cost: 5500,
        monthly_salary: 4000,
        skills: ['AWS', 'Kubernetes', 'CI/CD']
      }
    ]
  } catch (error) {
    notificationStore.error('Errore nel caricamento degli sviluppatori')
  } finally {
    loadingDevelopers.value = false
  }
}

const loadMarketSalesPeople = async () => {
  try {
    loadingSalesPeople.value = true
    // Simulated API call - replace with actual API
    await new Promise(resolve => setTimeout(resolve, 1200))

    // Mock data - replace with actual API call
    marketSalesPeople.value = [
      {
        id: 1,
        name: 'Roberto Ferrari',
        experience: 3,
        specialization: 'enterprise',
        hiring_cost: 2500,
        monthly_salary: 2000,
        estimated_generation_time: 45,
        estimated_project_value: 3000
      },
      {
        id: 2,
        name: 'Giulia Romano',
        experience: 4,
        specialization: 'startup',
        hiring_cost: 3500,
        monthly_salary: 2800,
        estimated_generation_time: 35,
        estimated_project_value: 4000
      },
      {
        id: 3,
        name: 'Matteo Conti',
        experience: 2,
        specialization: 'sme',
        hiring_cost: 1800,
        monthly_salary: 1500,
        estimated_generation_time: 50,
        estimated_project_value: 2000
      }
    ]
  } catch (error) {
    notificationStore.error('Errore nel caricamento dei commerciali')
  } finally {
    loadingSalesPeople.value = false
  }
}

const canAfford = (cost) => {
  return currentMoney.value >= cost
};

const hireDeveloper = async (developer) => {
  if (!canAfford(developer.hiring_cost)) {
    notificationStore.error('Budget insufficiente per questa assunzione')
    return
  }

  const hireId = `dev-${developer.id || developer.name}`
  hiringId.value = hireId

  try {
    // TODO: Replace with actual API call
    await new Promise(resolve => setTimeout(resolve, 1500))

    // Simulate successful hire
    notificationStore.success(`${developer.name} è stato assunto come sviluppatore!`)

    // Remove from market
    marketDevelopers.value = marketDevelopers.value.filter(d => d.id !== developer.id)

    // Update game money via store action (better approach)
    await gameStore.refreshCurrentGame()

  } catch (error) {
    notificationStore.error('Errore durante l\'assunzione dello sviluppatore')
  } finally {
    hiringId.value = null
  }
}

const hireSalesPerson = async (salesPerson) => {
  if (!canAfford(salesPerson.hiring_cost)) {
    notificationStore.error('Budget insufficiente per questa assunzione')
    return
  }

  const hireId = `sales-${salesPerson.id || salesPerson.name}`
  hiringId.value = hireId

  try {
    // TODO: Replace with actual API call
    await new Promise(resolve => setTimeout(resolve, 1500))

    // Simulate successful hire
    notificationStore.success(`${salesPerson.name} è stato assunto come commerciale!`)

    // Remove from market
    marketSalesPeople.value = marketSalesPeople.value.filter(s => s.id !== salesPerson.id)

    // Update game money via store action (better approach)
    await gameStore.refreshCurrentGame()

  } catch (error) {
    notificationStore.error('Errore durante l\'assunzione del commerciale')
  } finally {
    hiringId.value = null
  }
}

// Helper functions
const getSeniorityText = (seniority) => {
  const levels = {
    1: 'Junior',
    2: 'Junior-Mid',
    3: 'Mid',
    4: 'Senior',
    5: 'Lead'
  }
  return levels[seniority] || 'Unknown'
}

const getExperienceText = (experience) => {
  const levels = {
    1: 'Trainee',
    2: 'Junior',
    3: 'Mid',
    4: 'Senior',
    5: 'Manager'
  }
  return levels[experience] || 'Unknown'
}

const getSpecializationText = (specialization) => {
  const specs = {
    frontend: 'Frontend',
    backend: 'Backend',
    fullstack: 'Full Stack',
    mobile: 'Mobile',
    devops: 'DevOps'
  }
  return specs[specialization] || specialization
}

const getSalesSpecializationText = (specialization) => {
  const specs = {
    startup: 'Startup',
    sme: 'PMI',
    enterprise: 'Enterprise',
    ecommerce: 'E-commerce',
    consulting: 'Consulting'
  }
  return specs[specialization] || specialization
}

// 🎯 DEBUG: Aggiungi per verificare la struttura dati
onMounted(() => {
  console.log('🔍 HR Component mounted - Debug info:')
  console.log('Current game:', gameStore.currentGame)
  console.log('Current money:', currentMoney.value)
  console.log('Money structure:', gameStore.currentGame?.money)
  
  loadMarketDevelopers()
  loadMarketSalesPeople()
})
</script>