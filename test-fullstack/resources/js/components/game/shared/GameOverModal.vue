<template>
  <BaseModal
    :is-open="isOpen"
    :persistent="true"
    variant="danger"
    size="md"
    title="Game Over"
    icon="💀"
    subtitle="La tua software house è fallita!"
    @close="$emit('close')"
  >
    <!-- Game Over Content -->
    <div class="game-over-content">
      <div class="game-over-icon">
        <span class="icon-skull">💀</span>
      </div>

      <div class="game-over-message">
        <h3 class="message-title">Partita Terminata</h3>
        <p class="message-text">
          La tua software house ha esaurito le risorse.
          Non preoccuparti, puoi sempre ricominciare!
        </p>
      </div>

      <!-- Game Stats -->
      <div class="game-stats">
        <div class="stat-item">
          <span class="stat-label">Progetti Completati</span>
          <span class="stat-value">{{ gameStats.projectsCompleted || 0 }}</span>
        </div>
        <div class="stat-item">
          <span class="stat-label">Revenue Totale</span>
          <span class="stat-value">{{ formatCurrency(gameStats.totalRevenue || 0) }}</span>
        </div>
        <div class="stat-item">
          <span class="stat-label">Tempo di Gioco</span>
          <span class="stat-value">{{ gameStats.playTime || '0 min' }}</span>
        </div>
      </div>
    </div>

    <!-- Footer Actions -->
    <template #footer>
      <div class="game-over-actions">
        <BaseButton
          variant="secondary"
          @click="$emit('new-game')"
        >
          Nuova Partita
        </BaseButton>

        <BaseButton
          variant="primary"
          @click="$emit('restart')"
        >
          Riprova
        </BaseButton>
      </div>
    </template>
  </BaseModal>
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

<style scoped>
.game-over-content {
  @apply text-center space-y-6;
}

.game-over-icon {
  @apply flex justify-center;
}

.icon-skull {
  @apply text-6xl;
}

.game-over-message {
  @apply space-y-2;
}

.message-title {
  @apply text-xl font-bold text-neutral-900;
}

.message-text {
  @apply text-neutral-600 leading-relaxed;
}

.game-stats {
  @apply grid grid-cols-1 gap-3 bg-neutral-50 rounded-lg p-4;
}

.stat-item {
  @apply flex justify-between items-center;
}

.stat-label {
  @apply text-sm text-neutral-600;
}

.stat-value {
  @apply font-semibold text-neutral-900;
}

.game-over-actions {
  @apply flex justify-end space-x-3;
}

@media (max-width: 640px) {
  .game-over-actions {
    @apply flex-col space-y-2 space-x-0;
  }
}
</style>