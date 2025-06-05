<template>
  <component
    :is="tag"
    :class="buttonClasses"
    :disabled="disabled || loading"
    :type="type"
    :to="to"
    v-bind="$attrs"
    @click="handleClick"
  >
    <!-- Loading Spinner -->
    <svg
      v-if="loading"
      class="btn-spinner"
      :class="spinnerSizeClass"
      fill="none"
      viewBox="0 0 24 24"
    >
      <circle
        class="opacity-25"
        cx="12"
        cy="12"
        r="10"
        stroke="currentColor"
        stroke-width="4"
      />
      <path
        class="opacity-75"
        fill="currentColor"
        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
      />
    </svg>

    <!-- Icon (Left) -->
    <span
      v-if="icon && iconPosition === 'left' && !loading"
      class="btn-icon btn-icon--left"
      :class="iconSizeClass"
    >
      {{ icon }}
    </span>

    <!-- Button Content -->
    <span v-if="!loading || showTextOnLoading" class="btn-content">
      <slot>{{ text }}</slot>
    </span>

    <!-- Icon (Right) -->
    <span
      v-if="icon && iconPosition === 'right' && !loading"
      class="btn-icon btn-icon--right"
      :class="iconSizeClass"
    >
      {{ icon }}
    </span>

    <!-- Badge/Counter -->
    <span
      v-if="badge && !loading"
      class="btn-badge"
    >
      {{ badge }}
    </span>
  </component>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// Props
const props = defineProps({
  // Content
  text: {
    type: String,
    default: ''
  },
  icon: {
    type: String,
    default: null
  },
  iconPosition: {
    type: String,
    default: 'left',
    validator: (value) => ['left', 'right'].includes(value)
  },
  badge: {
    type: [String, Number],
    default: null
  },

  // Variants
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => [
      'primary', 'secondary', 'success', 'warning', 'danger',
      'outline', 'ghost', 'link'
    ].includes(value)
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl'].includes(value)
  },

  // States
  loading: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  },
  active: {
    type: Boolean,
    default: false
  },

  // Behavior
  type: {
    type: String,
    default: 'button',
    validator: (value) => ['button', 'submit', 'reset'].includes(value)
  },
  to: {
    type: [String, Object],
    default: null
  },
  replace: {
    type: Boolean,
    default: false
  },
  showTextOnLoading: {
    type: Boolean,
    default: false
  },

  // Visual options
  rounded: {
    type: Boolean,
    default: false
  },
  square: {
    type: Boolean,
    default: false
  },
  shadow: {
    type: Boolean,
    default: false
  },
  block: {
    type: Boolean,
    default: false
  }
})

// Emits
const emit = defineEmits(['click'])

// Computed
const tag = computed(() => {
  if (props.to) return 'router-link'
  return 'button'
})

const buttonClasses = computed(() => {
  const classes = ['btn', `btn--${props.variant}`, `btn--${props.size}`]

  if (props.loading) classes.push('btn--loading')
  if (props.disabled) classes.push('btn--disabled')
  if (props.active) classes.push('btn--active')
  if (props.rounded) classes.push('btn--rounded')
  if (props.square) classes.push('btn--square')
  if (props.shadow) classes.push('btn--shadow')
  if (props.block) classes.push('btn--block')
  if (props.icon && !props.text && !props.$slots.default) classes.push('btn--icon-only')

  return classes.join(' ')
})

const spinnerSizeClass = computed(() => {
  const sizeMap = {
    xs: 'w-3 h-3',
    sm: 'w-4 h-4',
    md: 'w-5 h-5',
    lg: 'w-6 h-6',
    xl: 'w-7 h-7'
  }
  return sizeMap[props.size] || sizeMap.md
})

const iconSizeClass = computed(() => {
  const sizeMap = {
    xs: 'text-xs',
    sm: 'text-sm',
    md: 'text-base',
    lg: 'text-lg',
    xl: 'text-xl'
  }
  return sizeMap[props.size] || sizeMap.md
})

// Methods
const handleClick = (event) => {
  if (props.loading || props.disabled) {
    event.preventDefault()
    return
  }

  emit('click', event)

  // Handle router navigation
  if (props.to && tag.value === 'router-link') {
    if (props.replace) {
      router.replace(props.to)
    } else {
      router.push(props.to)
    }
  }
}
</script>

<style scoped>
/* Base Button Styles */
.btn {
  @apply relative inline-flex items-center justify-center font-medium;
  @apply focus:outline-none focus:ring-2 focus:ring-offset-2;
  @apply transition-all duration-200 ease-in-out;
  @apply select-none;
  text-decoration: none; /* For router-link */
}

/* Sizes */
.btn--xs {
  @apply px-2 py-1 text-xs rounded;
  @apply focus:ring-1 focus:ring-offset-1;
}

.btn--sm {
  @apply px-3 py-1.5 text-sm rounded;
  @apply focus:ring-1 focus:ring-offset-1;
}

.btn--md {
  @apply px-4 py-2 text-sm rounded-md;
}

.btn--lg {
  @apply px-6 py-3 text-base rounded-md;
}

.btn--xl {
  @apply px-8 py-4 text-lg rounded-lg;
}

