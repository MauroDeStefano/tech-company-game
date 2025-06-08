<template>
  <component
    :is="tag"
    :class="cardClasses"
    :to="to"
    v-bind="$attrs"
    @click="handleClick"
  >
    <!-- Card Header -->
    <header v-if="hasHeader" class="border-b border-gray-200" :class="headerClasses">
      <div class="flex items-start justify-between">
        <!-- Title Section -->
        <div v-if="title || $slots.title" class="flex-1 min-w-0">
          <div class="flex items-center gap-3 mb-1">
            <!-- Icon -->
            <span v-if="icon" class="text-2xl" :class="iconColorClass">
              {{ icon }}
            </span>

            <!-- Title -->
            <h3 v-if="title" class="font-semibold text-gray-900" :class="titleSizeClass">
              {{ title }}
            </h3>
            <slot v-else name="title" />

            <!-- Badge -->
            <span v-if="badge" class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full" :class="badgeVariantClass">
              {{ badge }}
            </span>
          </div>

          <!-- Subtitle -->
          <p v-if="subtitle || $slots.subtitle" class="text-sm text-gray-600">
            <slot v-if="$slots.subtitle" name="subtitle" />
            <template v-else>{{ subtitle }}</template>
          </p>
        </div>

        <!-- Actions Section -->
        <div v-if="$slots.actions" class="flex items-center gap-2 ml-4">
          <slot name="actions" />
        </div>
      </div>
    </header>

    <!-- Card Body -->
    <main :class="bodyClasses">
      <!-- Loading State -->
      <div v-if="loading" class="flex flex-col items-center justify-center py-12">
        <div class="mb-4">
          <svg class="animate-spin h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
          </svg>
        </div>
        <p class="text-gray-600">{{ loadingText }}</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="empty" class="text-center py-12">
        <div class="text-4xl mb-4">
          {{ emptyIcon }}
        </div>
        <h4 class="text-lg font-medium text-gray-900 mb-2">{{ emptyTitle }}</h4>
        <p class="text-gray-600 mb-4">{{ emptyDescription }}</p>
        <div v-if="$slots.emptyActions">
          <slot name="emptyActions" />
        </div>
      </div>

      <!-- Main Content -->
      <div v-else>
        <slot />
      </div>
    </main>

    <!-- Card Footer -->
    <footer v-if="$slots.footer" class="border-t border-gray-200 bg-gray-50" :class="footerClasses">
      <slot name="footer" />
    </footer>

    <!-- Hover Overlay (for clickable cards) -->
    <div v-if="clickable && !disabled" class="absolute inset-0 bg-blue-50 opacity-0 hover:opacity-10 transition-opacity duration-200 rounded-lg pointer-events-none" />
  </component>
</template>

<script setup>
import { computed } from 'vue'

// Props
const props = defineProps({
  // Content
  title: {
    type: String,
    default: null
  },
  subtitle: {
    type: String,
    default: null
  },
  icon: {
    type: String,
    default: null
  },
  badge: {
    type: [String, Number],
    default: null
  },

  // Visual variants
  variant: {
    type: String,
    default: 'default',
    validator: (value) => [
      'default', 'primary', 'secondary', 'success', 'warning', 'danger'
    ].includes(value)
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg', 'xl'].includes(value)
  },

  // States
  loading: {
    type: Boolean,
    default: false
  },
  loadingText: {
    type: String,
    default: 'Caricamento...'
  },
  empty: {
    type: Boolean,
    default: false
  },
  emptyIcon: {
    type: String,
    default: '📭'
  },
  emptyTitle: {
    type: String,
    default: 'Nessun elemento'
  },
  emptyDescription: {
    type: String,
    default: 'Non ci sono elementi da visualizzare.'
  },
  disabled: {
    type: Boolean,
    default: false
  },

  // Behavior
  clickable: {
    type: Boolean,
    default: false
  },
  to: {
    type: [String, Object],
    default: null
  },

  // Visual options
  shadow: {
    type: String,
    default: 'sm',
    validator: (value) => ['none', 'sm', 'md', 'lg', 'xl'].includes(value)
  },
  rounded: {
    type: String,
    default: 'md',
    validator: (value) => ['none', 'sm', 'md', 'lg', 'xl', 'full'].includes(value)
  },
  border: {
    type: Boolean,
    default: true
  },
  hover: {
    type: Boolean,
    default: true
  },

  // Layout
  padding: {
    type: String,
    default: 'normal',
    validator: (value) => ['none', 'sm', 'normal', 'lg'].includes(value)
  },
  headerBorder: {
    type: Boolean,
    default: true
  },
  footerBorder: {
    type: Boolean,
    default: true
  }
})

