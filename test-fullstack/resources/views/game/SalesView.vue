<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-white">
    <!-- Page Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div class="flex-1">
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">
              <span class="text-2xl mr-2">💼</span>
              Sales
            </h1>
            <p class="text-lg text-gray-600 mt-1">
              Gestisci commerciali e acquisizione clienti
            </p>
          </div>

          <div class="flex flex-wrap gap-4">
            <div class="bg-blue-50 rounded-xl p-4 border border-blue-100">
              <div class="flex items-center gap-3">
                <span class="text-2xl">👨‍💼</span>
                <div>
                  <div class="text-2xl font-bold text-blue-600">{{ availableSales }}</div>
                  <div class="text-sm text-blue-700 font-medium">Disponibili</div>
                </div>
              </div>
            </div>
            
            <div class="bg-orange-50 rounded-xl p-4 border border-orange-100">
              <div class="flex items-center gap-3">
                <span class="text-2xl">⏳</span>
                <div>
                  <div class="text-2xl font-bold text-orange-600">{{ activeGenerations }}</div>
                  <div class="text-sm text-orange-700 font-medium">Generazioni</div>
                </div>
              </div>
            </div>
            
            <div class="bg-green-50 rounded-xl p-4 border border-green-100">
              <div class="flex items-center gap-3">
                <span class="text-2xl">💰</span>
                <div>
                  <div class="text-2xl font-bold text-green-600">{{ formatCurrency(totalValueGenerated) }}</div>
                  <div class="text-sm text-green-700 font-medium">Valore Totale</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content Grid -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Sales People Section -->
        <section class="space-y-6">
          <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-6">
              <div class="flex items-center gap-3">
                <span class="text-2xl">👨‍💼</span>
                <h2 class="text-xl font-bold text-gray-900">Commerciali</h2>
              </div>
              <button
                @click="refreshSalesPeople"
                :disabled="loading.salesPeople"
                class="px-4 py-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors duration-200 flex items-center gap-2"
              >
                <span class="text-lg">🔄</span>
                Aggiorna
              </button>
            </div>

            <div v-if="loading.salesPeople" class="flex items-center justify-center py-12">
              <div class="flex items-center gap-3">
                <svg class="animate-spin w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-gray-600">Caricamento...</span>
              </div>
            </div>

            <div v-else-if="salesPeople.length === 0" class="text-center py-12">
              <div class="text-6xl mb-4">👨‍💼</div>
              <h3 class="text-xl font-bold text-gray-900 mb-2">Nessun commerciale</h3>
              <p class="text-gray-600 mb-6">
                Vai alla sezione HR per assumere il tuo primo commerciale
              </p>
              <button
                @click="goToHR"
                class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors duration-200"
              >
                Assumi Commerciali
              </button>
            </div>

            <div v-else class="space-y-6">
              <div
                v-for="salesPerson in salesPeople"
                :key="salesPerson.id"
                class="bg-gray-50 rounded-xl p-6 border border-gray-200 transition-all duration-200"
                :class="{
                  'bg-orange-50 border-orange-200': salesPerson.status.is_busy,
                  'bg-green-50 border-green-200': salesPerson.status.is_available
                }"
              >
                <div class="flex items-start justify-between mb-4">
                  <div class="flex-1">
                    <h3 class="text-lg font-bold text-gray-900">{{ salesPerson.name }}</h3>
                    <div class="flex items-center gap-2 mt-1">
                      <span class="text-yellow-500">{{ salesPerson.experience.stars }}</span>
                      <span class="text-sm text-gray-600">{{ salesPerson.experience.name }}</span>
                    </div>
                  </div>
                  <div class="px-3 py-1 rounded-lg text-sm font-medium"
                       :class="{
                         'bg-orange-100 text-orange-700': salesPerson.status.is_busy,
                         'bg-green-100 text-green-700': salesPerson.status.is_available
                       }">
                    {{ salesPerson.status.is_busy ? 'Occupato' : 'Disponibile' }}
                  </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                  <div>
                    <span class="text-sm text-gray-500">Specializzazione:</span>
                    <div class="font-medium text-gray-900">{{ salesPerson.specialization.name || 'Generica' }}</div>
                  </div>
                  <div>
                    <span class="text-sm text-gray-500">Stipendio:</span>
                    <div class="font-medium text-gray-900">{{ salesPerson.salary.formatted }}/mese</div>
                  </div>
                  <div>
                    <span class="text-sm text-gray-500">Progetti generati:</span>
                    <div class="font-medium text-gray-900">{{ salesPerson.statistics.projects_generated }}</div>
                  </div>
                  <div>
                    <span class="text-sm text-gray-500">Valore totale:</span>
                    <div class="font-medium text-gray-900">{{ formatCurrency(salesPerson.statistics.total_value_generated) }}</div>
                  </div>
                </div>

                <!-- Generation Capabilities -->
                <div class="bg-white rounded-lg p-4 mb-4">
                  <h4 class="font-semibold text-gray-900 mb-3">Capacità di Generazione:</h4>
                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <span class="text-sm text-gray-500">Tempo stimato:</span>
                      <div class="font-medium text-gray-900">{{ salesPerson.generation_capabilities.estimated_generation_time }} min</div>
                    </div>
                    <div>
                      <span class="text-sm text-gray-500">Valore stimato:</span>
                      <div class="font-medium text-gray-900">{{ formatCurrency(salesPerson.generation_capabilities.estimated_project_value) }}</div>
                    </div>
                  </div>
                </div>

                <!-- Current Generation (if any) -->
                <div v-if="salesPerson.current_generation" class="bg-blue-50 rounded-lg p-4 mb-4">
                  <h4 class="font-semibold text-blue-900 mb-3">Generazione in Corso:</h4>
                  <div class="space-y-3">
                    <div>
                      <span class="text-sm text-blue-600">Valore stimato:</span>
                      <div class="font-medium text-blue-900">{{ formatCurrency(salesPerson.current_generation.estimated_value.amount) }}</div>
                    </div>
                    
                    <div>
                      <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-blue-600">Progresso</span>
                        <span class="text-sm font-medium text-blue-900">
                          {{ Math.round(salesPerson.current_generation.timing.current_progress) }}%
                        </span>
                      </div>
                      <div class="w-full bg-blue-200 rounded-full h-2">
                        <div
                          class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                          :style="{ width: `${salesPerson.current_generation.timing.current_progress}%` }"
                        ></div>
                      </div>
                      <div class="mt-2">
                        <span v-if="salesPerson.current_generation.timing.is_ready_for_completion" class="text-green-600 text-sm font-medium">
                          ✅ Generazione completata
                        </span>
                        <span v-else class="text-blue-600 text-sm">
                          ⏱️ {{ salesPerson.current_generation.timing.time_remaining_formatted }}
                        </span>
                      </div>
                    </div>

                    <div v-if="salesPerson.current_generation.timing.is_ready_for_completion">
                      <button
                        @click="completeGeneration(salesPerson.current_generation)"
                        :disabled="completingGeneration === salesPerson.current_generation.id"
                        class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-semibold rounded-lg transition-colors duration-200"
                      >
                        <span v-if="completingGeneration === salesPerson.current_generation.id">Completando...</span>
                        <span v-else>✅ Completa Generazione</span>
                      </button>
                    </div>
                  </div>
                </div>

                <!-- Actions -->
                <div class="flex gap-3">
                  <button
                    v-if="!salesPerson.status.is_busy"
                    @click="startGeneration(salesPerson)"
                    :disabled="generatingProject === salesPerson.id"
                    class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-semibold rounded-lg transition-colors duration-200"
                  >
                    <span v-if="generatingProject === salesPerson.id">Avviando...</span>
                    <span v-else>🚀 Genera Progetto</span>
                  </button>
                  <button
                    v-else
                    @click="openCancelModal(salesPerson)"
                    class="flex-1 px-4 py-2 border border-red-300 hover:border-red-400 hover:bg-red-50 text-red-700 font-semibold rounded-lg transition-colors duration-200"
                  >
                    Annulla Generazione
                  </button>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- Generated Projects Section -->
        <section class="space-y-6">
          <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-6">
              <div class="flex items-center gap-3">
                <span class="text-2xl">📋</span>
                <h2 class="text-xl font-bold text-gray-900">Progetti Generati Recentemente</h2>
              </div>
              <button
                @click="refreshGeneratedProjects"
                :disabled="loading.projects"
                class="px-4 py-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors duration-200 flex items-center gap-2"
              >
                <span class="text-lg">🔄</span>
                Aggiorna
              </button>
            </div>

            <div v-if="loading.projects" class="flex items-center justify-center py-12">
              <div class="flex items-center gap-3">
                <svg class="animate-spin w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-gray-600">Caricamento...</span>
              </div>
            </div>

            <div v-else-if="recentProjects.length === 0" class="text-center py-12">
              <div class="text-6xl mb-4">📋</div>
              <h3 class="text-xl font-bold text-gray-900 mb-2">Nessun progetto generato</h3>
              <p class="text-gray-600">
                I commerciali non hanno ancora generato progetti
              </p>
            </div>

            <div v-else class="space-y-4">
              <div
                v-for="project in recentProjects"
                :key="project.id"
                class="bg-gray-50 rounded-xl p-4 border border-gray-200 transition-all duration-200"
                :class="{
                  'bg-yellow-50 border-yellow-200': project.status.code === 'pending',
                  'bg-blue-50 border-blue-200': project.status.code === 'in_progress',
                  'bg-green-50 border-green-200': project.status.code === 'completed'
                }"
              >
                <div class="flex items-start justify-between mb-3">
                  <div class="flex-1">
                    <h3 class="font-bold text-gray-900">{{ project.name }}</h3>
                    <div class="flex items-center gap-3 mt-1">
                      <span class="text-yellow-500 text-sm">{{ project.complexity.stars }} {{ project.complexity.name }}</span>
                      <div class="px-2 py-1 rounded-lg text-xs font-medium"
                           :class="{
                             'bg-yellow-100 text-yellow-700': project.status.code === 'pending',
                             'bg-blue-100 text-blue-700': project.status.code === 'in_progress',
                             'bg-green-100 text-green-700': project.status.code === 'completed'
                           }">
                        {{ project.status.code === 'pending' ? 'In attesa' : 
                           project.status.code === 'in_progress' ? 'In corso' : 'Completato' }}
                      </div>
                    </div>
                  </div>
                  <div class="text-lg font-bold text-gray-900">
                    {{ project.value.formatted }}
                  </div>
                </div>

                <div class="grid grid-cols-2 gap-3 text-sm">
                  <div>
                    <span class="text-gray-500">Generato da:</span>
                    <div class="font-medium text-gray-900">{{ project.created_by_sales_person?.name || 'N/A' }}</div>
                  </div>
                  <div>
                    <span class="text-gray-500">Data generazione:</span>
                    <div class="font-medium text-gray-900">{{ formatDate(project.created_at) }}</div>
                  </div>
                  <div v-if="project.assignment?.developer">
                    <span class="text-gray-500">Assegnato a:</span>
                    <div class="font-medium text-gray-900">{{ project.assignment.developer.name }}</div>
                  </div>
                  <div v-if="project.timing?.current_progress > 0">
                    <span class="text-gray-500">Progresso:</span>
                    <div class="font-medium text-gray-900">{{ Math.round(project.timing.current_progress) }}%</div>
                  </div>
                </div>

                <div v-if="project.status.code === 'pending'" class="mt-4">
                  <button
                    @click="goToProduction"
                    class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors duration-200"
                  >
                    Assegna Sviluppatore
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Sales Statistics -->
          <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <div class="flex items-center gap-3 mb-6">
              <span class="text-2xl">📊</span>
              <h2 class="text-xl font-bold text-gray-900">Statistiche Sales</h2>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-4 border border-blue-200">
                <div class="flex items-center gap-2 mb-2">
                  <span class="text-xl">📈</span>
                  <span class="text-sm font-medium text-blue-700">Conversione Media</span>
                </div>
                <div class="text-2xl font-bold text-blue-900">{{ averageConversionRate }}%</div>
                <div class="text-xs text-blue-600">Progetti completati / generati</div>
              </div>

              <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-4 border border-green-200">
                <div class="flex items-center gap-2 mb-2">
                  <span class="text-xl">💰</span>
                  <span class="text-sm font-medium text-green-700">Valore Medio Progetto</span>
                </div>
                <div class="text-2xl font-bold text-green-900">{{ formatCurrency(averageProjectValue) }}</div>
                <div class="text-xs text-green-600">Media ultimi 10 progetti</div>
              </div>

              <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-4 border border-purple-200">
                <div class="flex items-center gap-2 mb-2">
                  <span class="text-xl">⚡</span>
                  <span class="text-sm font-medium text-purple-700">Efficienza Team</span>
                </div>
                <div class="text-2xl font-bold text-purple-900">{{ teamEfficiencyRating }}</div>
                <div class="text-xs text-purple-600">{{ teamEfficiencyDescription }}</div>
              </div>

              <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl p-4 border border-orange-200">
                <div class="flex items-center gap-2 mb-2">
                  <span class="text-xl">🎯</span>
                  <span class="text-sm font-medium text-orange-700">Progetti Questo Mese</span>
                </div>
                <div class="text-2xl font-bold text-orange-900">{{ projectsThisMonth }}</div>
                <div class="text-xs text-orange-600">{{ projectsThisMonthTrend }}</div>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>

    <!-- Cancel Generation Modal -->
    <div
      v-if="modals.cancel"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
      @click="closeCancelModal"
    >
      <div
        class="bg-white rounded-2xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto"
        @click.stop
      >
        <!-- Modal Header -->
        <div class="p-6 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <span class="text-2xl">⚠️</span>
              <h3 class="text-xl font-bold text-gray-900">Annulla Generazione</h3>
            </div>
            <button
              @click="closeCancelModal"
              class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors duration-200"
            >
              <span class="text-lg">✕</span>
            </button>
          </div>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
          <div v-if="selectedSalesPerson">
            <p class="text-gray-700 mb-4">
              Sei sicuro di voler annullare la generazione in corso di <strong>{{ selectedSalesPerson.name }}</strong>?
            </p>
            <p class="text-amber-600 font-medium">
              Il progresso della generazione andrà perso e il commerciale tornerà disponibile.
            </p>
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="p-6 border-t border-gray-200 flex items-center gap-3">
          <button
            @click="closeCancelModal"
            class="flex-1 px-4 py-3 border border-gray-300 hover:border-gray-400 hover:bg-gray-50 text-gray-700 font-semibold rounded-xl transition-colors duration-200"
          >
            Mantieni Generazione
          </button>
          <button
            @click="cancelGeneration"
            class="flex-1 px-4 py-3 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-xl transition-colors duration-200"
          >
            Annulla Generazione
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useGameStore } from '@/js/stores/game'
import { useNotificationStore } from '@/js/stores/notifications'
import api from '@/js/services/api'
import { formatCurrency, formatDate } from '@/js/utils/helpers'

