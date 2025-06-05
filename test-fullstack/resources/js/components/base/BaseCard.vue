<template>
  <component
    :is="tag"
    :class="cardClasses"
    :to="to"
    v-bind="$attrs"
    @click="handleClick"
  >
    <!-- Card Header -->
    <header v-if="hasHeader" class="card-header" :class="headerClasses">
      <div class="card-header-content">
        <!-- Title Section -->
        <div v-if="title || $slots.title" class="card-title-section">
          <div class="card-title-wrapper">
            <!-- Icon -->
            <span v-if="icon" class="card-icon" :class="iconColorClass">
              {{ icon }}
            </span>

            <!-- Title -->
            <h3 v-if="title" class="card-title" :class="titleSizeClass">
              {{ title }}
            </h3>
            <slot v-else name="title" />

            <!-- Badge -->
            <span v-if="badge" class="card-badge" :class="badgeVariantClass">
              {{ badge }}
            </span>
          </div>

          <!-- Subtitle -->
          <p v-if="subtitle || $slots.subtitle" class="card-subtitle">
            <slot v-if="$slots.subtitle" name="subtitle" />
            <template v-else>{{ subtitle }}</template>
          </p>
        </div>

        <!-- Actions Section -->
        <div v-if="$slots.actions" class="card-actions">
          <slot name="actions" />
        </div>
      </div>
    </header>

    <!-- Card Body -->
    <main class="card-body" :class="bodyClasses">
      <!-- Loading State -->
      <div v-if="loading" class="card-loading">
        <div class="loading-spinner">
          <svg class="animate-spin h-8 w-8 text-brand-600" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
          </svg>
        </div>
        <p class="loading-text">{{ loadingText }}</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="empty" class="card-empty">
        <div class="empty-icon">
          {{ emptyIcon }}
        </div>
        <h4 class="empty-title">{{ emptyTitle }}</h4>
        <p class="empty-description">{{ emptyDescription }}</p>
        <div v-if="$slots.emptyActions" class="empty-actions">
          <slot name="emptyActions" />
        </div>
      </div>

      <!-- Main Content -->
      <div v-else class="card-content">
        <slot />
      </div>
    </main>

    <!-- Card Footer -->
    <footer v-if="$slots.footer" class="card-footer" :class="footerClasses">
      <slot name="footer" />
    </footer>

    <!-- Hover Overlay (for clickable cards) -->
    <div v-if="clickable && !disabled" class="card-overlay" />
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
  const classes = [
    'card',
    `card--${props.variant}`,
    `card--${props.size}`,
    `card--shadow-${props.shadow}`,
    `card--rounded-${props.rounded}`
  ]

  if (!props.border) classes.push('card--no-border')
  if (props.hover && !props.disabled) classes.push('card--hover')
  if (props.clickable && !props.disabled) classes.push('card--clickable')
  if (props.disabled) classes.push('card--disabled')
  if (props.loading) classes.push('card--loading')

  return classes.join(' ')
})

const headerClasses = computed(() => {
  const classes = [`card-header--padding-${props.padding}`]
  if (props.headerBorder) classes.push('card-header--border')
  return classes.join(' ')
})

const bodyClasses = computed(() => {
  return `card-body--padding-${props.padding}`
})

const footerClasses = computed(() => {
  const classes = [`card-footer--padding-${props.padding}`]
  if (props.footerBorder) classes.push('card-footer--border')
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
    default: 'text-neutral-500',
    primary: 'text-brand-600',
    secondary: 'text-secondary-600',
    success: 'text-success-600',
    warning: 'text-warning-600',
    danger: 'text-danger-600'
  }
  return colorMap[props.variant] || colorMap.default
})

