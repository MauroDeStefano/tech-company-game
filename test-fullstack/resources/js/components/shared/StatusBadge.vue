<template>
  <span
    class="status-badge"
    :class="badgeClasses"
    :title="tooltip"
  >
    <span v-if="showIcon" class="status-icon">{{ statusIcon }}</span>
    <span class="status-text">{{ statusText }}</span>
    <span v-if="showPulse && isActive" class="status-pulse"></span>
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  status: {
    type: String,
    required: true,
    validator: (value) => [
      'active', 'paused', 'game_over', 'pending', 'in_progress',
      'completed', 'failed', 'cancelled', 'available', 'busy',
      'success', 'warning', 'danger', 'info'
    ].includes(value)
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'outlined', 'minimal'].includes(value)
  },
  showIcon: {
    type: Boolean,
    default: false
  },
  showPulse: {
    type: Boolean,
    default: false
  },
  customText: {
    type: String,
    default: null
  },
  customIcon: {
    type: String,
    default: null
  }
})

// Status configuration
const statusConfig = {
  // Game states
  active: {
    text: 'Attivo',
    icon: '🟢',
    color: 'success',
    tooltip: 'Il gioco è attualmente in corso'
  },
  paused: {
    text: 'In Pausa',
    icon: '⏸️',
    color: 'warning',
    tooltip: 'Il gioco è in pausa'
  },
  game_over: {
    text: 'Game Over',
    icon: '💀',
    color: 'danger',
    tooltip: 'La partita è terminata'
  },

  // Project states
  pending: {
    text: 'In Attesa',
    icon: '⏳',
    color: 'neutral',
    tooltip: 'In attesa di assegnazione'
  },
  in_progress: {
    text: 'In Corso',
    icon: '⚡',
    color: 'primary',
    tooltip: 'Progetto in sviluppo'
  },
  completed: {
    text: 'Completato',
    icon: '✅',
    color: 'success',
    tooltip: 'Progetto completato con successo'
  },
  failed: {
    text: 'Fallito',
    icon: '❌',
    color: 'danger',
    tooltip: 'Progetto fallito'
  },
  cancelled: {
    text: 'Cancellato',
    icon: '🚫',
    color: 'neutral',
    tooltip: 'Progetto cancellato'
  },

  // Person states
  available: {
    text: 'Disponibile',
    icon: '✅',
    color: 'success',
    tooltip: 'Disponibile per nuovi progetti'
  },
  busy: {
    text: 'Occupato',
    icon: '⏱️',
    color: 'warning',
    tooltip: 'Attualmente occupato'
  },

  // Generic states
  success: {
    text: 'Successo',
    icon: '✅',
    color: 'success',
    tooltip: 'Operazione completata con successo'
  },
  warning: {
    text: 'Attenzione',
    icon: '⚠️',
    color: 'warning',
    tooltip: 'Richiede attenzione'
  },
  danger: {
    text: 'Errore',
    icon: '❌',
    color: 'danger',
    tooltip: 'Si è verificato un errore'
  },
  info: {
    text: 'Info',
    icon: 'ℹ️',
    color: 'info',
    tooltip: 'Informazione'
  }
}

// Computed properties
const config = computed(() => statusConfig[props.status] || statusConfig.info)

const statusText = computed(() => props.customText || config.value.text)
const statusIcon = computed(() => props.customIcon || config.value.icon)
const tooltip = computed(() => config.value.tooltip)

const isActive = computed(() => ['active', 'in_progress'].includes(props.status))

const badgeClasses = computed(() => {
  const classes = [
    'status-badge--base',
    `status-badge--${props.size}`,
    `status-badge--${props.variant}`,
    `status-badge--${config.value.color}`
  ]

  if (isActive.value && props.showPulse) {
    classes.push('status-badge--pulsing')
  }

  return classes.join(' ')
})
</script>

<style scoped>
/* Base styles */
.status-badge {
  @apply inline-flex items-center justify-center;
  @apply font-medium rounded-full;
  @apply transition-all duration-200 ease-in-out;
  @apply relative;
}

.status-badge--base {
  @apply px-2 py-1;
}

/* Sizes */
.status-badge--sm {
  @apply text-xs px-2 py-0.5;
}

.status-badge--md {
  @apply text-sm px-2.5 py-1;
}

.status-badge--lg {
  @apply text-base px-3 py-1.5;
}

/* Variants */
.status-badge--default {
  /* Will be colored by color classes */
}

