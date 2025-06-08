<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
    @click="$emit('close')"
  >
    <div
      class="bg-white rounded-2xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto"
      @click.stop
    >
      <!-- Header -->
      <div class="p-6 border-b border-gray-200 bg-red-50 rounded-t-2xl">
        <div class="text-center">
          <div class="mb-4">
            <span class="text-6xl">💀</span>
          </div>
          <h2 class="text-2xl font-bold text-red-800 mb-2">Game Over</h2>
          <p class="text-red-600">La tua software house è fallita!</p>
        </div>
      </div>

      <!-- Game Over Content -->
      <div class="p-6">
        <div class="text-center mb-6">
          <h3 class="text-xl font-semibold text-gray-900 mb-2">Partita Terminata</h3>
          <p class="text-gray-600">
            La tua software house ha esaurito le risorse.
            Non preoccuparti, puoi sempre ricominciare!
          </p>
        </div>

        <!-- Game Stats -->
        <div class="space-y-4 mb-6">
          <div class="bg-gray-50 rounded-lg p-4">
            <div class="flex items-center justify-between">
              <span class="text-sm font-medium text-gray-700">Progetti Completati</span>
              <span class="text-lg font-bold text-gray-900">{{ gameStats.projectsCompleted || 0 }}</span>
            </div>
          </div>
          
          <div class="bg-gray-50 rounded-lg p-4">
            <div class="flex items-center justify-between">
              <span class="text-sm font-medium text-gray-700">Revenue Totale</span>
              <span class="text-lg font-bold text-green-600">{{ formatCurrency(gameStats.totalRevenue || 0) }}</span>
            </div>
          </div>
          
          <div class="bg-gray-50 rounded-lg p-4">
            <div class="flex items-center justify-between">
              <span class="text-sm font-medium text-gray-700">Tempo di Gioco</span>
              <span class="text-lg font-bold text-gray-900">{{ gameStats.playTime || '0 min' }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer Actions -->
      <div class="p-6 border-t border-gray-200 bg-gray-50 rounded-b-2xl">
        <div class="flex items-center gap-3">
          <button
            class="flex-1 px-4 py-3 border border-gray-300 hover:border-gray-400 hover:bg-gray-50 text-gray-700 font-semibold rounded-xl transition-colors duration-200"
            @click="$emit('new-game')"
          >
            Nuova Partita
          </button>

          <button
            class="flex-1 px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors duration-200"
            @click="$emit('restart')"
          >
            Riprova
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { formatCurrency } from '@/js/utils/helpers'

// Props
const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  gameData: {
    type: Object,
    default: () => ({})
  }
})

// Emits
const emits = defineEmits(['close', 'restart', 'new-game'])

// Computed
const gameStats = computed(() => {
  return {
    projectsCompleted: props.gameData?.projects_completed || 0,
    totalRevenue: props.gameData?.total_revenue || 0,
    playTime: props.gameData?.play_time || '0 min'
  }
})
</script>
