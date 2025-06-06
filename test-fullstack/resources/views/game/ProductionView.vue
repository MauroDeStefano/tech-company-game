<template>
  <div class="production-view">
    <!-- Page Header -->
    <div class="production-header">
      <div class="header-content">
        <h1 class="page-title">
          <span class="title-icon">🏗️</span>
          Produzione
        </h1>
        <p class="page-subtitle">
          Gestisci sviluppatori e progetti
        </p>
      </div>

      <div class="header-stats">
        <div class="stat-item">
          <span class="stat-icon">👨‍💻</span>
          <div class="stat-content">
            <span class="stat-value">{{ availableDevelopers }}</span>
            <span class="stat-label">Disponibili</span>
          </div>
        </div>
        <div class="stat-item">
          <span class="stat-icon">⏳</span>
          <div class="stat-content">
            <span class="stat-value">{{ pendingProjects }}</span>
            <span class="stat-label">In Attesa</span>
          </div>
        </div>
        <div class="stat-item">
          <span class="stat-icon">⚡</span>
          <div class="stat-content">
            <span class="stat-value">{{ activeProjects }}</span>
            <span class="stat-label">In Corso</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content Grid -->
    <div class="production-grid">
      <!-- Developers Section -->
      <section class="production-section developers-section">
        <BaseCard
          title="Sviluppatori"
          icon="👨‍💻"
          :loading="loading.developers"
        >
          <template #actions>
            <BaseButton
              variant="ghost"
              size="sm"
              icon="🔄"
              @click="refreshDevelopers"
              :disabled="loading.developers"
            >
              Aggiorna
            </BaseButton>
          </template>

          <div v-if="developers.length === 0" class="empty-state">
            <div class="empty-icon">👨‍💻</div>
            <h3 class="empty-title">Nessuno sviluppatore</h3>
            <p class="empty-description">
              Vai alla sezione HR per assumere il tuo primo sviluppatore
            </p>
            <BaseButton variant="primary" @click="goToHR">
              Assumi Sviluppatori
            </BaseButton>
          </div>

          <div v-else class="developers-list">
            <div
              v-for="developer in developers"
              :key="developer.id"
              class="developer-card"
              :class="{
                'developer-card--busy': developer.status.is_busy,
                'developer-card--available': developer.status.is_available
              }"
            >
              <div class="developer-header">
                <div class="developer-info">
                  <h3 class="developer-name">{{ developer.name }}</h3>
                  <div class="developer-seniority">
                    <span class="seniority-stars">{{ developer.seniority.stars }}</span>
                    <span class="seniority-name">{{ developer.seniority.name }}</span>
                  </div>
                </div>
                <StatusBadge
                  :status="developer.status.is_busy ? 'busy' : 'available'"
                  size="sm"
                />
              </div>

              <div class="developer-details">
                <div class="detail-item">
                  <span class="detail-label">Specializzazione:</span>
                  <span class="detail-value">{{ developer.specialization.name || 'Generica' }}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">Stipendio:</span>
                  <span class="detail-value">{{ developer.salary.formatted }}/mese</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">Progetti completati:</span>
                  <span class="detail-value">{{ developer.statistics.projects_completed }}</span>
                </div>
              </div>

              <!-- Current Project (if any) -->
              <div v-if="developer.current_project" class="current-project">
                <h4 class="current-project-title">Progetto Corrente:</h4>
                <div class="project-info">
                  <div class="project-name">{{ developer.current_project.name }}</div>
                  <div class="project-progress">
                    <div class="progress-bar">
                      <div
                        class="progress-fill"
                        :style="{ width: `${developer.current_project.timing.current_progress}%` }"
                      ></div>
                    </div>
                    <span class="progress-text">
                      {{ Math.round(developer.current_project.timing.current_progress) }}%
                    </span>
                  </div>
                  <div class="project-time-remaining">
                    {{ developer.current_project.timing.time_remaining_formatted }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </BaseCard>
      </section>

      <!-- Projects Section -->
      <section class="production-section projects-section">
        <!-- Pending Projects -->
        <BaseCard
          title="Progetti in Attesa"
          icon="⏳"
          :loading="loading.projects"
          class="mb-6"
        >
          <template #actions>
            <BaseButton
              variant="ghost"
              size="sm"
              icon="🔄"
              @click="refreshProjects"
              :disabled="loading.projects"
            >
              Aggiorna
            </BaseButton>
          </template>

          <div v-if="pendingProjectsList.length === 0" class="empty-state">
            <div class="empty-icon">📋</div>
            <h3 class="empty-title">Nessun progetto in attesa</h3>
            <p class="empty-description">
              Vai alla sezione Sales per generare nuovi progetti
            </p>
            <BaseButton variant="primary" @click="goToSales">
              Genera Progetti
            </BaseButton>
          </div>

          <div v-else class="projects-list">
            <div
              v-for="project in pendingProjectsList"
              :key="project.id"
              class="project-card project-card--pending"
            >
              <div class="project-header">
                <div class="project-info">
                  <h3 class="project-name">{{ project.name }}</h3>
                  <div class="project-complexity">
                    <span class="complexity-stars">{{ project.complexity.stars }}</span>
                    <span class="complexity-name">{{ project.complexity.name }}</span>
                  </div>
                </div>
                <div class="project-value">
                  {{ project.value.formatted }}
                </div>
              </div>

              <div class="project-details">
                <div class="detail-item">
                  <span class="detail-label">Complessità:</span>
                  <span class="detail-value">{{ project.complexity.level }}/5</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">Valore orario:</span>
                  <span class="detail-value">{{ project.value.hourly_rate }}€/h</span>
                </div>
                <div v-if="project.created_by_sales_person" class="detail-item">
                  <span class="detail-label">Generato da:</span>
                  <span class="detail-value">{{ project.created_by_sales_person.name }}</span>
                </div>
              </div>

              <div class="project-actions">
                <BaseButton
                  variant="primary"
                  size="sm"
                  :disabled="availableDevelopers === 0 || assigningProject === project.id"
                  @click="openAssignModal(project)"
                >
                  <span v-if="assigningProject === project.id">Assegnando...</span>
                  <span v-else>Assegna Sviluppatore</span>
                </BaseButton>
              </div>
            </div>
          </div>
        </BaseCard>

        <!-- Active Projects -->
        <BaseCard
          title="Progetti in Corso"
          icon="⚡"
          :loading="loading.projects"
        >
          <div v-if="activeProjectsList.length === 0" class="empty-state">
            <div class="empty-icon">⚡</div>
            <h3 class="empty-title">Nessun progetto in corso</h3>
            <p class="empty-description">
              Assegna progetti ai tuoi sviluppatori per iniziare
            </p>
          </div>

          <div v-else class="projects-list">
            <div
              v-for="project in activeProjectsList"
              :key="project.id"
              class="project-card project-card--active"
            >
              <div class="project-header">
                <div class="project-info">
                  <h3 class="project-name">{{ project.name }}</h3>
                  <div class="project-developer">
                    👨‍💻 {{ project.assignment.developer.name }}
                  </div>
                </div>
                <div class="project-value">
                  {{ project.value.formatted }}
                </div>
              </div>

              <div class="project-progress-section">
                <div class="progress-header">
                  <span class="progress-label">Progresso</span>
                  <span class="progress-percentage">
                    {{ Math.round(project.timing.current_progress) }}%
                  </span>
                </div>
                <div class="progress-bar">
                  <div
                    class="progress-fill"
                    :style="{ width: `${project.timing.current_progress}%` }"
                  ></div>
                </div>
                <div class="progress-time">
                  <span v-if="project.timing.is_ready_for_completion" class="completion-ready">
                    ✅ Pronto per il completamento
                  </span>
                  <span v-else class="time-remaining">
                    ⏱️ {{ project.timing.time_remaining_formatted }}
                  </span>
                </div>
              </div>

              <div class="project-actions">
                <BaseButton
                  v-if="project.timing.is_ready_for_completion"
                  variant="success"
                  size="sm"
                  :disabled="completingProject === project.id"
                  @click="completeProject(project)"
                >
                  <span v-if="completingProject === project.id">Completando...</span>
                  <span v-else>✅ Completa Progetto</span>
                </BaseButton>
                <BaseButton
                  variant="outline"
                  size="sm"
                  @click="openUnassignModal(project)"
                >
                  Riassegna
                </BaseButton>
              </div>
            </div>
          </div>
        </BaseCard>
      </section>
    </div>

    <!-- Assign Project Modal -->
    <BaseModal
      v-model:is-open="modals.assign"
      title="Assegna Sviluppatore"
      icon="🚀"
      size="md"
      :loading="assigningProject !== null"
    >
      <div v-if="selectedProject" class="assign-modal-content">
        <div class="project-summary">
          <h3 class="project-name">{{ selectedProject.name }}</h3>
          <div class="project-details">
            <div class="detail-row">
              <span class="detail-label">Complessità:</span>
              <span class="detail-value">{{ selectedProject.complexity.name }}</span>
            </div>
            <div class="detail-row">
              <span class="detail-label">Valore:</span>
              <span class="detail-value">{{ selectedProject.value.formatted }}</span>
            </div>
          </div>
        </div>

        <div class="developers-selection">
          <h4 class="selection-title">Seleziona Sviluppatore:</h4>
          <div class="developers-grid">
            <div
              v-for="developer in availableDevelopersList"
              :key="developer.id"
              class="developer-option"
              :class="{ 'developer-option--selected': selectedDeveloper?.id === developer.id }"
              @click="selectedDeveloper = developer"
            >
              <div class="developer-header">
                <span class="developer-name">{{ developer.name }}</span>
                <span class="developer-seniority">{{ developer.seniority.stars }}</span>
              </div>
              <div class="developer-stats">
                <div class="stat">
                  <span class="stat-label">Specializzazione:</span>
                  <span class="stat-value">{{ developer.specialization.name || 'Generica' }}</span>
                </div>
                <div class="stat">
                  <span class="stat-label">Progetti completati:</span>
                  <span class="stat-value">{{ developer.statistics.projects_completed }}</span>
                </div>
              </div>
              <div class="estimated-time">
                <span class="time-label">Tempo stimato:</span>
                <span class="time-value">{{ calculateEstimatedTime(selectedProject, developer) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <template #footer>
        <BaseButton variant="secondary" @click="closeAssignModal">
          Annulla
        </BaseButton>
        <BaseButton
          variant="primary"
          :disabled="!selectedDeveloper || assigningProject !== null"
          @click="assignProject"
        >
          <span v-if="assigningProject">Assegnando...</span>
          <span v-else>Assegna Progetto</span>
        </BaseButton>
      </template>
    </BaseModal>

    <!-- Unassign Project Modal -->
    <BaseModal
      v-model:is-open="modals.unassign"
      title="Riassegna Progetto"
      icon="🔄"
      variant="warning"
    >
      <div v-if="selectedProject">
        <p class="unassign-message">
          Sei sicuro di voler rimuovere <strong>{{ selectedProject.assignment?.developer?.name }}</strong>
          dal progetto <strong>{{ selectedProject.name }}</strong>?
        </p>
        <p class="unassign-warning">
          Il progetto tornerà in stato "In Attesa" e dovrai riassegnarlo manualmente.
        </p>
      </div>

      <template #footer>
        <BaseButton variant="secondary" @click="closeUnassignModal">
          Annulla
        </BaseButton>
        <BaseButton variant="warning" @click="unassignProject">
          Riassegna
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
import StatusBadge from '@/js/components/shared/StatusBadge.vue'

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
  return developers.value.filter(d => d.status.is_available).length
})

const pendingProjects = computed(() => pendingProjectsList.value.length)
const activeProjects = computed(() => activeProjectsList.value.length)

const availableDevelopersList = computed(() => {
  return developers.value.filter(d => d.status.is_available)
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

    notificationStore.success(`Progetto "${project.name}" completato! +${project.value.formatted}`)

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
  const baseTime = project.complexity.level * 30
  const seniorityMultiplier = 1 - (developer.seniority.level - 1) * 0.15
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
  router.push({ name: 'HR' })
}

const goToSales = () => {
  router.push({ name: 'Sales' })
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

<style scoped>
.production-view {
  @apply min-h-full;
}

/* Page Header */
.production-header {
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

/* Production Grid */
.production-grid {
  @apply grid gap-6;
  @apply grid-cols-1 xl:grid-cols-12;
}

.developers-section {
  @apply xl:col-span-5;
}

.projects-section {
  @apply xl:col-span-7;
}

/* Developers List */
.developers-list {
  @apply space-y-4;
}

.developer-card {
  @apply bg-neutral-50 rounded-lg p-4 border;
  @apply transition-all duration-200;
}

.developer-card--available {
  @apply border-success-200 bg-success-50;
}

.developer-card--busy {
  @apply border-warning-200 bg-warning-50;
}

.developer-header {
  @apply flex items-start justify-between mb-3;
}

.developer-info {
  @apply flex-1;
}

.developer-name {
  @apply font-semibold text-neutral-900 mb-1;
}

.developer-seniority {
  @apply flex items-center space-x-2;
}

.seniority-stars {
  @apply text-yellow-500;
}

.seniority-name {
  @apply text-sm text-neutral-600;
}

.developer-details {
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

.current-project {
  @apply bg-white rounded-md p-3 border border-neutral-200;
}

.current-project-title {
  @apply text-sm font-medium text-neutral-700 mb-2;
}

.project-info {
  @apply space-y-2;
}

.project-name {
  @apply font-medium text-neutral-900;
}

.project-progress {
  @apply flex items-center space-x-2;
}

.progress-bar {
  @apply flex-1 h-2 bg-neutral-200 rounded-full overflow-hidden;
}

.progress-fill {
  @apply h-full bg-brand-500 transition-all duration-500;
}

.progress-text {
  @apply text-xs font-medium text-neutral-600;
}

.project-time-remaining {
  @apply text-xs text-neutral-500;
}

/* Projects List */
.projects-list {
  @apply space-y-4;
}

.project-card {
  @apply bg-white rounded-lg p-4 border border-neutral-200;
  @apply transition-all duration-200;
}

.project-card--pending {
  @apply border-neutral-200 hover:border-brand-300 hover:shadow-md;
}

.project-card--active {
  @apply border-brand-200 bg-brand-50;
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

.project-complexity {
  @apply flex items-center space-x-2;
}

.complexity-stars {
  @apply text-purple-500;
}

.complexity-name {
  @apply text-sm text-neutral-600;
}

.project-developer {
  @apply text-sm text-neutral-600;
}

.project-value {
  @apply text-lg font-bold text-success-600;
}

.project-details {
  @apply space-y-1 mb-3;
}

.project-progress-section {
  @apply mb-4;
}

.progress-header {
  @apply flex items-center justify-between mb-2;
}

.progress-label {
  @apply text-sm font-medium text-neutral-700;
}

.progress-percentage {
  @apply text-sm font-bold text-brand-600;
}

.progress-time {
  @apply mt-2;
}

.completion-ready {
  @apply text-sm text-success-600 font-medium;
}

.time-remaining {
  @apply text-sm text-neutral-600;
}

.project-actions {
  @apply flex space-x-2;
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
.assign-modal-content {
  @apply space-y-6;
}

.project-summary {
  @apply bg-neutral-50 rounded-lg p-4;
}

.project-summary .project-name {
  @apply text-lg font-semibold text-neutral-900 mb-3;
}

.project-summary .project-details {
  @apply space-y-2;
}

.detail-row {
  @apply flex justify-between;
}

.developers-selection {
  /* Styles for developer selection */
}

.selection-title {
  @apply text-base font-semibold text-neutral-900 mb-3;
}

.developers-grid {
  @apply space-y-3;
}

.developer-option {
  @apply border border-neutral-200 rounded-lg p-3 cursor-pointer;
  @apply transition-all duration-200 hover:border-brand-300;
}

.developer-option--selected {
  @apply border-brand-500 bg-brand-50;
}

.developer-option .developer-header {
  @apply flex items-center justify-between mb-2;
}

.developer-option .developer-name {
  @apply font-medium text-neutral-900;
}

.developer-option .developer-seniority {
  @apply text-yellow-500;
}

.developer-option .developer-stats {
  @apply space-y-1 mb-2;
}

.developer-option .stat {
  @apply flex justify-between text-sm;
}

.developer-option .stat-label {
  @apply text-neutral-600;
}

.developer-option .stat-value {
  @apply text-neutral-900;
}

.estimated-time {
  @apply flex justify-between text-sm bg-neutral-100 rounded px-2 py-1;
}

.time-label {
  @apply text-neutral-600;
}

.time-value {
  @apply text-neutral-900 font-medium;
}

.unassign-message {
  @apply mb-3;
}

.unassign-warning {
  @apply text-sm text-warning-600 bg-warning-50 rounded p-3;
}

/* Responsive */
@media (max-width: 1279px) {
  .production-grid {
    @apply grid-cols-1 gap-4;
  }
}

@media (max-width: 640px) {
  .production-header {
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

  .developers-list,
  .projects-list {
    @apply space-y-3;
  }

  .developer-card,
  .project-card {
    @apply p-3;
  }

  .project-actions {
    @apply flex-col space-y-2 space-x-0;
  }
}

/* Animations */
.developer-card,
.project-card {
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

.progress-fill {
  animation: progressGrow 1s ease-out;
}

@keyframes progressGrow {
  from {
    width: 0%;
  }
}

/* Loading states */
.loading .developer-card,
.loading .project-card {
  @apply animate-pulse;
}

/* Focus states */
.developer-option:focus {
  @apply outline-none ring-2 ring-brand-500 ring-offset-2;
}

.project-card:focus-within {
  @apply ring-2 ring-brand-500 ring-offset-2;
}
</style>