/* Primary Variant */
.btn--primary {
  @apply bg-brand-600 text-white border border-transparent;
  @apply hover:bg-brand-700 focus:ring-brand-500;
  @apply active:bg-brand-800;
}

.btn--primary.btn--disabled {
  @apply bg-brand-300 cursor-not-allowed;
}

.btn--primary.btn--loading {
  @apply bg-brand-600 cursor-wait;
}

/* Secondary Variant */
.btn--secondary {
  @apply bg-neutral-100 text-neutral-900 border border-neutral-300;
  @apply hover:bg-neutral-200 focus:ring-neutral-500;
  @apply active:bg-neutral-300;
}

.btn--secondary.btn--disabled {
  @apply bg-neutral-50 text-neutral-400 cursor-not-allowed;
}

/* Success Variant */
.btn--success {
  @apply bg-success-600 text-white border border-transparent;
  @apply hover:bg-success-700 focus:ring-success-500;
  @apply active:bg-success-800;
}

.btn--success.btn--disabled {
  @apply bg-success-300 cursor-not-allowed;
}

/* Warning Variant */
.btn--warning {
  @apply bg-warning-500 text-white border border-transparent;
  @apply hover:bg-warning-600 focus:ring-warning-500;
  @apply active:bg-warning-700;
}

.btn--warning.btn--disabled {
  @apply bg-warning-300 cursor-not-allowed;
}

/* Danger Variant */
.btn--danger {
  @apply bg-danger-600 text-white border border-transparent;
  @apply hover:bg-danger-700 focus:ring-danger-500;
  @apply active:bg-danger-800;
}

.btn--danger.btn--disabled {
  @apply bg-danger-300 cursor-not-allowed;
}

/* Outline Variant */
.btn--outline {
  @apply bg-transparent text-brand-700 border-2 border-brand-600;
  @apply hover:bg-brand-50 focus:ring-brand-500;
  @apply active:bg-brand-100;
}

.btn--outline.btn--disabled {
  @apply text-neutral-400 border-neutral-300 cursor-not-allowed;
}

/* Ghost Variant */
.btn--ghost {
  @apply bg-transparent text-neutral-600 border border-transparent;
  @apply hover:bg-neutral-100 hover:text-neutral-900 focus:ring-neutral-500;
  @apply active:bg-neutral-200;
}

.btn--ghost.btn--disabled {
  @apply text-neutral-400 cursor-not-allowed;
}

/* Link Variant */
.btn--link {
  @apply bg-transparent text-brand-600 border border-transparent;
  @apply hover:text-brand-800 hover:underline focus:ring-brand-500;
  @apply active:text-brand-900;
  @apply p-0 font-normal;
}

.btn--link.btn--disabled {
  @apply text-neutral-400 cursor-not-allowed;
}

/* States */
.btn--active {
  @apply ring-2 ring-brand-500 ring-offset-2;
}

.btn--loading {
  @apply cursor-wait;
}

/* Visual Options */
.btn--rounded {
  @apply rounded-full;
}

.btn--square {
  @apply rounded-none;
}

.btn--shadow {
  @apply shadow-md hover:shadow-lg;
}

.btn--shadow.btn--disabled {
  @apply shadow-none;
}

.btn--block {
  @apply w-full;
}

.btn--icon-only {
  @apply aspect-square;
}

.btn--icon-only.btn--xs {
  @apply p-1;
}

.btn--icon-only.btn--sm {
  @apply p-1.5;
}

.btn--icon-only.btn--md {
  @apply p-2;
}

.btn--icon-only.btn--lg {
  @apply p-3;
}

.btn--icon-only.btn--xl {
  @apply p-4;
}

/* Content Elements */
.btn-spinner {
  @apply animate-spin;
}

.btn-icon--left {
  @apply mr-2;
}

.btn-icon--right {
  @apply ml-2;
}

.btn-content {
  @apply truncate;
}

.btn-badge {
  @apply absolute -top-1 -right-1 bg-danger-500 text-white text-xs;
  @apply rounded-full min-w-[1.25rem] h-5 flex items-center justify-center;
  @apply px-1 font-semibold;
}

/* Hover Effects */
.btn:hover:not(.btn--disabled):not(.btn--loading) {
  @apply transform -translate-y-0.5;
}

.btn--shadow:hover:not(.btn--disabled):not(.btn--loading) {
  @apply shadow-lg;
}

/* Focus Visible */
.btn:focus-visible {
  @apply outline-none ring-2 ring-offset-2;
}

/* Loading State Animations */
.btn--loading .btn-content {
  @apply opacity-75;
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .btn--lg {
    @apply px-4 py-2 text-sm;
  }

  .btn--xl {
    @apply px-6 py-3 text-base;
  }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
  .btn--secondary {
    @apply bg-neutral-800 text-neutral-100 border-neutral-700;
    @apply hover:bg-neutral-700 active:bg-neutral-600;
  }

  .btn--ghost {
    @apply text-neutral-400 hover:bg-neutral-800 hover:text-neutral-200;
  }
}

/* Accessibility improvements */
.btn[aria-pressed="true"] {
  @apply ring-2 ring-brand-500 ring-offset-2;
}

.btn:disabled {
  @apply cursor-not-allowed opacity-60;
}

/* Print styles */
@media print {
  .btn {
    @apply bg-white text-black border border-black;
  }
}
</style>