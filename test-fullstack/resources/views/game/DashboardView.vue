<!-- src/views/game/DashboardView.vue -->
<template>
  <div class="dashboard-view">
    <!-- Page Header -->
    <div class="dashboard-header">
      <div class="header-content">
        <h1 class="page-title">
          <span class="title-icon">🏠</span>
          Dashboard
        </h1>
        <p class="page-subtitle">
          Panoramica della tua software house
        </p>
      </div>

      <div class="header-actions">
        <BaseButton
          variant="primary"
          icon="🚀"
          @click="goToProduction"
        >
          Nuova Assegnazione
        </BaseButton>
      </div>
    </div>

    <!-- Main Dashboard Grid -->
    <div class="dashboard-grid">
      <!-- Stats Overview -->
      <section class="dashboard-section dashboard-section--stats">
        <GameStatsOverview />
      </section>

      <!-- Quick Actions -->
      <section class="dashboard-section dashboard-section--actions">
        <QuickActionsCard />
      </section>

      <!-- Active Projects -->
      <section class="dashboard-section dashboard-section--projects">
        <ActiveProjectsCard />
      </section>

      <!-- Team Status -->
      <section class="dashboard-section dashboard-section--team">
        <TeamStatusCard />
      </section>

      <!-- Recent Activity -->
      <section class="dashboard-section dashboard-section--activity">
        <RecentActivityCard />
      </section>

      <!-- Financial Summary -->
      <section class="dashboard-section dashboard-section--financial">
        <FinancialSummaryCard />
      </section>
    </div>

    <!-- Mobile Quick Actions -->
    <div class="mobile-quick-actions">
      <BaseButton
        v-for="action in mobileQuickActions"
        :key="action.name"
        :variant="action.variant"
        :icon="action.icon"
        size="sm"
        @click="action.action"
        class="quick-action-btn"
      >
        {{ action.label }}
      </BaseButton>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useGameStore } from '@/stores/game'
import { useNotificationStore } from '@/stores/notifications'

// Components
import GameStatsOverview from '@/components/game/dashboard/GameStatsOverview.vue'
import QuickActionsCard from '@/components/game/dashboard/QuickActionsCard.vue'
import ActiveProjectsCard from '@/components/game/dashboard/ActiveProjectsCard.vue'
import TeamStatusCard from '@/components/game/dashboard/TeamStatusCard.vue'
import RecentActivityCard from '@/components/game/dashboard/RecentActivityCard.vue'
import FinancialSummaryCard from '@/components/game/dashboard/FinancialSummaryCard.vue'

// Stores
const gameStore = useGameStore()
const notificationStore = useNotificationStore()
const router = useRouter()

// Mobile quick actions
const mobileQuickActions = computed(() => [
  {
    name: 'assign-project',
    label: 'Assegna',
    icon: '🚀',
    variant: 'primary',
    action: goToProduction
  },
  {
    name: 'hire-person',
    label: 'Assumi',
    icon: '👥',
    variant: 'secondary',
    action: goToHR
  },
  {
    name: 'generate-project',
    label: 'Genera',
    icon: '💼',
    variant: 'success',
    action: goToSales
  }
])

// Methods
const goToProduction = () => {
  router.push({ name: 'Production' })
}

const goToHR = () => {
  router.push({ name: 'HR' })
}

const goToSales = () => {
  router.push({ name: 'Sales' })
}
</script>

<style scoped>
.dashboard-view {
  @apply min-h-full;
}

/* Page Header */
.dashboard-header {
  @apply flex flex-col sm:flex-row sm:items-center sm:justify-between;
  @apply mb-6 sm:mb-8;
}

.header-content {
  @apply mb-4 sm:mb-0;
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

.header-actions {
  @apply flex items-center space-x-3;
}

/* Dashboard Grid */
.dashboard-grid {
  @apply grid gap-6;
  @apply grid-cols-1 lg:grid-cols-12;
  @apply auto-rows-min;
}

.dashboard-section {
  @apply min-h-0; /* Prevent grid items from stretching */
}

/* Grid Layout */
.dashboard-section--stats {
  @apply lg:col-span-8;
}

.dashboard-section--actions {
  @apply lg:col-span-4;
}

.dashboard-section--projects {
  @apply lg:col-span-8;
}

.dashboard-section--team {
  @apply lg:col-span-4;
}

.dashboard-section--activity {
  @apply lg:col-span-6;
}

.dashboard-section--financial {
  @apply lg:col-span-6;
}

/* Mobile Quick Actions */
.mobile-quick-actions {
  @apply fixed bottom-20 left-4 right-4 z-20;
  @apply flex space-x-2 lg:hidden;
}

.quick-action-btn {
  @apply flex-1;
}

/* Responsive adjustments */
@media (max-width: 1023px) {
  .dashboard-grid {
    @apply grid-cols-1 gap-4;
  }

  .dashboard-section {
    @apply col-span-1;
  }
}

@media (max-width: 640px) {
  .dashboard-header {
    @apply mb-4;
  }

  .page-title {
    @apply text-xl;
  }

  .title-icon {
    @apply text-2xl mr-2;
  }

  .header-actions {
    @apply flex-col space-y-2 space-x-0;
  }

  .dashboard-grid {
    @apply gap-3;
  }
}

/* Loading states */
.dashboard-section.loading {
  @apply animate-pulse;
}

/* Smooth transitions for layout changes */
.dashboard-grid {
  @apply transition-all duration-300 ease-in-out;
}

/* Focus management */
.dashboard-view:focus-within .dashboard-section {
  @apply transition-opacity duration-200;
}

.dashboard-view:focus-within .dashboard-section:not(:focus-within) {
  @apply opacity-75;
}
</style>