.status-badge--outlined {
  @apply bg-transparent border-2;
}

.status-badge--minimal {
  @apply bg-transparent;
}

/* Colors - Default variant */
.status-badge--default.status-badge--success {
  @apply bg-success-100 text-success-800 border border-success-200;
}

.status-badge--default.status-badge--warning {
  @apply bg-warning-100 text-warning-800 border border-warning-200;
}

.status-badge--default.status-badge--danger {
  @apply bg-danger-100 text-danger-800 border border-danger-200;
}

.status-badge--default.status-badge--primary {
  @apply bg-brand-100 text-brand-800 border border-brand-200;
}

.status-badge--default.status-badge--neutral {
  @apply bg-neutral-100 text-neutral-800 border border-neutral-200;
}

.status-badge--default.status-badge--info {
  @apply bg-blue-100 text-blue-800 border border-blue-200;
}

/* Colors - Outlined variant */
.status-badge--outlined.status-badge--success {
  @apply text-success-700 border-success-400;
}

.status-badge--outlined.status-badge--warning {
  @apply text-warning-700 border-warning-400;
}

.status-badge--outlined.status-badge--danger {
  @apply text-danger-700 border-danger-400;
}

.status-badge--outlined.status-badge--primary {
  @apply text-brand-700 border-brand-400;
}

.status-badge--outlined.status-badge--neutral {
  @apply text-neutral-700 border-neutral-400;
}

.status-badge--outlined.status-badge--info {
  @apply text-blue-700 border-blue-400;
}

/* Colors - Minimal variant */
.status-badge--minimal.status-badge--success {
  @apply text-success-600;
}

.status-badge--minimal.status-badge--warning {
  @apply text-warning-600;
}

.status-badge--minimal.status-badge--danger {
  @apply text-danger-600;
}

.status-badge--minimal.status-badge--primary {
  @apply text-brand-600;
}

.status-badge--minimal.status-badge--neutral {
  @apply text-neutral-600;
}

.status-badge--minimal.status-badge--info {
  @apply text-blue-600;
}

/* Content */
.status-icon {
  @apply mr-1;
}

.status-text {
  @apply whitespace-nowrap;
}

/* Pulse animation */
.status-pulse {
  @apply absolute -inset-1 rounded-full opacity-75;
  @apply animate-ping;
}

.status-badge--success .status-pulse {
  @apply bg-success-400;
}

.status-badge--primary .status-pulse {
  @apply bg-brand-400;
}

.status-badge--pulsing {
  @apply animate-pulse;
}

/* Hover effects */
.status-badge:hover {
  @apply transform scale-105;
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
  .status-badge--default.status-badge--success {
    @apply bg-success-900 text-success-200 border-success-700;
  }

  .status-badge--default.status-badge--warning {
    @apply bg-warning-900 text-warning-200 border-warning-700;
  }

  .status-badge--default.status-badge--danger {
    @apply bg-danger-900 text-danger-200 border-danger-700;
  }

  .status-badge--default.status-badge--primary {
    @apply bg-brand-900 text-brand-200 border-brand-700;
  }

  .status-badge--default.status-badge--neutral {
    @apply bg-neutral-800 text-neutral-200 border-neutral-600;
  }

  .status-badge--default.status-badge--info {
    @apply bg-blue-900 text-blue-200 border-blue-700;
  }
}

/* Accessibility */
.status-badge {
  @apply cursor-default;
}

/* Animation keyframes */
@keyframes pulse-glow {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

.status-badge--pulsing {
  animation: pulse-glow 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Size adjustments for icons */
.status-badge--sm .status-icon {
  @apply text-xs mr-0.5;
}

.status-badge--lg .status-icon {
  @apply text-lg mr-1.5;
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .status-badge--lg {
    @apply text-sm px-2.5 py-1;
  }

  .status-badge--md {
    @apply text-xs px-2 py-0.5;
  }
}

/* Focus states for accessibility */
.status-badge:focus {
  @apply outline-none ring-2 ring-offset-2;
}

.status-badge--success:focus {
  @apply ring-success-500;
}

.status-badge--warning:focus {
  @apply ring-warning-500;
}

.status-badge--danger:focus {
  @apply ring-danger-500;
}

.status-badge--primary:focus {
  @apply ring-brand-500;
}

.status-badge--neutral:focus {
  @apply ring-neutral-500;
}

.status-badge--info:focus {
  @apply ring-blue-500;
}
</style>