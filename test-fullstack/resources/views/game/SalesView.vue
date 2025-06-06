<template>
  <div class="sales-view">
    <!-- Page Header -->
    <div class="sales-header">
      <div class="header-content">
        <h1 class="page-title">
          <span class="title-icon">💼</span>
          Sales
        </h1>
        <p class="page-subtitle">
          Gestisci commerciali e acquisizione clienti
        </p>
      </div>

      <div class="header-stats">
        <div class="stat-item">
          <span class="stat-icon">👨‍💼</span>
          <div class="stat-content">
            <span class="stat-value">{{ availableSales }}</span>
            <span class="stat-label">Disponibili</span>
          </div>
        </div>
        <div class="stat-item">
          <span class="stat-icon">⏳</span>
          <div class="stat-content">
            <span class="stat-value">{{ activeGenerations }}</span>
            <span class="stat-label">Generazioni</span>
          </div>
        </div>
        <div class="stat-item">
          <span class="stat-icon">💰</span>
          <div class="stat-content">
            <span class="stat-value">{{ formatCurrency(totalValueGenerated) }}</span>
            <span class="stat-label">Valore Totale</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content Grid -->
    <div class="sales-grid">
      <!-- Sales People Section -->
      <section class="sales-section sales-people-section">
        <BaseCard
          title="Commerciali"
          icon="👨‍💼"
          :loading="loading.salesPeople"
        >
          <template #actions>
            <BaseButton
              variant="ghost"
              size="sm"
              icon="🔄"
              @click="refreshSalesPeople"
              :disabled="loading.salesPeople"
            >
              Aggiorna
            </BaseButton>
          </template>

          <div v-if="salesPeople.length === 0" class="empty-state">
            <div class="empty-icon">👨‍💼</div>
            <h3 class="empty-title">Nessun commerciale</h3>
            <p class="empty-description">
              Vai alla sezione HR per assumere il tuo primo commerciale
            </p>
            <BaseButton variant="primary" @click="goToHR">
              Assumi Commerciali
            </BaseButton>
          </div>

          <div v-else class="sales-people-list">
            <div
              v-for="salesPerson in salesPeople"
              :key="salesPerson.id"
              class="sales-person-card"
              :class="{
                'sales-person-card--busy': salesPerson.status.is_busy,
                'sales-person-card--available': salesPerson.status.is_available
              }"
            >
              <div class="sales-person-header">
                <div class="sales-person-info">
                  <h3 class="sales-person-name">{{ salesPerson.name }}</h3>
                  <div class="sales-person-experience">
                    <span class="experience-stars">{{ salesPerson.experience.stars }}</span>
                    <span class="experience-name">{{ salesPerson.experience.name }}</span>
                  </div>
                </div>
                <StatusBadge
                  :status="salesPerson.status.is_busy ? 'busy' : 'available'"
                  size="sm"
                />
              </div>

              <div class="sales-person-details">
                <div class="detail-item">
                  <span class="detail-label">Specializzazione:</span>
                  <span class="detail-value">{{ salesPerson.specialization.name || 'Generica' }}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">Stipendio:</span>
                  <span class="detail-value">{{ salesPerson.salary.formatted }}/mese</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">Progetti generati:</span>
                  <span class="detail-value">{{ salesPerson.statistics.projects_generated }}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">Valore totale:</span>
                  <span class="detail-value">{{ formatCurrency(salesPerson.statistics.total_value_generated) }}</span>
                </div>
              </div>

              <!-- Generation Capabilities -->
              <div class="generation-capabilities">
                <h4 class="capabilities-title">Capacità di Generazione:</h4>
                <div class="capabilities-grid">
                  <div class="capability-item">
                    <span class="capability-label">Tempo stimato:</span>
                    <span class="capability-value">{{ salesPerson.generation_capabilities.estimated_generation_time }} min</span>
                  </div>
                  <div class="capability-item">
                    <span class="capability-label">Valore stimato:</span>
                    <span class="capability-value">{{ formatCurrency(salesPerson.generation_capabilities.estimated_project_value) }}</span>
                  </div>
                </div>
              </div>

              <!-- Current Generation (if any) -->
              <div v-if="salesPerson.current_generation" class="current-generation">
                <h4 class="current-generation-title">Generazione in Corso:</h4>
                <div class="generation-info">
                  <div class="generation-details">
                    <div class="detail-item">
                      <span class="detail-label">Valore stimato:</span>
                      <span class="detail-value">{{ formatCurrency(salesPerson.current_generation.estimated_value.amount) }}</span>
                    </div>
                  </div>
                  <div class="generation-progress">
                    <div class="progress-header">
                      <span class="progress-label">Progresso</span>
                      <span class="progress-percentage">
                        {{ Math.round(salesPerson.current_generation.timing.current_progress) }}%
                      </span>
                    </div>
                    <div class="progress-bar">
                      <div
                        class="progress-fill progress-fill--sales"
                        :style="{ width: `${salesPerson.current_generation.timing.current_progress}%` }"
                      ></div>
                    </div>
                    <div class="progress-time">
                      <span v-if="salesPerson.current_generation.timing.is_ready_for_completion" class="completion-ready">
                        ✅ Generazione completata
                      </span>
                      <span v-else class="time-remaining">
                        ⏱️ {{ salesPerson.current_generation.timing.time_remaining_formatted }}
                      </span>
                    </div>
                  </div>

                  <div v-if="salesPerson.current_generation.timing.is_ready_for_completion" class="generation-actions">
                    <BaseButton
                      variant="success"
                      size="sm"
                      :disabled="completingGeneration === salesPerson.current_generation.id"
                      @click="completeGeneration(salesPerson.current_generation)"
                    >
                      <span v-if="completingGeneration === salesPerson.current_generation.id">Completando...</span>
                      <span v-else>✅ Completa Generazione</span>
                    </BaseButton>
                  </div>
                </div>
              </div>

              <!-- Actions -->
              <div class="sales-person-actions">
                <BaseButton
                  v-if="!salesPerson.status.is_busy"
                  variant="primary"
                  size="sm"
                  :disabled="generatingProject === salesPerson.id"
                  @click="startGeneration(salesPerson)"
                >
                  <span v-if="generatingProject === salesPerson.id">Avviando...</span>
                  <span v-else>🚀 Genera Progetto</span>
                </BaseButton>
                <BaseButton
                  v-else
                  variant="outline"
                  size="sm"
                  @click="openCancelModal(salesPerson)"
                >
                  Annulla Generazione
                </BaseButton>
              </div>
            </div>
          </div>
        </BaseCard>
      </section>

      <!-- Generated Projects Section -->
      <section class="sales-section generated-projects-section">
        <BaseCard
          title="Progetti Generati Recentemente"
          icon="📋"
          :loading="loading.projects"
        >
          <template #actions>
            <BaseButton
              variant="ghost"
              size="sm"
              icon="🔄"
              @click="refreshGeneratedProjects"
              :disabled="loading.projects"
            >
              Aggiorna
            </BaseButton>
          </template>

          <div v-if="recentProjects.length === 0" class="empty-state">
            <div class="empty-icon">📋</div>
            <h3 class="empty-title">Nessun progetto generato</h3>
            <p class="empty-description">
              I commerciali non hanno ancora generato progetti
            </p>
          </div>

          <div v-else class="generated-projects-list">
            <div
              v-for="project in recentProjects"
              :key="project.id"
              class="generated-project-card"
              :class="{
                'generated-project-card--pending': project.status.code === 'pending',
                'generated-project-card--assigned': project.status.code === 'in_progress',
                'generated-project-card--completed': project.status.code === 'completed'
              }"
            >
              <div class="project-header">
                <div class="project-info">
                  <h3 class="project-name">{{ project.name }}</h3>
                  <div class="project-meta">
                    <span class="project-complexity">{{ project.complexity.stars }} {{ project.complexity.name }}</span>
                    <StatusBadge :status="project.status.code" size="sm" />
                  </div>
                </div>
                <div class="project-value">
                  {{ project.value.formatted }}
                </div>
              </div>

              <div class="project-details">
                <div class="detail-item">
                  <span class="detail-label">Generato da:</span>
                  <span class="detail-value">{{ project.created_by_sales_person?.name || 'N/A' }}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">Data generazione:</span>
                  <span class="detail-value">{{ formatDate(project.created_at) }}</span>
                </div>
                <div v-if="project.assignment?.developer" class="detail-item">
                  <span class="detail-label">Assegnato a:</span>
                  <span class="detail-value">{{ project.assignment.developer.name }}</span>
                </div>
                <div v-if="project.timing?.current_progress > 0" class="detail-item">
                  <span class="detail-label">Progresso:</span>
                  <span class="detail-value">{{ Math.round(project.timing.current_progress) }}%</span>
                </div>
              </div>

              <div v-if="project.status.code === 'pending'" class="project-actions">
                <BaseButton
                  variant="primary"
                  size="sm"
                  @click="goToProduction"
                >
                  Assegna Sviluppatore
                </BaseButton>
              </div>
            </div>
          </div>
        </BaseCard>

        <!-- Sales Statistics -->
        <BaseCard
          title="Statistiche Sales"
          icon="📊"
          class="mt-6"
        >
          <div class="stats-grid">
            <div class="stat-card">
              <div class="stat-header">
                <span class="stat-icon">📈</span>
                <span class="stat-label">Conversione Media</span>
              </div>
              <div class="stat-value">{{ averageConversionRate }}%</div>
              <div class="stat-description">Progetti completati / generati</div>
            </div>

            <div class="stat-card">
              <div class="stat-header">
                <span class="stat-icon">💰</span>
                <span class="stat-label">Valore Medio Progetto</span>
              </div>
              <div class="stat-value">{{ formatCurrency(averageProjectValue) }}</div>
              <div class="stat-description">Media ultimi 10 progetti</div>
            </div>

            <div class="stat-card">
              <div class="stat-header">
                <span class="stat-icon">⚡</span>
                <span class="stat-label">Efficienza Team</span>
              </div>
              <div class="stat-value">{{ teamEfficiencyRating }}</div>
              <div class="stat-description">{{ teamEfficiencyDescription }}</div>
            </div>

            <div class="stat-card">
              <div class="stat-header">
                <span class="stat-icon">🎯</span>
                <span class="stat-label">Progetti Questo Mese</span>
              </div>
              <div class="stat-value">{{ projectsThisMonth }}</div>
              <div class="stat-description">{{ projectsThisMonthTrend }}</div>
            </div>
          </div>
        </BaseCard>
      </section>
    </div>

    <!-- Cancel Generation Modal -->
    <BaseModal
      v-model:is-open="modals.cancel"
      title="Annulla Generazione"
      icon="⚠️"
      variant="warning"
    >
      <div v-if="selectedSalesPerson">
        <p class="cancel-message">
          Sei sicuro di voler annullare la generazione in corso di <strong>{{ selectedSalesPerson.name }}</strong>?
        </p>
        <p class="cancel-warning">
          Il progresso della generazione andrà perso e il commerciale tornerà disponibile.
        </p>
      </div>

      <template #footer>
        <BaseButton variant="secondary" @click="closeCancelModal">
          Mantieni Generazione
        </BaseButton>
        <BaseButton variant="warning" @click="cancelGeneration">
          Annulla Generazione
        </BaseButton>
      </template>
    </BaseModal>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useGameStore } from '@/js/stores/game'
