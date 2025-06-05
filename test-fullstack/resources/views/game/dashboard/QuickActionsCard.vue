<!-- src/components/game/dashboard/QuickActionsCard.vue -->
<template>
  <BaseCard
    title="Azioni Rapide"
    icon="⚡"
    class="quick-actions-card"
  >
    <div class="actions-grid">
      <!-- Assegna Progetto -->
      <div class="action-item" :class="{ 'action-item--disabled': !canAssignProject }">
        <button
          class="action-button"
          :disabled="!canAssignProject"
          @click="assignProject"
        >
          <div class="action-icon">🚀</div>
          <div class="action-content">
            <div class="action-title">Assegna Progetto</div>
            <div class="action-description">
              {{ assignProjectDescription }}
            </div>
          </div>
          <div v-if="pendingProjectsCount > 0" class="action-badge">
            {{ pendingProjectsCount }}
          </div>
        </button>
      </div>
    </div>

    <!-- Quick Stats -->
    <div class="quick-stats">
      <div class="quick-stat">
        <span class="quick-stat-icon">⏱️</span>
        <div class="quick-stat-content">
          <span class="quick-stat-label">Progetti Attivi</span>
          <span class="quick-stat-value">{{ activeProjectsCount }}</span>
        </div>
      </div>

      <div class="quick-stat">
        <span class="quick-stat-icon">👨‍💼</span>
        <div class="quick-stat-content">
          <span class="quick-stat-label">Team Disponibile</span>
          <span class="quick-stat-value">{{ availableTeamCount }}</span>
        </div>
      </div>
    </div>

    <!-- Smart Suggestions -->
    <div v-if="suggestions.length > 0" class="suggestions-section">
      <h4 class="suggestions-title">💡 Suggerimenti</h4>
      <div class="suggestions-list">
        <div
          v-for="suggestion in suggestions"
          :key="suggestion.id"
          class="suggestion-item"
          :class="`suggestion-item--${suggestion.priority}`"
          @click="handleSuggestion(suggestion)"
        >
          <span class="suggestion-icon">{{ suggestion.icon }}</span>
          <div class="suggestion-content">
            <span class="suggestion-text">{{ suggestion.text }}</span>
            <span v-if="suggestion.action" class="suggestion-action">{{ suggestion.actionText }}</span>
          </div>
          <span class="suggestion-arrow">→</span>
        </div>
      </div>
    </div>

    <!-- Hotkeys Info -->
    <div class="hotkeys-info">
      <div class="hotkeys-title">⌨️ Scorciatoie</div>
      <div class="hotkeys-list">
        <div class="hotkey-item">
          <kbd class="hotkey">Ctrl+2</kbd>
          <span class="hotkey-desc">Produzione</span>
        </div>
        <div class="hotkey-item">
          <kbd class="hotkey">Ctrl+3</kbd>
          <span class="hotkey-desc">Sales</span>
        </div>
        <div class="hotkey-item">
          <kbd class="hotkey">Ctrl+4</kbd>
          <span class="hotkey-desc">HR</span>
        </div>
        <div class="hotkey-item">
          <kbd class="hotkey">Ctrl+S</kbd>
          <span class="hotkey-desc">Salva</span>
        </div>
      </div>
    </div>
  </BaseCard>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useGameStore } from '@/stores/game'
import { useNotificationStore } from '@/stores/notifications'

// Stores
const gameStore = useGameStore()
const notificationStore = useNotificationStore()
const router = useRouter()

// Reactive state
const isSaving = ref(false)

// Computed properties
const pendingProjectsCount = computed(() => {
  return gameStore.currentGame?.projects?.filter(p => p.status === 'pending').length || 0
})

const availableDevelopersCount = computed(() => {
  return gameStore.currentGame?.developers?.filter(d => !d.is_busy).length || 0
})

const availableSalesCount = computed(() => {
  return gameStore.currentGame?.sales_people?.filter(s => !s.is_busy).length || 0
})

const activeProjectsCount = computed(() => {
  return gameStore.currentGame?.projects?.filter(p => p.status === 'in_progress').length || 0
})

const availableTeamCount = computed(() => {
  return availableDevelopersCount.value + availableSalesCount.value
})