// Stores
const gameStore = useGameStore()
const notificationStore = useNotificationStore()
const router = useRouter()

// Reactive state
const loading = ref({
  salesPeople: false,
  projects: false
})

const salesPeople = ref([])
const recentProjects = ref([])

const modals = ref({
  cancel: false
})

const selectedSalesPerson = ref(null)
const generatingProject = ref(null)
const completingGeneration = ref(null)

// Computed properties
const availableSales = computed(() => {
  return salesPeople.value.filter(s => s.status.is_available).length
})

const activeGenerations = computed(() => {
  return salesPeople.value.filter(s => s.status.is_busy).length
})

const totalValueGenerated = computed(() => {
  return salesPeople.value.reduce((sum, s) => sum + (s.statistics.total_value_generated || 0), 0)
})

const averageConversionRate = computed(() => {
  if (salesPeople.value.length === 0) return 0

  const totalConversion = salesPeople.value.reduce((sum, s) => {
    return sum + (s.statistics.conversion_rate || 0)
  }, 0)

  return Math.round(totalConversion / salesPeople.value.length)
})

const averageProjectValue = computed(() => {
  if (recentProjects.value.length === 0) return 0

  const totalValue = recentProjects.value
    .slice(0, 10)
    .reduce((sum, p) => sum + (p.value.amount || 0), 0)

  return totalValue / Math.min(10, recentProjects.value.length)
})