import { useNotificationStore } from '@/js/stores/notifications'
import api from '@/js/services/api'
import { formatCurrency, formatDate } from '@/js/utils/helpers'
import StatusBadge from '@/js/components/shared/StatusBadge.vue'

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
    // Fetch recent projects (assuming there's an endpoint for this)
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

    // Refresh data
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

    // Refresh data
    await Promise.all([fetchSalesPeople(), fetchGeneratedProjects()])

    // Update game state
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

    // Refresh data
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

<style scoped>
.sales-view {
  @apply min-h-full;
}

/* Page Header */
.sales-header {
  @apply flex flex-col lg:flex-row lg:items-center lg:justify-between;
  @apply mb-6 lg:mb-8;
}

.header-content {
  @apply mb-4 lg:mb-0;
}

.page-title {
  @apply text-2xl lg:text-3xl font-bold text-neutral-900;
  @apply flex items-center;
}

.title-icon {
  @apply text-3xl lg:text-4xl mr-3;
}

.page-subtitle {
  @apply text-neutral-600 mt-1;
}

.header-stats {
  @apply flex space-x-4;
}

.stat-item {
  @apply flex items-center space-x-2 bg-white rounded-lg px-3 py-2;
  @apply border border-neutral-200;
}

.stat-icon {
  @apply text-lg;
}

