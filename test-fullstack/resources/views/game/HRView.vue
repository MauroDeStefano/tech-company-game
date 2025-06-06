<template>
  <div class="hr-view">
    <!-- Page Header -->
    <div class="page-header">
      <div class="header-content">
        <h1 class="page-title">
          <span class="title-icon">🧑‍💼</span>
          Risorse Umane
        </h1>
        <p class="page-subtitle">
          Assumi nuovo personale per far crescere la tua software house
        </p>
      </div>

      <div class="header-stats">
        <div class="header-stat">
          <span class="stat-icon">💰</span>
          <div class="stat-content">
            <span class="stat-value" :class="moneyColorClass">
              {{ formatCurrency(gameStore.currentGame?.money || 0) }}
            </span>
            <span class="stat-label">Budget Disponibile</span>
          </div>
        </div>

        <div class="header-stat">
          <span class="stat-icon">👥</span>
          <div class="stat-content">
            <span class="stat-value">{{ currentTeamSize }}</span>
            <span class="stat-label">Team Attuale</span>
          </div>
        </div>

        <div class="header-stat">
          <span class="stat-icon">📊</span>
          <div class="stat-content">
            <span class="stat-value">{{ formatCurrency(monthlyCosts) }}</span>
            <span class="stat-label">Costi Mensili</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Current Team Overview -->
    <div class="current-team-section">
      <BaseCard
        title="Il Tuo Team Attuale"
        icon="👥"
        class="team-overview-card"
      >
        <div class="team-grid">
          <!-- Developers -->
          <div class="team-category">
            <h3 class="category-title">
              <span class="category-icon">👨‍💻</span>
              Sviluppatori ({{ currentDevelopers.length }})
            </h3>
            <div v-if="currentDevelopers.length === 0" class="empty-category">
              <p class="empty-text">Nessuno sviluppatore nel team</p>
            </div>
            <div v-else class="team-members">
              <div
                v-for="developer in currentDevelopers"
                :key="developer.id"
                class="team-member"
              >
                <div class="member-info">
                  <span class="member-name">{{ developer.name }}</span>
                  <div class="member-details">
                    <span class="member-seniority">
                      {{ getSeniorityText(developer.seniority) }}
                    </span>
                    <span class="member-salary">
                      {{ formatCurrency(developer.monthly_salary) }}/mese
                    </span>
                  </div>
                </div>
                <StatusBadge
                  :status="developer.is_busy ? 'busy' : 'available'"
                  size="sm"
                />
              </div>
            </div>
          </div>

          <!-- Sales People -->
          <div class="team-category">
            <h3 class="category-title">
              <span class="category-icon">💼</span>
              Commerciali ({{ currentSalesPeople.length }})
            </h3>
            <div v-if="currentSalesPeople.length === 0" class="empty-category">
              <p class="empty-text">Nessun commerciale nel team</p>
            </div>
            <div v-else class="team-members">
              <div
                v-for="salesPerson in currentSalesPeople"
                :key="salesPerson.id"
                class="team-member"
              >
                <div class="member-info">
                  <span class="member-name">{{ salesPerson.name }}</span>
                  <div class="member-details">
                    <span class="member-experience">
                      {{ getExperienceText(salesPerson.experience) }}
                    </span>
                    <span class="member-salary">
                      {{ formatCurrency(salesPerson.monthly_salary) }}/mese
                    </span>
                  </div>
                </div>
                <StatusBadge
                  :status="salesPerson.is_busy ? 'busy' : 'available'"
                  size="sm"
                />
              </div>
            </div>
          </div>
        </div>
      </BaseCard>
    </div>

    <!-- Market Sections -->
    <div class="market-sections">
      <!-- Developers Market -->
      <section class="market-section">
        <div class="section-header">
          <h2 class="section-title">
            <span class="section-icon">👨‍💻</span>
            Sviluppatori Disponibili
          </h2>
          <div class="section-filters">
            <select v-model="developerFilter" class="filter-select">
              <option value="all">Tutti i livelli</option>
              <option value="1">Junior</option>
              <option value="2">Junior-Mid</option>
              <option value="3">Mid</option>
              <option value="4">Senior</option>
              <option value="5">Lead</option>
            </select>
          </div>
        </div>

        <div v-if="loadingDevelopers" class="loading-grid">
          <div v-for="i in 4" :key="i" class="candidate-card-skeleton">
            <div class="skeleton-header"></div>
            <div class="skeleton-content">
              <div class="skeleton-line"></div>
              <div class="skeleton-line short"></div>
            </div>
          </div>
        </div>

        <div v-else-if="filteredDevelopers.length === 0" class="empty-market">
          <div class="empty-icon">😔</div>
          <p class="empty-text">Nessuno sviluppatore disponibile</p>
        </div>

        <div v-else class="candidates-grid">
          <div
            v-for="developer in filteredDevelopers"
            :key="developer.id || developer.name"
            class="candidate-card"
            :class="{ 'candidate-card--disabled': !canAfford(developer.hiring_cost) }"
          >
            <div class="candidate-header">
              <h3 class="candidate-name">{{ developer.name }}</h3>
              <div class="candidate-level">
                <span class="level-stars">{{ '⭐'.repeat(developer.seniority) }}</span>
                <span class="level-text">{{ getSeniorityText(developer.seniority) }}</span>
              </div>
            </div>

            <div class="candidate-info">
              <div v-if="developer.specialization" class="candidate-specialization">
                <span class="spec-icon">🎯</span>
                <span class="spec-text">{{ getSpecializationText(developer.specialization) }}</span>
              </div>

              <div class="candidate-costs">
                <div class="cost-item">
                  <span class="cost-label">Costo Assunzione</span>
                  <span class="cost-value" :class="{ 'cost-value--expensive': !canAfford(developer.hiring_cost) }">
                    {{ formatCurrency(developer.hiring_cost) }}
                  </span>
                </div>
                <div class="cost-item">
                  <span class="cost-label">Stipendio Mensile</span>
                  <span class="cost-value">{{ formatCurrency(developer.monthly_salary) }}</span>
                </div>
              </div>

              <div v-if="developer.skills && developer.skills.length > 0" class="candidate-skills">
                <span class="skills-label">Competenze:</span>
                <div class="skills-list">
                  <span
                    v-for="skill in developer.skills.slice(0, 3)"
                    :key="skill"
                    class="skill-tag"
                  >
                    {{ skill }}
                  </span>
                  <span v-if="developer.skills.length > 3" class="skill-more">
                    +{{ developer.skills.length - 3 }}
                  </span>
                </div>
              </div>
            </div>

            <div class="candidate-actions">
              <BaseButton
                v-if="canAfford(developer.hiring_cost)"
                variant="primary"
                size="sm"
                @click="hireDeveloper(developer)"
                :loading="hiringId === `dev-${developer.id || developer.name}`"
                class="hire-btn"
              >
                Assumi
              </BaseButton>
              <BaseButton
                v-else
                variant="secondary"
                size="sm"
                disabled
                class="hire-btn"
              >
                Budget Insufficiente
              </BaseButton>
            </div>
          </div>
        </div>
      </section>

      <!-- Sales People Market -->
      <section class="market-section">
        <div class="section-header">
          <h2 class="section-title">
            <span class="section-icon">💼</span>
            Commerciali Disponibili
          </h2>
          <div class="section-filters">
            <select v-model="salesFilter" class="filter-select">
              <option value="all">Tutti i livelli</option>
              <option value="1">Trainee</option>
              <option value="2">Junior</option>
              <option value="3">Mid</option>
              <option value="4">Senior</option>
              <option value="5">Manager</option>
            </select>
          </div>
        </div>

        <div v-if="loadingSalesPeople" class="loading-grid">
          <div v-for="i in 4" :key="i" class="candidate-card-skeleton">
            <div class="skeleton-header"></div>
            <div class="skeleton-content">
              <div class="skeleton-line"></div>
              <div class="skeleton-line short"></div>
            </div>
          </div>
        </div>

        <div v-else-if="filteredSalesPeople.length === 0" class="empty-market">
          <div class="empty-icon">😔</div>
          <p class="empty-text">Nessun commerciale disponibile</p>
        </div>

        <div v-else class="candidates-grid">
          <div
            v-for="salesPerson in filteredSalesPeople"
            :key="salesPerson.id || salesPerson.name"
            class="candidate-card"
            :class="{ 'candidate-card--disabled': !canAfford(salesPerson.hiring_cost) }"
          >
            <div class="candidate-header">
              <h3 class="candidate-name">{{ salesPerson.name }}</h3>
              <div class="candidate-level">
                <span class="level-stars">{{ '⭐'.repeat(salesPerson.experience) }}</span>
                <span class="level-text">{{ getExperienceText(salesPerson.experience) }}</span>
              </div>
            </div>

            <div class="candidate-info">
              <div v-if="salesPerson.specialization" class="candidate-specialization">
                <span class="spec-icon">🎯</span>
                <span class="spec-text">{{ getSalesSpecializationText(salesPerson.specialization) }}</span>
              </div>

              <div class="candidate-costs">
                <div class="cost-item">
                  <span class="cost-label">Costo Assunzione</span>
                  <span class="cost-value" :class="{ 'cost-value--expensive': !canAfford(salesPerson.hiring_cost) }">
                    {{ formatCurrency(salesPerson.hiring_cost) }}
                  </span>
                </div>
                <div class="cost-item">
                  <span class="cost-label">Stipendio Mensile</span>
                  <span class="cost-value">{{ formatCurrency(salesPerson.monthly_salary) }}</span>
                </div>
              </div>

              <div class="candidate-performance">
                <div class="performance-item">
                  <span class="perf-label">Tempo Generazione</span>
                  <span class="perf-value">{{ salesPerson.estimated_generation_time }}min</span>
                </div>
                <div class="performance-item">
                  <span class="perf-label">Valore Progetti</span>
                  <span class="perf-value">{{ formatCurrency(salesPerson.estimated_project_value) }}</span>
                </div>
              </div>
            </div>

            <div class="candidate-actions">
              <BaseButton
                v-if="canAfford(salesPerson.hiring_cost)"
                variant="success"
                size="sm"
                @click="hireSalesPerson(salesPerson)"
                :loading="hiringId === `sales-${salesPerson.id || salesPerson.name}`"
                class="hire-btn"
              >
                Assumi
              </BaseButton>
              <BaseButton
                v-else
                variant="secondary"
                size="sm"
                disabled
                class="hire-btn"
              >
                Budget Insufficiente
              </BaseButton>
            </div>
          </div>
        </div>
      </section>
    </div>

    <!-- Budget Warning -->
    <div v-if="isLowBudget" class="budget-warning">
      <div class="warning-content">
        <span class="warning-icon">⚠️</span>
        <div class="warning-text">
          <strong>Budget limitato!</strong>
          Completa progetti per aumentare il patrimonio e assumere nuovo personale.
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
import StatusBadge from '@/js/components/shared/StatusBadge.vue'

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

