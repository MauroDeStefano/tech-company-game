<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-white">
    <!-- Page Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
          <div>
            <h1 class="text-4xl font-bold text-gray-900 tracking-tight mb-2">
              <span class="text-2xl">🏗️</span>
              Produzione
            </h1>
            <p class="text-xl text-gray-600">
              Gestisci sviluppatori e progetti
            </p>
          </div>

          <div class="grid grid-cols-3 gap-4">
            <div class="bg-gray-50 rounded-lg p-3 text-center">
              <span class="text-2xl block mb-1">👨‍💻</span>
              <div class="text-lg font-bold text-gray-900">{{ availableDevelopers }}</div>
              <div class="text-xs text-gray-600">Disponibili</div>
            </div>

            <div class="bg-gray-50 rounded-lg p-3 text-center">
              <span class="text-2xl block mb-1">⏳</span>
              <div class="text-lg font-bold text-gray-900">{{ pendingProjects }}</div>
              <div class="text-xs text-gray-600">In Attesa</div>
            </div>

            <div class="bg-gray-50 rounded-lg p-3 text-center">
              <span class="text-2xl block mb-1">⚡</span>
              <div class="text-lg font-bold text-gray-900">{{ activeProjects }}</div>
              <div class="text-xs text-gray-600">In Corso</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Developers Section -->
        <section class="lg:col-span-5">
          <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
              <div class="flex items-center gap-2">
                <span class="text-2xl">👨‍💻</span>
                <h3 class="text-lg font-bold text-gray-900">Sviluppatori</h3>
                <div v-if="loading.developers" class="ml-2">
                  <svg class="animate-spin w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                </div>
              </div>
              <button
                @click="refreshDevelopers"
                :disabled="loading.developers"
                class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 rounded-lg transition-colors duration-200"
              >
                <span>🔄</span>
                Aggiorna
              </button>
            </div>

            <!-- Content -->
            <div v-if="developers.length === 0" class="text-center py-8">
              <div class="text-6xl mb-4">👨‍💻</div>
              <h3 class="text-lg font-semibold text-gray-900 mb-2">Nessuno sviluppatore</h3>
              <p class="text-gray-600 mb-6">
                Vai alla sezione HR per assumere il tuo primo sviluppatore
              </p>
              <button
                @click="goToHR"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors duration-200"
              >
                Assumi Sviluppatori
              </button>
            </div>

            <div v-else class="space-y-4">
              <div
                v-for="developer in developers"
                :key="developer.id"
                class="border rounded-lg p-4"
                :class="{
                  'border-red-200 bg-red-50': developer.status?.is_busy,
                  'border-green-200 bg-green-50': developer.status?.is_available
                }"
              >
                <div class="flex items-center justify-between mb-3">
                  <div>
                    <h3 class="font-semibold text-gray-900">{{ developer.name }}</h3>
                    <div class="flex items-center gap-2 mt-1">
                      <span class="text-sm">{{ developer.seniority?.stars || '⭐' }}</span>
                      <span class="text-sm text-gray-600">{{ developer.seniority?.name || 'Junior' }}</span>
                    </div>
                  </div>
                  <span 
                    class="text-xs px-2 py-1 rounded-full"
                    :class="{
                      'bg-red-100 text-red-700': developer.status?.is_busy,
                      'bg-green-100 text-green-700': developer.status?.is_available
                    }"
                  >
                    {{ developer.status?.is_busy ? 'Occupato' : 'Disponibile' }}
                  </span>
                </div>

                <div class="space-y-1 text-sm text-gray-600 mb-3">
                  <div class="flex justify-between">
                    <span>Specializzazione:</span>
                    <span>{{ developer.specialization?.name || 'Generica' }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span>Stipendio:</span>
                    <span>{{ developer.salary?.formatted || '€2000' }}/mese</span>
                  </div>
                  <div class="flex justify-between">
                    <span>Progetti completati:</span>
                    <span>{{ developer.statistics?.projects_completed || 0 }}</span>
                  </div>
                </div>

                <!-- Current Project -->
                <div v-if="developer.current_project" class="bg-white border border-gray-200 rounded-lg p-3">
                  <h4 class="font-medium text-gray-900 mb-2">Progetto Corrente:</h4>
                  <div class="space-y-2">
                    <div class="font-medium text-gray-800">{{ developer.current_project.name }}</div>
                    <div class="flex items-center gap-2">
                      <div class="flex-1 bg-gray-200 rounded-full h-2">
                        <div
                          class="bg-blue-500 h-2 rounded-full transition-all duration-300"
                          :style="{ width: `${developer.current_project.timing?.current_progress || 0}%` }"
                        ></div>
                      </div>
                      <span class="text-sm font-medium text-gray-900">
                        {{ Math.round(developer.current_project.timing?.current_progress || 0) }}%
                      </span>
                    </div>
                    <div class="text-sm text-gray-600">
                      {{ developer.current_project.timing?.time_remaining_formatted || 'Tempo sconosciuto' }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- Projects Section -->
        <section class="lg:col-span-7 space-y-8">
          <!-- Pending Projects -->
          <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-6">
              <div class="flex items-center gap-2">
                <span class="text-2xl">⏳</span>
                <h3 class="text-lg font-bold text-gray-900">Progetti in Attesa</h3>
                <div v-if="loading.projects" class="ml-2">
                  <svg class="animate-spin w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                </div>
              </div>
              <button
                @click="refreshProjects"
                :disabled="loading.projects"
                class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 rounded-lg transition-colors duration-200"
              >
                <span>🔄</span>
                Aggiorna
              </button>
            </div>

            <div v-if="pendingProjectsList.length === 0" class="text-center py-8">
              <div class="text-6xl mb-4">📋</div>
              <h3 class="text-lg font-semibold text-gray-900 mb-2">Nessun progetto in attesa</h3>
              <p class="text-gray-600 mb-6">
                Vai alla sezione Sales per generare nuovi progetti
              </p>
              <button
                @click="goToSales"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors duration-200"
              >
                Genera Progetti
              </button>
            </div>

            <div v-else class="space-y-4">
              <div
                v-for="project in pendingProjectsList"
                :key="project.id"
                class="border border-yellow-200 bg-yellow-50 rounded-lg p-4"
              >
                <div class="flex items-start justify-between mb-3">
                  <div>
                    <h3 class="font-semibold text-gray-900">{{ project.name }}</h3>
                    <div class="flex items-center gap-2 mt-1">
                      <span class="text-sm">{{ project.complexity?.stars || '⭐' }}</span>
                      <span class="text-sm text-gray-600">{{ project.complexity?.name || 'Medio' }}</span>
                    </div>
                  </div>
                  <div class="text-lg font-bold text-gray-900">
                    {{ project.value?.formatted || '€0' }}
                  </div>
                </div>

                <div class="space-y-1 text-sm text-gray-600 mb-4">
                  <div class="flex justify-between">
                    <span>Complessità:</span>
                    <span>{{ project.complexity?.level || 3 }}/5</span>
                  </div>
                  <div class="flex justify-between">
                    <span>Valore orario:</span>
                    <span>{{ project.value?.hourly_rate || '50' }}€/h</span>
                  </div>
                  <div v-if="project.created_by_sales_person" class="flex justify-between">
                    <span>Generato da:</span>
                    <span>{{ project.created_by_sales_person.name }}</span>
                  </div>
                </div>

                <button
                  @click="openAssignModal(project)"
                  :disabled="availableDevelopers === 0 || assigningProject === project.id"
                  class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-medium rounded-lg transition-colors duration-200"
                >
                  <span v-if="assigningProject === project.id">Assegnando...</span>
                  <span v-else>Assegna Sviluppatore</span>
                </button>
              </div>
            </div>
          </div>

          <!-- Active Projects -->
          <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <div class="flex items-center gap-2 mb-6">
              <span class="text-2xl">⚡</span>
              <h3 class="text-lg font-bold text-gray-900">Progetti in Corso</h3>
            </div>

            <div v-if="activeProjectsList.length === 0" class="text-center py-8">
              <div class="text-6xl mb-4">⚡</div>
              <h3 class="text-lg font-semibold text-gray-900 mb-2">Nessun progetto in corso</h3>
              <p class="text-gray-600">
                Assegna progetti ai tuoi sviluppatori per iniziare
              </p>
            </div>

            <div v-else class="space-y-4">
              <div
                v-for="project in activeProjectsList"
                :key="project.id"
                class="border border-blue-200 bg-blue-50 rounded-lg p-4"
              >
                <div class="flex items-start justify-between mb-3">
                  <div>
                    <h3 class="font-semibold text-gray-900">{{ project.name }}</h3>
                    <div class="flex items-center gap-1 mt-1">
                      <span class="text-sm">👨‍💻</span>
                      <span class="text-sm text-gray-600">{{ project.assignment?.developer?.name || 'Sviluppatore' }}</span>
                    </div>
                  </div>
                  <div class="text-lg font-bold text-gray-900">
                    {{ project.value?.formatted || '€0' }}
                  </div>
                </div>

                <div class="mb-4">
                  <div class="flex items-center justify-between mb-1">
                    <span class="text-sm font-medium text-gray-700">Progresso</span>
                    <span class="text-sm font-semibold text-gray-900">
                      {{ Math.round(project.timing?.current_progress || 0) }}%
                    </span>
                  </div>
                  <div class="w-full bg-gray-200 rounded-full h-2">
                    <div
                      class="bg-blue-500 h-2 rounded-full transition-all duration-300"
                      :style="{ width: `${project.timing?.current_progress || 0}%` }"
                    ></div>
                  </div>
                  <div class="mt-2">
                    <span v-if="project.timing?.is_ready_for_completion" class="flex items-center gap-1 text-sm text-green-600 font-medium">
                      <span>✅</span>
                      Pronto per il completamento
                    </span>
                    <span v-else class="flex items-center gap-1 text-sm text-gray-600">
                      <span>⏱️</span>
                      {{ project.timing?.time_remaining_formatted || 'Tempo sconosciuto' }}
                    </span>
                  </div>
                </div>

                <div class="flex gap-2">
                  <button
                    v-if="project.timing?.is_ready_for_completion"
                    @click="completeProject(project)"
                    :disabled="completingProject === project.id"
                    class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-medium rounded-lg transition-colors duration-200"
                  >
                    <span v-if="completingProject === project.id">Completando...</span>
                    <span v-else>✅ Completa Progetto</span>
                  </button>
                  <button
                    @click="openUnassignModal(project)"
                    class="px-4 py-2 border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium rounded-lg transition-colors duration-200"
                  >
                    Riassegna
                  </button>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>

    <!-- Assign Project Modal -->
    <div
      v-if="modals.assign"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
      @click="closeAssignModal"
    >
      <div
        class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto"
        @click.stop
      >
        <!-- Modal Header -->
        <div class="p-6 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2">
              <span>🚀</span>
              Assegna Sviluppatore
            </h3>
            <button
              @click="closeAssignModal"
              class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors duration-200"
            >
              <span class="text-lg">✕</span>
            </button>
          </div>
        </div>

        <!-- Modal Body -->
        <div v-if="selectedProject" class="p-6">
          <div class="mb-6 p-4 bg-gray-50 rounded-lg">
            <h3 class="font-semibold text-gray-900 mb-2">{{ selectedProject.name }}</h3>
            <div class="grid grid-cols-2 gap-4 text-sm text-gray-600">
              <div class="flex justify-between">
                <span>Complessità:</span>
                <span>{{ selectedProject.complexity?.name || 'Medio' }}</span>
              </div>
              <div class="flex justify-between">
                <span>Valore:</span>
                <span>{{ selectedProject.value?.formatted || '€0' }}</span>
              </div>
            </div>
          </div>

          <div>
            <h4 class="font-semibold text-gray-900 mb-4">Seleziona Sviluppatore:</h4>
            <div class="space-y-3">
              <div
                v-for="developer in availableDevelopersList"
                :key="developer.id"
                class="border rounded-lg p-3 cursor-pointer transition-all duration-200"
                :class="{
                  'border-blue-300 bg-blue-50': selectedDeveloper?.id === developer.id,
                  'border-gray-200 hover:border-gray-300 hover:bg-gray-50': selectedDeveloper?.id !== developer.id
                }"
                @click="selectedDeveloper = developer"
              >
                <div class="flex items-center justify-between mb-2">
                  <span class="font-medium text-gray-900">{{ developer.name }}</span>
                  <span class="text-sm">{{ developer.seniority?.stars || '⭐' }}</span>
                </div>
                <div class="text-sm text-gray-600 space-y-1">
                  <div class="flex justify-between">
                    <span>Specializzazione:</span>
                    <span>{{ developer.specialization?.name || 'Generica' }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span>Progetti completati:</span>
                    <span>{{ developer.statistics?.projects_completed || 0 }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span>Tempo stimato:</span>
                    <span>{{ calculateEstimatedTime(selectedProject, developer) }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="p-6 border-t border-gray-200 flex justify-end gap-3">
          <button
            @click="closeAssignModal"
            class="px-4 py-2 border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium rounded-lg transition-colors duration-200"
          >
            Annulla
          </button>
          <button
            @click="assignProject"
            :disabled="!selectedDeveloper || assigningProject !== null"
            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-medium rounded-lg transition-colors duration-200"
          >
            <span v-if="assigningProject">Assegnando...</span>
            <span v-else>Assegna Progetto</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Unassign Project Modal -->
    <div
      v-if="modals.unassign"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
      @click="closeUnassignModal"
    >
      <div
        class="bg-white rounded-2xl shadow-2xl max-w-md w-full"
        @click.stop
      >
        <!-- Modal Header -->
        <div class="p-6 border-b border-gray-200">
          <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2">
            <span>🔄</span>
            Riassegna Progetto
          </h3>
        </div>

        <!-- Modal Body -->
        <div v-if="selectedProject" class="p-6">
          <p class="text-gray-700 mb-4">
            Sei sicuro di voler rimuovere <strong>{{ selectedProject.assignment?.developer?.name }}</strong>
            dal progetto <strong>{{ selectedProject.name }}</strong>?
          </p>
          <p class="text-sm text-yellow-700 bg-yellow-50 border border-yellow-200 rounded-lg p-3">
            Il progetto tornerà in stato "In Attesa" e dovrai riassegnarlo manualmente.
          </p>
        </div>

        <!-- Modal Footer -->
        <div class="p-6 border-t border-gray-200 flex justify-end gap-3">
          <button
            @click="closeUnassignModal"
            class="px-4 py-2 border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium rounded-lg transition-colors duration-200"
          >
            Annulla
          </button>
          <button
            @click="unassignProject"
            class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white font-medium rounded-lg transition-colors duration-200"
          >
            Riassegna
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

// Stores
const gameStore = useGameStore()
const notificationStore = useNotificationStore()
const router = useRouter()

// Reactive state
const loading = ref({
  developers: false,
  projects: false
})

const developers = ref([])
const pendingProjectsList = ref([])
const activeProjectsList = ref([])

const modals = ref({
  assign: false,
  unassign: false
})

const selectedProject = ref(null)
const selectedDeveloper = ref(null)
const assigningProject = ref(null)
const completingProject = ref(null)

// Computed properties
const availableDevelopers = computed(() => {
  return developers.value.filter(d => d.status?.is_available).length
})

const pendingProjects = computed(() => pendingProjectsList.value.length)
const activeProjects = computed(() => activeProjectsList.value.length)

const availableDevelopersList = computed(() => {
  return developers.value.filter(d => d.status?.is_available)
})

// Methods
const fetchDevelopers = async () => {
  loading.value.developers = true
  try {
    const response = await api.get(`/games/${gameStore.currentGame.id}/developers`)
    developers.value = response.data.data
  } catch (error) {
    notificationStore.error('Errore nel caricamento degli sviluppatori')
    console.error('Error fetching developers:', error)
  } finally {
    loading.value.developers = false
  }
}

const fetchProjects = async () => {
  loading.value.projects = true
  try {
    const [pendingResponse, activeResponse] = await Promise.all([
      api.get(`/games/${gameStore.currentGame.id}/projects/pending`),
      api.get(`/games/${gameStore.currentGame.id}/projects/active`)
    ])

    pendingProjectsList.value = pendingResponse.data.data
    activeProjectsList.value = activeResponse.data.data
  } catch (error) {
    notificationStore.error('Errore nel caricamento dei progetti')
    console.error('Error fetching projects:', error)
  } finally {
    loading.value.projects = false
  }
}

const refreshDevelopers = () => {
  fetchDevelopers()
}

const refreshProjects = () => {
  fetchProjects()
}

const openAssignModal = (project) => {
  selectedProject.value = project
  selectedDeveloper.value = null
  modals.value.assign = true
}

const closeAssignModal = () => {
  modals.value.assign = false
  selectedProject.value = null
  selectedDeveloper.value = null
}

const openUnassignModal = (project) => {
  selectedProject.value = project
  modals.value.unassign = true
}

const closeUnassignModal = () => {
  modals.value.unassign = false
  selectedProject.value = null
}

const assignProject = async () => {
  if (!selectedProject.value || !selectedDeveloper.value) return

  assigningProject.value = selectedProject.value.id
  try {
    await api.post(
      `/games/${gameStore.currentGame.id}/projects/${selectedProject.value.id}/assign`,
      { developer_id: selectedDeveloper.value.id }
    )

    notificationStore.success('Progetto assegnato con successo!')
    closeAssignModal()

    // Refresh data
    await Promise.all([fetchDevelopers(), fetchProjects()])
  } catch (error) {
    notificationStore.error('Errore nell\'assegnazione del progetto')
    console.error('Error assigning project:', error)
  } finally {
    assigningProject.value = null
  }
}

const unassignProject = async () => {
  if (!selectedProject.value) return

  try {
    await api.post(
      `/games/${gameStore.currentGame.id}/projects/${selectedProject.value.id}/unassign`
    )

    notificationStore.success('Progetto riassegnato con successo!')
    closeUnassignModal()

    // Refresh data
    await Promise.all([fetchDevelopers(), fetchProjects()])
  } catch (error) {
    notificationStore.error('Errore nella riassegnazione del progetto')
    console.error('Error unassigning project:', error)
  }
}

const completeProject = async (project) => {
  completingProject.value = project.id
  try {
    await api.post(`/projects/${project.id}/complete`)

    notificationStore.success(`Progetto "${project.name}" completato! +${project.value?.formatted || '€0'}`)

    // Refresh data
    await Promise.all([fetchDevelopers(), fetchProjects()])

    // Update game state
    await gameStore.refreshGameState()
  } catch (error) {
    if (error.response?.status === 400) {
      notificationStore.warning('Il progetto non è ancora pronto per il completamento')
    } else {
      notificationStore.error('Errore nel completamento del progetto')
    }
    console.error('Error completing project:', error)
  } finally {
    completingProject.value = null
  }
}

const calculateEstimatedTime = (project, developer) => {
  if (!project || !developer) return 'N/A'

  // Calcolo semplificato: complessità * 30 minuti * (1 - (seniority-1) * 0.15)
  const baseTime = (project.complexity?.level || 3) * 30
  const seniorityMultiplier = 1 - ((developer.seniority?.level || 1) - 1) * 0.15
  const estimatedMinutes = Math.round(baseTime * seniorityMultiplier)

  if (estimatedMinutes < 60) {
    return `${estimatedMinutes} min`
  } else {
    const hours = Math.floor(estimatedMinutes / 60)
    const minutes = estimatedMinutes % 60
    return minutes > 0 ? `${hours}h ${minutes}m` : `${hours}h`
  }
}

const goToHR = () => {
  router.push('/game/hr')
}

const goToSales = () => {
  router.push('/game/sales')
}

// Auto-refresh data
let refreshInterval = null

onMounted(async () => {
  await Promise.all([fetchDevelopers(), fetchProjects()])

  // Auto-refresh every 30 seconds
  refreshInterval = setInterval(() => {
    if (!modals.value.assign && !modals.value.unassign) {
      Promise.all([fetchDevelopers(), fetchProjects()])
    }
  }, 30000)
})

onUnmounted(() => {
  if (refreshInterval) {
    clearInterval(refreshInterval)
  }
})
</script>