<template>
  <div class="space-y-2" :class="groupClasses">
    <!-- Label -->
    <label
      v-if="label || $slots.label"
      :for="inputId"
      class="block text-sm font-medium"
      :class="labelClasses"
    >
      <slot v-if="$slots.label" name="label" />
      <template v-else>
        {{ label }}
        <span v-if="required" class="text-red-500 ml-1">*</span>
      </template>
    </label>

    <!-- Input Container -->
    <div class="relative" :class="containerClasses">
      <!-- Left Icon/Addon -->
      <div v-if="leftIcon || $slots.leftAddon" class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
        <slot v-if="$slots.leftAddon" name="leftAddon" />
        <span v-else-if="leftIcon" class="text-gray-400 text-lg">{{ leftIcon }}</span>
      </div>

      <!-- Input Element -->
      <component
        :is="inputType"
        :id="inputId"
        ref="inputRef"
        :class="inputClasses"
        :type="computedType"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :readonly="readonly"
        :required="required"
        :min="min"
        :max="max"
        :step="step"
        :minlength="minlength"
        :maxlength="maxlength"
        :pattern="pattern"
        :autocomplete="autocomplete"
        :autofocus="autofocus"
        :rows="rows"
        :cols="cols"
        v-bind="$attrs"
        @input="handleInput"
        @change="handleChange"
        @focus="handleFocus"
        @blur="handleBlur"
        @keydown="handleKeydown"
        @keyup="handleKeyup"
      />

      <!-- Right Icon/Addon -->
      <div v-if="rightIcon || $slots.rightAddon || showPasswordToggle || showClearButton"
           class="absolute inset-y-0 right-0 pr-3 flex items-center gap-2">
        <!-- Clear Button -->
        <button
          v-if="showClearButton"
          type="button"
          class="text-gray-400 hover:text-gray-600 transition-colors duration-200"
          @click="clearInput"
          :disabled="disabled"
        >
          <span class="text-sm">✕</span>
        </button>

        <!-- Password Toggle -->
        <button
          v-if="showPasswordToggle"
          type="button"
          class="text-gray-400 hover:text-gray-600 transition-colors duration-200"
          @click="togglePasswordVisibility"
          :disabled="disabled"
        >
          <span class="text-lg">{{ isPasswordVisible ? '🙈' : '👁️' }}</span>
        </button>

        <!-- Custom Right Addon -->
        <slot v-if="$slots.rightAddon" name="rightAddon" />
        <span v-else-if="rightIcon" class="text-gray-400 text-lg">{{ rightIcon }}</span>
      </div>

      <!-- Loading Spinner -->
      <div v-if="loading" class="absolute inset-y-0 right-0 pr-3 flex items-center">
        <svg class="animate-spin h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
        </svg>
      </div>
    </div>

    <!-- Helper Text / Error Message -->
    <div v-if="helperText || errorMessage || $slots.helper" class="text-sm">
      <div v-if="errorMessage" class="flex items-center gap-2 text-red-600">
        <span>⚠️</span>
        {{ errorMessage }}
      </div>
      <div v-else-if="helperText || $slots.helper" class="text-gray-600">
        <slot v-if="$slots.helper" name="helper" />
        <template v-else>{{ helperText }}</template>
      </div>
    </div>

    <!-- Character Counter -->
    <div v-if="showCounter" class="text-xs text-gray-500 text-right">
      {{ characterCount }}/{{ maxlength }}
    </div>
  </div>
</template>

<script setup>
import { ref, computed, nextTick, watch } from 'vue'
import { generateId } from '@/js/utils/helpers'

// Props
const props = defineProps({
  // Basic props
  modelValue: {
    type: [String, Number],
    default: ''
  },
  type: {
    type: String,
    default: 'text',
    validator: (value) => [
      'text', 'email', 'password', 'number', 'tel', 'url', 'search',
      'date', 'time', 'datetime-local', 'month', 'week', 'color'
    ].includes(value)
  },
  inputType: {
    type: String,
    default: 'input',
    validator: (value) => ['input', 'textarea'].includes(value)
  },

  // Content
  label: {
    type: String,
    default: null
  },
  placeholder: {
    type: String,
    default: null
  },
  helperText: {
    type: String,
    default: null
  },
  errorMessage: {
    type: String,
    default: null
  },

  // Icons and addons
  leftIcon: {
    type: String,
    default: null
  },
  rightIcon: {
    type: String,
    default: null
  },

  // Validation
  required: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  },
  readonly: {
    type: Boolean,
    default: false
  },
  loading: {
    type: Boolean,
    default: false
  },

  // HTML attributes
  min: {
    type: [String, Number],
    default: null
  },
  max: {
    type: [String, Number],
    default: null
  },
  step: {
    type: [String, Number],
    default: null
  },
  minlength: {
    type: [String, Number],
    default: null
  },
  maxlength: {
    type: [String, Number],
    default: null
  },
  pattern: {
    type: String,
    default: null
  },
  autocomplete: {
    type: String,
    default: null
  },
  autofocus: {
    type: Boolean,
    default: false
  },

  // Textarea specific
  rows: {
    type: [String, Number],
    default: 3
  },
  cols: {
    type: [String, Number],
    default: null
  },

  // Visual options
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'filled', 'outlined'].includes(value)
  },
  rounded: {
    type: Boolean,
    default: false
  },

  // Features
  clearable: {
    type: Boolean,
    default: false
  },
  showCounter: {
    type: Boolean,
    default: false
  },
  debounce: {
    type: Number,
    default: 0
  }
})