const canAssignProject = computed(() => {
  return pendingProjectsCount.value > 0 && availableDevelopersCount.value > 0
})

const canGenerateProject = computed(() => {
  return availableSalesCount.value > 0
})

const assignProjectDescription = computed(() => {
  if (pendingProjectsCount.value === 0) return 'Nessun progetto in attesa'
  if (availableDevelopersCount.value === 0) return 'Nessun sviluppatore disponibile'
  return `${pendingProjectsCount.value} progetti in attesa`
})

const generateProjectDescription = computed(() => {
  if (availableSalesCount.value === 0) return 'Nessun commerciale disponibile'
  return `${availableSalesCount.value} commerciali disponibili`
})

const saveDescription = computed(() => {
  if (isSaving.value) return 'Salvataggio in corso...'
  return 'Backup dei progressi'
})

// Smart Suggestions
const suggestions = computed(() => {
  const suggestions = []

  // Suggerisci di assegnare progetti se ci sono progetti pending e sviluppatori liberi
  if (canAssignProject.value) {
    suggestions.push({
      id: 'assign-projects',
      icon: '🎯',
      text: `Hai ${pendingProjectsCount.value} progetti da assegnare`,
      actionText: 'Assegna ora',
      priority: 'high',
      action: () => router.push({ name: 'Production' })
    })
  }

  // Suggerisci di generare progetti se ci sono commerciali liberi ma pochi progetti
  if (availableSalesCount.value > 0 && pendingProjectsCount.value < 2) {
    suggestions.push({
      id: 'generate-projects',
      icon: '💼',
      text: 'Genera nuovi progetti per mantenere il flusso di lavoro',
      actionText: 'Vai ai Sales',
      priority: 'medium',
      action: () => router.push({ name: 'Sales' })
    })
  }

  // Suggerisci di assumere se il team è troppo piccolo
  const totalTeam = (gameStore.currentGame?.developers?.length || 0) + (gameStore.currentGame?.sales_people?.length || 0)
  if (totalTeam < 4 && gameStore.currentGame?.money > 5000) {
    suggestions.push({
      id: 'hire-team',
      icon: '👥',
      text: 'Il tuo team è piccolo, considera di assumere nuovo personale',
      actionText: 'Assumi ora',
      priority: 'medium',
      action: () => router.push({ name: 'HR' })
    })
  }

  // Suggerisci di salvare se ci sono molte attività
  if (activeProjectsCount.value > 2) {
    suggestions.push({
      id: 'save-progress',
      icon: '💾',
      text: 'Molti progetti attivi, salva i progressi',
      actionText: 'Salva ora',
      priority: 'low',
      action: saveGame
    })
  }

  return suggestions.slice(0, 3) // Max 3 suggestions
})

// Methods
const assignProject = () => {
  if (!canAssignProject.value) return
  router.push({ name: 'Production' })
}

const generateProject = () => {
  if (!canGenerateProject.value) return
  router.push({ name: 'Sales' })
}

const hirePersonnel = () => {
  router.push({ name: 'HR' })
}

const saveGame = async () => {
  if (isSaving.value) return

  isSaving.value = true
  try {
    await gameStore.saveGame()
    notificationStore.success('Gioco salvato con successo!')
  } catch (error) {
    notificationStore.error('Errore nel salvataggio del gioco')
  } finally {
    isSaving.value = false
  }
}

const handleSuggestion = (suggestion) => {
  if (suggestion.action) {
    suggestion.action()
  }
}
</script>

<style scoped>
.quick-actions-card {
  @apply h-full;
}

/* Actions Grid */
.actions-grid {
  @apply grid grid-cols-1 gap-3 mb-6;
}

.action-item {
  @apply transition-all duration-200;
}

.action-item--disabled {
  @apply opacity-60;
}

.action-button {
  @apply w-full flex items-center p-4 rounded-lg;
  @apply bg-neutral-50 hover:bg-neutral-100 border border-neutral-200;
  @apply transition-all duration-200 group;
  @apply focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2;
  @apply disabled:cursor-not-allowed disabled:hover:bg-neutral-50;
}

