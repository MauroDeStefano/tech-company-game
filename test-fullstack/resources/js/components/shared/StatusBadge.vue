<template>
  <span
    class="inline-flex items-center gap-1 font-medium rounded-full transition-all duration-200"
    :class="badgeClasses"
    :title="tooltip"
  >
    <span v-if="showIcon" class="flex-shrink-0">{{ statusIcon }}</span>
    <span>{{ statusText }}</span>
    <span 
      v-if="showPulse && isActive" 
      class="w-2 h-2 rounded-full animate-pulse"
      :class="pulseColorClass"
    ></span>
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

const sizeClasses = computed(() => {
  const sizes = {
    sm: 'px-2 py-1 text-xs',
    md: 'px-3 py-1.5 text-sm',
    lg: 'px-4 py-2 text-base'
  }
  return sizes[props.size] || sizes.md
})

const colorClasses = computed(() => {
  const colors = {
    success: {
      default: 'bg-green-100 text-green-800 border border-green-200',
      outlined: 'bg-transparent text-green-600 border-2 border-green-500',
      minimal: 'bg-transparent text-green-600'
    },
    warning: {
      default: 'bg-yellow-100 text-yellow-800 border border-yellow-200',
      outlined: 'bg-transparent text-yellow-600 border-2 border-yellow-500',
      minimal: 'bg-transparent text-yellow-600'
    },
    danger: {
      default: 'bg-red-100 text-red-800 border border-red-200',
      outlined: 'bg-transparent text-red-600 border-2 border-red-500',
      minimal: 'bg-transparent text-red-600'
    },
    info: {
      default: 'bg-blue-100 text-blue-800 border border-blue-200',
      outlined: 'bg-transparent text-blue-600 border-2 border-blue-500',
      minimal: 'bg-transparent text-blue-600'
    },
    primary: {
      default: 'bg-purple-100 text-purple-800 border border-purple-200',
      outlined: 'bg-transparent text-purple-600 border-2 border-purple-500',
      minimal: 'bg-transparent text-purple-600'
    },
    neutral: {
      default: 'bg-gray-100 text-gray-800 border border-gray-200',
      outlined: 'bg-transparent text-gray-600 border-2 border-gray-500',
      minimal: 'bg-transparent text-gray-600'
    }
  }
  
  const colorSet = colors[config.value.color] || colors.info
  return colorSet[props.variant] || colorSet.default
})

const pulseColorClass = computed(() => {
  const pulseColors = {
    success: 'bg-green-500',
    warning: 'bg-yellow-500',
    danger: 'bg-red-500',
    info: 'bg-blue-500',
    primary: 'bg-purple-500',
    neutral: 'bg-gray-500'
  }
  return pulseColors[config.value.color] || pulseColors.info
})

const badgeClasses = computed(() => {
  return [
    sizeClasses.value,
    colorClasses.value,
    isActive.value && props.showPulse ? 'animate-pulse' : ''
  ].filter(Boolean).join(' ')
})
</script>