const teamEfficiencyRating = computed(() => {
  const totalProjects = salesPeople.value.reduce((sum, s) => sum + (s.statistics.projects_generated || 0), 0)
  const teamSize = salesPeople.value.length

  if (teamSize === 0) return 'N/A'

  const efficiency = totalProjects / teamSize
  if (efficiency >= 10) return 'Eccellente'
  if (efficiency >= 5) return 'Buona'
  if (efficiency >= 2) return 'Sufficiente'
  return 'Da migliorare'
})

const teamEfficiencyDescription = computed(() => {
  const totalProjects = salesPeople.value.reduce((sum, s) => sum + (s.statistics.projects_generated || 0), 0)
  const teamSize = salesPeople.value.length

  if (teamSize === 0) return 'Nessun commerciale'

  const projectsPerPerson = Math.round(totalProjects / teamSize)
  return `${projectsPerPerson} progetti per persona`
})

const projectsThisMonth = computed(() => {
  const now = new Date()
  const thisMonth = recentProjects.value.filter(p => {
    const projectDate = new Date(p.created_at)
    return projectDate.getMonth() === now.getMonth() &&
           projectDate.getFullYear() === now.getFullYear()
  })
  return thisMonth.length
})

const projectsThisMonthTrend = computed(() => {
  const count = projectsThisMonth.value
  if (count === 0) return 'Nessun progetto'
  if (count >= 10) return '🔥 Ottimo trend'
  if (count >= 5) return '📈 Buon trend'
  return '📊 In crescita'
})