.stat-content {
  @apply flex flex-col;
}

.stat-value {
  @apply text-lg font-bold text-neutral-900;
}

.stat-label {
  @apply text-xs text-neutral-500;
}

/* Sales Grid */
.sales-grid {
  @apply grid gap-6;
  @apply grid-cols-1 xl:grid-cols-12;
}

.sales-people-section {
  @apply xl:col-span-7;
}

.generated-projects-section {
  @apply xl:col-span-5;
}

/* Sales People List */
.sales-people-list {
  @apply space-y-4;
}

.sales-person-card {
  @apply bg-neutral-50 rounded-lg p-4 border;
  @apply transition-all duration-200;
}

.sales-person-card--available {
  @apply border-success-200 bg-success-50;
}

.sales-person-card--busy {
  @apply border-warning-200 bg-warning-50;
}

.sales-person-header {
  @apply flex items-start justify-between mb-3;
}

.sales-person-info {
  @apply flex-1;
}

.sales-person-name {
  @apply font-semibold text-neutral-900 mb-1;
}

.sales-person-experience {
  @apply flex items-center space-x-2;
}

.experience-stars {
  @apply text-blue-500;
}

.experience-name {
  @apply text-sm text-neutral-600;
}

.sales-person-details {
  @apply space-y-1 mb-3;
}

