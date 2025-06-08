<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-white">
    <!-- Page Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h1 class="text-4xl font-bold text-gray-900 tracking-tight mb-2">
              <span class="text-2xl">🏠</span>
              Dashboard
            </h1>
            <p class="text-xl text-gray-600">
              Panoramica della tua software house
            </p>
          </div>

          <div>
            <button
              @click="goToProduction"
              class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors duration-200"
            >
              <span>🚀</span>
              Nuova Assegnazione
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Dashboard Grid -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Stats Overview -->
        <section class="lg:col-span-8">
          <GameStatsOverview />
        </section>

        <!-- Quick Actions -->
        <section class="lg:col-span-4">
          <QuickActionsCard />
        </section>

        <!-- Active Projects -->
        <section class="lg:col-span-8">
          <ActiveProjectsCard />
        </section>

        <!-- Team Status -->
        <section class="lg:col-span-4">
          <TeamStatusCard />
        </section>

        <!-- Recent Activity -->
        <section class="lg:col-span-6">
          <RecentActivityCard />
        </section>

        <!-- Financial Summary -->
        <section class="lg:col-span-6">
          <FinancialSummaryCard />
        </section>
      </div>
    </div>

    <!-- Mobile Quick Actions -->
    <div class="fixed bottom-4 left-4 right-4 lg:hidden">
      <div class="flex justify-center gap-3">
        <button
          v-for="action in mobileQuickActions"
          :key="action.name"
          @click="action.action"
          class="flex items-center gap-2 px-4 py-2 rounded-xl font-medium text-sm transition-colors duration-200"
          :class="{
            'bg-blue-600 hover:bg-blue-700 text-white': action.variant === 'primary',
            'bg-gray-600 hover:bg-gray-700 text-white': action.variant === 'secondary',
            'bg-green-600 hover:bg-green-700 text-white': action.variant === 'success'
          }"
        >
          <span>{{ action.icon }}</span>
          {{ action.label }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useGameStore } from '@/js/stores/game'
import { useNotificationStore } from '@/js/stores/notifications'

// Components
import GameStatsOverview from '@/views/game/dashboard/GameStatsOverview.vue'
import QuickActionsCard from '@/views/game/dashboard/QuickActionsCard.vue'
import ActiveProjectsCard from '@/views/game/dashboard/ActiveProjectsCard.vue'
import TeamStatusCard from '@/views/game/dashboard/TeamStatusCard.vue'
import RecentActivityCard from '@/views/game/dashboard/RecentActivityCard.vue'
import FinancialSummaryCard from '@/views/game/dashboard/FinancialSummaryCard.vue'

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