// Methods
const fetchSalesPeople = async () => {
  loading.value.salesPeople = true
  try {
    const response = await api.get(`/games/${gameStore.currentGame.id}/sales-people`)
    salesPeople.value = response.data.data
  } catch (error) {
    notificationStore.error('Errore nel caricamento dei commerciali')
    console.error('Error fetching sales people:', error)
  } finally {
    loading.value.salesPeople = false
  }
}

const fetchGeneratedProjects = async () => {
  loading.value.projects = true
  try {
    const response = await api.get(`/games/${gameStore.currentGame.id}/projects`, {
      params: {
        limit: 20,
        sort: 'created_at',
        order: 'desc'
      }
    })
    recentProjects.value = response.data.data
  } catch (error) {
    notificationStore.error('Errore nel caricamento dei progetti')
    console.error('Error fetching projects:', error)
  } finally {
    loading.value.projects = false
  }
}

const refreshSalesPeople = () => {
  fetchSalesPeople()
}

const refreshGeneratedProjects = () => {
  fetchGeneratedProjects()
}

const startGeneration = async (salesPerson) => {
  generatingProject.value = salesPerson.id
  try {
    await api.post(`/games/${gameStore.currentGame.id}/sales-people/${salesPerson.id}/generate-project`)

    notificationStore.success(`${salesPerson.name} ha iniziato a generare un nuovo progetto!`)

    await fetchSalesPeople()
  } catch (error) {
    notificationStore.error('Errore nell\'avvio della generazione')
    console.error('Error starting generation:', error)
  } finally {
    generatingProject.value = null
  }
}

