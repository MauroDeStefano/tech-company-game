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
      class="animate-spin"
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
      :class="iconSizeClass"
    >
      {{ icon }}
    </span>

    <!-- Button Content -->
    <span v-if="!loading || showTextOnLoading" class="inline-block">
      <slot>{{ text }}</slot>
    </span>

    <!-- Icon (Right) -->
    <span
      v-if="icon && iconPosition === 'right' && !loading"
      :class="iconSizeClass"
    >
      {{ icon }}
    </span>

    <!-- Badge/Counter -->
    <span
      v-if="badge && !loading"
      class="ml-2 inline-flex items-center px-2 py-1 text-xs font-medium bg-white bg-opacity-20 rounded-full"
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
  const baseClasses = [
    'inline-flex items-center justify-center gap-2',
    'font-semibold transition-all duration-200',
    'focus:outline-none focus:ring-2 focus:ring-offset-2',
    'disabled:opacity-50 disabled:cursor-not-allowed'
  ]

  // Size classes
  const sizeClasses = {
    xs: 'px-2 py-1 text-xs',
    sm: 'px-3 py-1.5 text-sm',
    md: 'px-4 py-2 text-base',
    lg: 'px-6 py-3 text-lg',
    xl: 'px-8 py-4 text-xl'
  }
  baseClasses.push(sizeClasses[props.size])

  // Variant classes
  const variantClasses = {
    primary: 'bg-blue-600 hover:bg-blue-700 text-white focus:ring-blue-500',
    secondary: 'bg-gray-600 hover:bg-gray-700 text-white focus:ring-gray-500',
    success: 'bg-green-600 hover:bg-green-700 text-white focus:ring-green-500',
    warning: 'bg-yellow-600 hover:bg-yellow-700 text-white focus:ring-yellow-500',
    danger: 'bg-red-600 hover:bg-red-700 text-white focus:ring-red-500',
    outline: 'border-2 border-gray-300 hover:border-gray-400 bg-transparent text-gray-700 hover:bg-gray-50 focus:ring-gray-500',
    ghost: 'bg-transparent hover:bg-gray-100 text-gray-700 focus:ring-gray-500',
    link: 'bg-transparent hover:bg-transparent text-blue-600 hover:text-blue-700 underline focus:ring-blue-500 p-0'
  }
  baseClasses.push(variantClasses[props.variant])

  // Rounded
  if (props.rounded) {
    baseClasses.push('rounded-full')
  } else if (props.square) {
    baseClasses.push('rounded-none')
  } else {
    baseClasses.push('rounded-lg')
  }

  // Shadow
  if (props.shadow) {
    baseClasses.push('shadow-md hover:shadow-lg')
  }

  // Block
  if (props.block) {
    baseClasses.push('w-full')
  }

  // Loading state
  if (props.loading) {
    baseClasses.push('pointer-events-none')
  }

  // Active state
  if (props.active) {
    baseClasses.push('ring-2 ring-offset-2')
    if (props.variant === 'primary') {
      baseClasses.push('ring-blue-500')
    } else if (props.variant === 'secondary') {
      baseClasses.push('ring-gray-500')
    }
  }

  // Icon only button
  if (props.icon && !props.text && !props.$slots.default) {
    const iconOnlySizes = {
      xs: 'w-6 h-6',
      sm: 'w-8 h-8',
      md: 'w-10 h-10',
      lg: 'w-12 h-12',
      xl: 'w-14 h-14'
    }
    baseClasses.push(iconOnlySizes[props.size])
    baseClasses.push('p-0')
  }

  return baseClasses.join(' ')
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