.action-button:hover:not(:disabled) {
  @apply shadow-md transform -translate-y-0.5 border-brand-200;
}

.action-icon {
  @apply text-2xl mr-3 flex-shrink-0;
  @apply transition-transform duration-200 group-hover:scale-110;
}

.action-content {
  @apply flex-1 text-left;
}

.action-title {
  @apply font-semibold text-neutral-900 mb-1;
}

.action-description {
  @apply text-sm text-neutral-600;
}

.action-badge {
  @apply ml-2 px-2 py-1 bg-brand-100 text-brand-800;
  @apply text-xs font-bold rounded-full;
  @apply flex items-center justify-center min-w-[20px] h-5;
}

.action-badge--success {
  @apply bg-success-100 text-success-800;
}

.action-badge--info {
  @apply bg-blue-100 text-blue-800;
}

.badge-icon {
  @apply font-bold;
}

.saving-spinner {
  @apply text-brand-600;
}

/* Quick Stats */
.quick-stats {
  @apply grid grid-cols-2 gap-3 mb-6 p-4 bg-neutral-50 rounded-lg;
}

.quick-stat {
  @apply flex items-center space-x-2;
}

.quick-stat-icon {
  @apply text-lg;
}

.quick-stat-content {
  @apply flex flex-col;
}

.quick-stat-label {
  @apply text-xs text-neutral-600;
}

.quick-stat-value {
  @apply text-lg font-bold text-neutral-900;
}

/* Suggestions */
.suggestions-section {
  @apply mb-6;
}

.suggestions-title {
  @apply text-sm font-semibold text-neutral-700 mb-3;
}

.suggestions-list {
  @apply space-y-2;
}

.suggestion-item {
  @apply flex items-center p-3 rounded-lg cursor-pointer;
  @apply transition-all duration-200 hover:shadow-sm;
}

.suggestion-item--high {
  @apply bg-danger-50 text-danger-800 hover:bg-danger-100;
}

.suggestion-item--medium {
  @apply bg-warning-50 text-warning-800 hover:bg-warning-100;
}

.suggestion-item--low {
  @apply bg-blue-50 text-blue-800 hover:bg-blue-100;
}

.suggestion-icon {
  @apply mr-2 flex-shrink-0;
}

.suggestion-content {
  @apply flex-1;
}

.suggestion-text {
  @apply text-sm block;
}

.suggestion-action {
  @apply text-xs opacity-75 block mt-0.5;
}

.suggestion-arrow {
  @apply ml-2 opacity-50 transition-transform duration-200;
}

.suggestion-item:hover .suggestion-arrow {
  @apply transform translate-x-1;
}

/* Hotkeys */
.hotkeys-info {
  @apply border-t border-neutral-200 pt-4;
}

.hotkeys-title {
  @apply text-sm font-semibold text-neutral-700 mb-3;
}

.hotkeys-list {
  @apply grid grid-cols-2 gap-2;
}

.hotkey-item {
  @apply flex items-center justify-between;
}

.hotkey {
  @apply px-2 py-1 bg-neutral-200 text-neutral-700 rounded text-xs font-mono;
}

.hotkey-desc {
  @apply text-xs text-neutral-600;
}

/* Responsive */
@media (max-width: 640px) {
  .actions-grid {
    @apply gap-2;
  }

  .action-button {
    @apply p-3;
  }

  .action-icon {
    @apply text-xl mr-2;
  }

  .action-title {
    @apply text-sm;
  }

  .action-description {
    @apply text-xs;
  }

  .quick-stats {
    @apply grid-cols-1 gap-2 p-3;
  }

  .hotkeys-list {
    @apply grid-cols-1;
  }
}

/* Animation for new suggestions */
.suggestion-item {
  animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateX(-10px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

/* Focus states */
.action-button:focus {
  @apply ring-2 ring-brand-500 ring-offset-2;
}

.suggestion-item:focus {
  @apply outline-none ring-2 ring-offset-2;
}

.suggestion-item--high:focus {
  @apply ring-danger-500;
}

.suggestion-item--medium:focus {
  @apply ring-warning-500;
}

.suggestion-item--low:focus {
  @apply ring-blue-500;
}
</style>