const badgeVariantClass = computed(() => {
  const variantMap = {
    default: 'bg-neutral-100 text-neutral-800',
    primary: 'bg-brand-100 text-brand-800',
    secondary: 'bg-secondary-100 text-secondary-800',
    success: 'bg-success-100 text-success-800',
    warning: 'bg-warning-100 text-warning-800',
    danger: 'bg-danger-100 text-danger-800'
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

<style scoped>
/* Base Card Styles */
.card {
  @apply relative bg-white overflow-hidden;
  @apply transition-all duration-200 ease-in-out;
}

/* Variants */
.card--default {
  @apply bg-white;
}

.card--primary {
  @apply bg-brand-50 border-brand-200;
}

.card--secondary {
  @apply bg-secondary-50 border-secondary-200;
}

.card--success {
  @apply bg-success-50 border-success-200;
}

.card--warning {
  @apply bg-warning-50 border-warning-200;
}

.card--danger {
  @apply bg-danger-50 border-danger-200;
}

/* Sizes */
.card--sm {
  @apply text-sm;
}

.card--md {
  @apply text-base;
}

.card--lg {
  @apply text-lg;
}

.card--xl {
  @apply text-xl;
}

/* Shadows */
.card--shadow-none {
  @apply shadow-none;
}

.card--shadow-sm {
  @apply shadow-sm;
}

.card--shadow-md {
  @apply shadow-md;
}

.card--shadow-lg {
  @apply shadow-lg;
}

.card--shadow-xl {
  @apply shadow-xl;
}

/* Rounded */
.card--rounded-none {
  @apply rounded-none;
}

.card--rounded-sm {
  @apply rounded-sm;
}

.card--rounded-md {
  @apply rounded-md;
}

.card--rounded-lg {
  @apply rounded-lg;
}

.card--rounded-xl {
  @apply rounded-xl;
}

.card--rounded-full {
  @apply rounded-full;
}

/* Border */
.card {
  @apply border border-neutral-200;
}

.card--no-border {
  @apply border-0;
}

/* Interactive States */
.card--hover:hover {
  @apply shadow-lg transform -translate-y-1;
}

.card--clickable {
  @apply cursor-pointer focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2;
  text-decoration: none; /* For router-link */
}

.card--clickable:hover .card-overlay {
  @apply opacity-5;
}

.card--disabled {
  @apply opacity-60 cursor-not-allowed;
}

.card--loading {
  @apply pointer-events-none;
}

/* Header */
.card-header {
  @apply bg-neutral-50;
}

.card-header--border {
  @apply border-b border-neutral-200;
}

.card-header--padding-none {
  @apply p-0;
}

.card-header--padding-sm {
  @apply p-3;
}

.card-header--padding-normal {
  @apply p-4;
}

.card-header--padding-lg {
  @apply p-6;
}

.card-header-content {
  @apply flex items-start justify-between;
}

.card-title-section {
  @apply flex-1 min-w-0;
}

.card-title-wrapper {
  @apply flex items-center;
}

.card-icon {
  @apply text-xl mr-3 flex-shrink-0;
}

.card-title {
  @apply font-semibold text-neutral-900 flex-1 min-w-0 truncate;
}

.card-badge {
  @apply ml-2 px-2 py-1 text-xs font-medium rounded-full flex-shrink-0;
}

.card-subtitle {
  @apply mt-1 text-sm text-neutral-600;
}

.card-actions {
  @apply flex items-center space-x-2 ml-4 flex-shrink-0;
}

/* Body */
.card-body {
  @apply relative;
}

.card-body--padding-none {
  @apply p-0;
}

.card-body--padding-sm {
  @apply p-3;
}

.card-body--padding-normal {
  @apply p-4;
}

.card-body--padding-lg {
  @apply p-6;
}

.card-content {
  @apply space-y-3;
}

/* Footer */
.card-footer {
  @apply bg-neutral-50;
}

.card-footer--border {
  @apply border-t border-neutral-200;
}

.card-footer--padding-none {
  @apply p-0;
}

.card-footer--padding-sm {
  @apply p-3;
}

.card-footer--padding-normal {
  @apply p-4;
}

.card-footer--padding-lg {
  @apply p-6;
}

/* Loading State */
.card-loading {
  @apply flex flex-col items-center justify-center py-12;
}

.loading-spinner {
  @apply mb-4;
}

.loading-text {
  @apply text-neutral-600 text-sm;
}

/* Empty State */
.card-empty {
  @apply text-center py-12;
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

.empty-actions {
  @apply flex justify-center;
}

/* Overlay for clickable cards */
.card-overlay {
  @apply absolute inset-0 bg-brand-500 opacity-0 transition-opacity duration-200;
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .card-header-content {
    @apply flex-col space-y-3;
  }

  .card-actions {
    @apply ml-0 w-full justify-end;
  }

  .card-icon {
    @apply text-lg mr-2;
  }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
  .card {
    @apply bg-neutral-900 border-neutral-700;
  }

  .card-header {
    @apply bg-neutral-800 border-neutral-700;
  }

  .card-footer {
    @apply bg-neutral-800 border-neutral-700;
  }

  .card-title {
    @apply text-neutral-100;
  }

  .card-subtitle {
    @apply text-neutral-400;
  }

  .loading-text {
    @apply text-neutral-400;
  }

  .empty-title {
    @apply text-neutral-100;
  }

  .empty-description {
    @apply text-neutral-400;
  }
}

/* Print styles */
@media print {
  .card {
    @apply shadow-none border border-black;
  }

  .card--hover:hover {
    @apply transform-none shadow-none;
  }
}

/* Accessibility improvements */
.card--clickable[role="button"] {
  @apply focus:ring-2 focus:ring-brand-500 focus:ring-offset-2;
}

.card--disabled [tabindex] {
  @apply pointer-events-none;
}

/* Animation for loading state */
.card--loading .card-loading {
  animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

/* Hover effect for non-clickable cards */
.card--hover:not(.card--clickable):hover {
  @apply shadow-md;
}
</style>