.detail-item {
  @apply flex justify-between text-sm;
}

.detail-label {
  @apply text-neutral-600;
}

.detail-value {
  @apply text-neutral-900 font-medium;
}

.generation-capabilities {
  @apply bg-white rounded-md p-3 border border-neutral-200 mb-3;
}

.capabilities-title {
  @apply text-sm font-medium text-neutral-700 mb-2;
}

.capabilities-grid {
  @apply grid grid-cols-2 gap-2;
}

.capability-item {
  @apply flex flex-col;
}

.capability-label {
  @apply text-xs text-neutral-600;
}

.capability-value {
  @apply text-sm font-medium text-neutral-900;
}

.current-generation {
  @apply bg-blue-50 rounded-md p-3 border border-blue-200 mb-3;
}

.current-generation-title {
  @apply text-sm font-medium text-blue-700 mb-2;
}

.generation-info {
  @apply space-y-3;
}

.generation-details {
  @apply space-y-1;
}

.generation-progress {
  @apply space-y-2;
}

.progress-header {
  @apply flex items-center justify-between;
}

.progress-label {
  @apply text-sm font-medium text-neutral-700;
}

.progress-percentage {
  @apply text-sm font-bold text-blue-600;
}

.progress-bar {
  @apply w-full h-2 bg-neutral-200 rounded-full overflow-hidden;
}

.progress-fill {
  @apply h-full transition-all duration-500 ease-out;
}

.progress-fill--sales {
  @apply bg-blue-500;
}

.progress-time {
  @apply text-sm;
}

.completion-ready {
  @apply text-success-600 font-medium;
}

.time-remaining {
  @apply text-neutral-600;
}

.generation-actions {
  @apply mt-2;
}

.sales-person-actions {
  @apply flex space-x-2;
}

/* Generated Projects List */
.generated-projects-list {
  @apply space-y-4;
}

.generated-project-card {
  @apply bg-white rounded-lg p-4 border;
  @apply transition-all duration-200;
}

.generated-project-card--pending {
  @apply border-neutral-200 hover:border-brand-300;
}

.generated-project-card--assigned {
  @apply border-blue-200 bg-blue-50;
}

.generated-project-card--completed {
  @apply border-success-200 bg-success-50;
}