const completeGeneration = async (generation) => {
  completingGeneration.value = generation.id
  try {
    await api.post(`/generations/${generation.id}/complete`)

    notificationStore.success(`Nuovo progetto generato! Valore: ${formatCurrency(generation.estimated_value.amount)}`)

    await Promise.all([fetchSalesPeople(), fetchGeneratedProjects()])
    await gameStore.refreshGameState()
  } catch (error) {
    if (error.response?.status === 400) {
      notificationStore.warning('La generazione non è ancora completata')
    } else {
      notificationStore.error('Errore nel completamento della generazione')
    }
    console.error('Error completing generation:', error)
  } finally {
    completingGeneration.value = null
  }
}

const openCancelModal = (salesPerson) => {
  selectedSalesPerson.value = salesPerson
  modals.value.cancel = true
}

const closeCancelModal = () => {
  modals.value.cancel = false
  selectedSalesPerson.value = null
}

const cancelGeneration = async () => {
  if (!selectedSalesPerson.value) return

  try {
    await api.post(`/games/${gameStore.currentGame.id}/sales-people/${selectedSalesPerson.value.id}/cancel-generation`)

    notificationStore.success('Generazione annullata')
    closeCancelModal()

    await fetchSalesPeople()
  } catch (error) {
    notificationStore.error('Errore nell\'annullamento della generazione')
    console.error('Error canceling generation:', error)
  }
}

const goToHR = () => {
  router.push({ name: 'HR' })
}

const goToProduction = () => {
  router.push({ name: 'Production' })
}

// Auto-refresh data
let refreshInterval = null

onMounted(async () => {
  await Promise.all([fetchSalesPeople(), fetchGeneratedProjects()])

  // Auto-refresh every 30 seconds
  refreshInterval = setInterval(() => {
    if (!modals.value.cancel) {
      Promise.all([fetchSalesPeople(), fetchGeneratedProjects()])
    }
  }, 30000)
})

onUnmounted(() => {
  if (refreshInterval) {
    clearInterval(refreshInterval)
  }
})
</script>