// Emits
const emit = defineEmits([
  'update:modelValue',
  'change',
  'focus',
  'blur',
  'keydown',
  'keyup',
  'clear'
])

// Reactive state
const inputRef = ref(null)
const isFocused = ref(false)
const isPasswordVisible = ref(false)
const debounceTimer = ref(null)

// Computed
const inputId = computed(() => generateId('input'))

const computedType = computed(() => {
  if (props.type === 'password') {
    return isPasswordVisible.value ? 'text' : 'password'
  }
  return props.type
})

const hasError = computed(() => !!props.errorMessage)

const showPasswordToggle = computed(() =>
  props.type === 'password' && !props.disabled && !props.readonly
)

const showClearButton = computed(() =>
  props.clearable &&
  props.modelValue &&
  props.modelValue.toString().length > 0 &&
  !props.disabled &&
  !props.readonly
)

const characterCount = computed(() =>
  props.modelValue ? props.modelValue.toString().length : 0
)

const groupClasses = computed(() => {
  const classes = []
  if (hasError.value) classes.push('opacity-100')
  if (props.disabled) classes.push('opacity-60')
  return classes.join(' ')
})

const labelClasses = computed(() => {
  const classes = ['text-gray-700']
  
  if (hasError.value) classes.push('text-red-600')
  if (props.disabled) classes.push('text-gray-400')
  
  return classes.join(' ')
})

const containerClasses = computed(() => {
  const classes = []
  
  if (props.loading) classes.push('pr-10')
  
  return classes.join(' ')
})

const inputClasses = computed(() => {
  const baseClasses = [
    'block w-full border transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2'
  ]

  // Size classes
  const sizeClasses = {
    sm: 'px-3 py-2 text-sm',
    md: 'px-4 py-3 text-base',
    lg: 'px-5 py-4 text-lg'
  }
  baseClasses.push(sizeClasses[props.size])

  // Variant classes
  const variantClasses = {
    default: 'bg-white border-gray-300 focus:border-blue-500 focus:ring-blue-500',
    filled: 'bg-gray-50 border-gray-300 focus:bg-white focus:border-blue-500 focus:ring-blue-500',
    outlined: 'bg-transparent border-2 border-gray-300 focus:border-blue-500 focus:ring-blue-500'
  }
  baseClasses.push(variantClasses[props.variant])

  // Rounded
  if (props.rounded) {
    baseClasses.push('rounded-full')
  } else {
    baseClasses.push('rounded-lg')
  }

  // State classes
  if (hasError.value) {
    baseClasses.push('border-red-500 focus:border-red-500 focus:ring-red-500')
  }
  
  if (props.disabled) {
    baseClasses.push('bg-gray-100 text-gray-500 cursor-not-allowed')
  }
  
  if (props.readonly) {
    baseClasses.push('bg-gray-50 focus:ring-0 focus:border-gray-300')
  }

  // Padding adjustments for icons
  if (props.leftIcon || props.$slots.leftAddon) {
    baseClasses.push('pl-10')
  }
  
  if (props.rightIcon || props.$slots.rightAddon || showPasswordToggle.value || showClearButton.value || props.loading) {
    baseClasses.push('pr-10')
  }

  return baseClasses.join(' ')
})

// Methods
const handleInput = (event) => {
  const value = event.target.value

  if (props.debounce > 0) {
    if (debounceTimer.value) {
      clearTimeout(debounceTimer.value)
    }

    debounceTimer.value = setTimeout(() => {
      emit('update:modelValue', value)
    }, props.debounce)
  } else {
    emit('update:modelValue', value)
  }
}

const handleChange = (event) => {
  emit('change', event.target.value)
}

const handleFocus = (event) => {
  isFocused.value = true
  emit('focus', event)
}

const handleBlur = (event) => {
  isFocused.value = false
  emit('blur', event)
}

const handleKeydown = (event) => {
  emit('keydown', event)

  // Clear on Escape
  if (event.key === 'Escape' && props.clearable) {
    clearInput()
  }
}

const handleKeyup = (event) => {
  emit('keyup', event)
}

const togglePasswordVisibility = () => {
  isPasswordVisible.value = !isPasswordVisible.value

  // Keep focus on input after toggle
  nextTick(() => {
    inputRef.value?.focus()
  })
}

const clearInput = () => {
  emit('update:modelValue', '')
  emit('clear')

  // Focus input after clear
  nextTick(() => {
    inputRef.value?.focus()
  })
}

const focus = () => {
  inputRef.value?.focus()
}

const blur = () => {
  inputRef.value?.blur()
}

const select = () => {
  inputRef.value?.select()
}

// Watch for autofocus
watch(() => props.autofocus, (newVal) => {
  if (newVal) {
    nextTick(() => {
      focus()
    })
  }
}, { immediate: true })

// Expose methods
defineExpose({
  focus,
  blur,
  select,
  inputRef
})
</script>