// Emits
const emit = defineEmits(['click'])

// Computed
const tag = computed(() => {
  if (props.to) return 'router-link'
  return props.clickable ? 'button' : 'div'
})

const hasHeader = computed(() => {
  return props.title || props.icon || props.badge ||
         props.$slots.title || props.$slots.subtitle || props.$slots.actions
})

const cardClasses = computed(() => {
  const baseClasses = ['bg-white relative transition-all duration-200']

  // Shadow
  const shadowClasses = {
    none: '',
    sm: 'shadow-sm',
    md: 'shadow-md',
    lg: 'shadow-lg',
    xl: 'shadow-xl'
  }
  if (props.shadow !== 'none') {
    baseClasses.push(shadowClasses[props.shadow])
  }

  // Rounded
  const roundedClasses = {
    none: '',
    sm: 'rounded-sm',
    md: 'rounded-lg',
    lg: 'rounded-xl',
    xl: 'rounded-2xl',
    full: 'rounded-full'
  }
  if (props.rounded !== 'none') {
    baseClasses.push(roundedClasses[props.rounded])
  }

  // Border
  if (props.border) {
    baseClasses.push('border border-gray-200')
  }

  // Variant colors
  const variantClasses = {
    default: '',
    primary: 'border-l-4 border-l-blue-500',
    secondary: 'border-l-4 border-l-gray-500',
    success: 'border-l-4 border-l-green-500',
    warning: 'border-l-4 border-l-yellow-500',
    danger: 'border-l-4 border-l-red-500'
  }
  if (props.variant !== 'default') {
    baseClasses.push(variantClasses[props.variant])
  }

  // Interactive states
  if (props.hover && !props.disabled && !props.loading) {
    baseClasses.push('hover:shadow-md')
  }

  if (props.clickable && !props.disabled && !props.loading) {
    baseClasses.push('cursor-pointer hover:scale-[1.02] active:scale-[0.98]')
  }

  if (props.disabled) {
    baseClasses.push('opacity-60 cursor-not-allowed')
  }

  if (props.loading) {
    baseClasses.push('pointer-events-none')
  }

  return baseClasses.join(' ')
})

const headerClasses = computed(() => {
  const paddingClasses = {
    none: '',
    sm: 'p-3',
    normal: 'p-4',
    lg: 'p-6'
  }
  
  const classes = [paddingClasses[props.padding]]
  
  if (!props.headerBorder) {
    classes.push('border-0')
  }
  
  return classes.join(' ')
})

const bodyClasses = computed(() => {
  const paddingClasses = {
    none: '',
    sm: 'p-3',
    normal: 'p-4',
    lg: 'p-6'
  }
  
  return paddingClasses[props.padding]
})

const footerClasses = computed(() => {
  const paddingClasses = {
    none: '',
    sm: 'p-3',
    normal: 'p-4',
    lg: 'p-6'
  }
  
  const classes = [paddingClasses[props.padding]]
  
  if (!props.footerBorder) {
    classes.push('border-0 bg-transparent')
  }
  
  return classes.join(' ')
})

const titleSizeClass = computed(() => {
  const sizeMap = {
    sm: 'text-sm',
    md: 'text-base',
    lg: 'text-lg',
    xl: 'text-xl'
  }
  return sizeMap[props.size] || sizeMap.md
})

const iconColorClass = computed(() => {
  const colorMap = {
    default: 'text-gray-500',
    primary: 'text-blue-600',
    secondary: 'text-gray-600',
    success: 'text-green-600',
    warning: 'text-yellow-600',
    danger: 'text-red-600'
  }
  return colorMap[props.variant] || colorMap.default
})

const badgeVariantClass = computed(() => {
  const variantMap = {
    default: 'bg-gray-100 text-gray-800',
    primary: 'bg-blue-100 text-blue-800',
    secondary: 'bg-gray-100 text-gray-800',
    success: 'bg-green-100 text-green-800',
    warning: 'bg-yellow-100 text-yellow-800',
    danger: 'bg-red-100 text-red-800'
  }
  return variantMap[props.variant] || variantMap.default
})

// Methods
const handleClick = (event) => {
  if (props.disabled || props.loading) {
    event.preventDefault()
    return
  }

  if (props.clickable) {
    emit('click', event)
  }
}
</script>