.project-header {
  @apply flex items-start justify-between mb-3;
}

.project-info {
  @apply flex-1;
}

.project-name {
  @apply font-semibold text-neutral-900 mb-1;
}

.project-meta {
  @apply flex items-center space-x-2;
}

.project-complexity {
  @apply text-sm text-neutral-600;
}

.project-value {
  @apply text-lg font-bold text-success-600;
}

.project-details {
  @apply space-y-1 mb-3;
}

.project-actions {
  @apply flex space-x-2;
}

/* Statistics Grid */
.stats-grid {
  @apply grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4;
}

.stat-card {
  @apply bg-neutral-50 rounded-lg p-4 border border-neutral-200;
}

.stat-header {
  @apply flex items-center mb-2;
}

.stat-card .stat-icon {
  @apply text-lg mr-2;
}

.stat-card .stat-label {
  @apply text-sm font-medium text-neutral-600;
}

.stat-card .stat-value {
  @apply text-xl font-bold text-neutral-900 mb-1;
}

.stat-description {
  @apply text-xs text-neutral-500;
}

/* Empty States */
.empty-state {
  @apply text-center py-8;
}

.empty-icon {
  @apply text-4xl mb-4;
}

.empty-title {
  @apply text-lg font-semibold text-neutral-900 mb-2;
}

.empty-description {
  @apply text-neutral-600 mb-4;
}

/* Modal Content */
.cancel-message {
  @apply mb-3;
}

.cancel-warning {
  @apply text-sm text-warning-600 bg-warning-50 rounded p-3;
}

/* Responsive */
@media (max-width: 1279px) {
  .sales-grid {
    @apply grid-cols-1 gap-4;
  }
}

@media (max-width: 640px) {
  .sales-header {
    @apply mb-4;
  }

  .page-title {
    @apply text-xl;
  }

  .title-icon {
    @apply text-2xl mr-2;
  }

  .header-stats {
    @apply grid grid-cols-3 gap-2;
  }

  .stat-item {
    @apply px-2 py-1;
  }

  .stat-value {
    @apply text-base;
  }

  .sales-people-list,
  .generated-projects-list {
    @apply space-y-3;
  }

  .sales-person-card,
  .generated-project-card {
    @apply p-3;
  }

  .capabilities-grid {
    @apply grid-cols-1 gap-1;
  }

  .stats-grid {
    @apply grid-cols-1 gap-3;
  }

  .sales-person-actions,
  .project-actions {
    @apply flex-col space-y-2 space-x-0;
  }
}

/* Animations */
.sales-person-card,
.generated-project-card {
  animation: fadeInUp 0.3s ease-out;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.progress-fill--sales {
  animation: progressGrow 1s ease-out;
}

@keyframes progressGrow {
  from {
    width: 0%;
  }
}

/* Hover effects */
.generated-project-card:hover {
  @apply shadow-md transform -translate-y-0.5;
}

.stat-card:hover {
  @apply shadow-sm;
}

/* Loading states */
.loading .sales-person-card,
.loading .generated-project-card,
.loading .stat-card {
  @apply animate-pulse;
}

/* Focus states */
.sales-person-card:focus-within,
.generated-project-card:focus-within {
  @apply ring-2 ring-brand-500 ring-offset-2;
}

/* Status-specific styling */
.sales-person-card--available .sales-person-actions button {
  @apply shadow-md hover:shadow-lg;
}

.sales-person-card--busy .current-generation {
  animation: pulseGlow 2s ease-in-out infinite;
}

@keyframes pulseGlow {
  0%, 100% {
    box-shadow: 0 0 5px rgba(59, 130, 246, 0.3);
  }
  50% {
    box-shadow: 0 0 15px rgba(59, 130, 246, 0.5);
  }
}

/* Accessibility improvements */
.sales-person-card[role="button"]:focus,
.generated-project-card[role="button"]:focus {
  @apply outline-none ring-2 ring-brand-500 ring-offset-2;
}

/* Print styles */
@media print {
  .sales-person-actions,
  .project-actions,
  .generation-actions {
    @apply hidden;
  }
}
</style>