// Computed properties
const currentDevelopers = computed(() => {
  return gameStore.currentGame?.developers || []
})

const currentSalesPeople = computed(() => {
  return gameStore.currentGame?.sales_people || []
})

const currentTeamSize = computed(() => {
  return currentDevelopers.value.length + currentSalesPeople.value.length
})

const monthlyCosts = computed(() => {
  return gameStore.currentGame?.monthly_costs || 0
})

const moneyColorClass = computed(() => {
  const money = gameStore.currentGame?.money || 0
  if (money < 0) return 'text-danger-600'
  if (money < 2000) return 'text-warning-600'
  return 'text-success-600'
})

const isLowBudget = computed(() => {
  return (gameStore.currentGame?.money || 0) < 3000
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
  return (gameStore.currentGame?.money || 0) >= cost
}

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

    // Update game money (should be done by API)
    if (gameStore.currentGame) {
      gameStore.currentGame.money -= developer.hiring_cost
    }

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

    // Update game money (should be done by API)
    if (gameStore.currentGame) {
      gameStore.currentGame.money -= salesPerson.hiring_cost
    }

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

// Lifecycle
onMounted(() => {
  loadMarketDevelopers()
  loadMarketSalesPeople()
})
</script>

<style scoped>
.hr-view {
  @apply space-y-8;
}

/* Page Header */
.page-header {
  @apply flex flex-col lg:flex-row lg:items-start lg:justify-between;
  @apply mb-8;
}

.header-content {
  @apply mb-6 lg:mb-0;
}

.page-title {
  @apply text-2xl sm:text-3xl font-bold text-neutral-900;
  @apply flex items-center;
}

.title-icon {
  @apply text-3xl sm:text-4xl mr-3;
}

.page-subtitle {
  @apply text-neutral-600 mt-1;
}

.header-stats {
  @apply grid grid-cols-1 sm:grid-cols-3 gap-4;
}

.header-stat {
  @apply bg-white rounded-lg border border-neutral-200 p-4;
  @apply flex items-center space-x-3;
}

.stat-icon {
  @apply text-2xl;
}

.stat-content {
  @apply flex flex-col;
}

.stat-value {
  @apply text-lg font-bold text-neutral-900;
}

.stat-label {
  @apply text-sm text-neutral-600;
}

/* Current Team */
.current-team-section {
  @apply mb-8;
}

.team-grid {
  @apply grid grid-cols-1 lg:grid-cols-2 gap-6;
}

.team-category {
  @apply space-y-4;
}

.category-title {
  @apply text-lg font-semibold text-neutral-900;
  @apply flex items-center;
}

.category-icon {
  @apply text-xl mr-2;
}

.empty-category {
  @apply text-center py-6 bg-neutral-50 rounded-lg;
}

.empty-text {
  @apply text-neutral-500;
}

.team-members {
  @apply space-y-3;
}

.team-member {
  @apply flex items-center justify-between p-3;
  @apply bg-neutral-50 rounded-lg border border-neutral-200;
}

.member-info {
  @apply flex-1;
}

.member-name {
  @apply font-medium text-neutral-900 block;
}

.member-details {
  @apply flex items-center space-x-4 mt-1;
}

.member-seniority,
.member-experience {
  @apply text-sm text-neutral-600;
}

.member-salary {
  @apply text-sm text-neutral-500;
}

/* Market Sections */
.market-sections {
  @apply space-y-8;
}

.market-section {
  @apply bg-white rounded-lg border border-neutral-200 p-6;
}

.section-header {
  @apply flex flex-col sm:flex-row sm:items-center sm:justify-between;
  @apply mb-6;
}

.section-title {
  @apply text-xl font-semibold text-neutral-900;
  @apply flex items-center mb-4 sm:mb-0;
}

.section-icon {
  @apply text-2xl mr-2;
}

.section-filters {
  @apply flex items-center space-x-3;
}

.filter-select {
  @apply px-3 py-2 border border-neutral-300 rounded-md;
  @apply bg-white text-neutral-900;
  @apply focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500;
}

/* Loading and Empty States */
.loading-grid {
  @apply grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4;
}

.candidate-card-skeleton {
  @apply bg-neutral-50 rounded-lg border border-neutral-200 p-4;
  @apply animate-pulse;
}

.skeleton-header {
  @apply h-4 bg-neutral-300 rounded mb-3;
}

.skeleton-content {
  @apply space-y-2;
}

.skeleton-line {
  @apply h-3 bg-neutral-200 rounded;
}

.skeleton-line.short {
  @apply w-2/3;
}

.empty-market {
  @apply text-center py-12;
}

.empty-icon {
  @apply text-4xl mb-3;
}

/* Candidates Grid */
.candidates-grid {
  @apply grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4;
}

.candidate-card {
  @apply bg-white rounded-lg border border-neutral-200 p-4;
  @apply transition-all duration-200 hover:shadow-md hover:border-brand-300;
}

.candidate-card--disabled {
  @apply opacity-60 hover:shadow-none hover:border-neutral-200;
}

.candidate-header {
  @apply mb-4;
}

.candidate-name {
  @apply font-semibold text-neutral-900 mb-2;
}

.candidate-level {
  @apply flex items-center space-x-2;
}

.level-stars {
  @apply text-yellow-500;
}

.level-text {
  @apply text-sm text-neutral-600 font-medium;
}

.candidate-info {
  @apply space-y-3 mb-4;
}

.candidate-specialization {
  @apply flex items-center space-x-2;
  @apply bg-brand-50 text-brand-700 px-2 py-1 rounded;
}

.spec-icon {
  @apply text-sm;
}

.spec-text {
  @apply text-sm font-medium;
}

.candidate-costs,
.candidate-performance {
  @apply space-y-2;
}

.cost-item,
.performance-item {
  @apply flex items-center justify-between;
}

.cost-label,
.perf-label {
  @apply text-sm text-neutral-600;
}

.cost-value,
.perf-value {
  @apply text-sm font-medium text-neutral-900;
}

.cost-value--expensive {
  @apply text-danger-600;
}

.candidate-skills {
  @apply space-y-1;
}

.skills-label {
  @apply text-sm font-medium text-neutral-700;
}

.skills-list {
  @apply flex flex-wrap gap-1;
}

.skill-tag {
  @apply text-xs bg-neutral-100 text-neutral-700 px-2 py-1 rounded;
}

.skill-more {
  @apply text-xs text-neutral-500;
}

.candidate-actions {
  @apply pt-3 border-t border-neutral-200;
}

.hire-btn {
  @apply w-full;
}

/* Budget Warning */
.budget-warning {
  @apply bg-warning-50 border border-warning-200 rounded-lg p-4;
}

.warning-content {
  @apply flex items-start space-x-3;
}

.warning-icon {
  @apply text-xl text-warning-600 flex-shrink-0;
}

.warning-text {
  @apply text-warning-800;
}

/* Responsive */
@media (max-width: 768px) {
  .page-title {
    @apply text-xl;
  }

  .title-icon {
    @apply text-2xl mr-2;
  }

  .header-stats {
    @apply grid-cols-1 gap-3;
  }

  .team-grid {
    @apply grid-cols-1;
  }

  .candidates-grid {
    @apply grid-cols-1 gap-3;
  }

  .section-header {
    @apply flex-col;
  }
}

/* Animations */
.candidate-card {
  animation: slideUp 0.3s ease-out;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Loading animation for skeletons */
.candidate-card-skeleton .skeleton-header,
.candidate-card-skeleton .skeleton-line {
  background: linear-gradient(90deg, #f3f4f6 25%, #e5e7eb 50%, #f3f4f6 75%);
  background-size: 200% 100%;
  animation: loading 1.5s infinite;
}

@keyframes loading {
  0% {
    background-position: 200% 0;
  }
  100% {
    background-position: -200% 0;
  }
}

/* Focus states for accessibility */
.candidate-card:focus {
  @apply outline-none ring-2 ring-brand-500 ring-offset-2;
}

.filter-select:focus {
  @apply outline-none ring-2 ring-brand-500 ring-offset-2;
}

/* Hover effects */
.team-member:hover {
  @apply bg-neutral-100;
}

.candidate-card:not(.candidate-card--disabled):hover {
  @apply transform -translate-y-1;
}
</style>