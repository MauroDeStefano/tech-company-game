<template>
  <div class="input-group" :class="groupClasses">
    <!-- Label -->
    <label
      v-if="label || $slots.label"
      :for="inputId"
      class="input-label"
      :class="labelClasses"
    >
      <slot v-if="$slots.label" name="label" />
      <template v-else>
        {{ label }}
        <span v-if="required" class="input-required">*</span>
      </template>
    </label>

    <!-- Input Container -->
    <div class="input-container" :class="containerClasses">
      <!-- Left Icon/Addon -->
      <div v-if="leftIcon || $slots.leftAddon" class="input-addon input-addon--left">
        <slot v-if="$slots.leftAddon" name="leftAddon" />
        <span v-else-if="leftIcon" class="input-icon">{{ leftIcon }}</span>
      </div>

      <!-- Input Element -->
      <component
        :is="inputType"
        :id="inputId"
        ref="inputRef"
        :class="inputClasses"
        :type="type"
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
           class="input-addon input-addon--right">
        <!-- Clear Button -->
        <button
          v-if="showClearButton"
          type="button"
          class="input-clear-btn"
          @click="clearInput"
          :disabled="disabled"
        >
          <span class="input-icon">✕</span>
        </button>

        <!-- Password Toggle -->
        <button
          v-if="showPasswordToggle"
          type="button"
          class="input-password-toggle"
          @click="togglePasswordVisibility"
          :disabled="disabled"
        >
          <span class="input-icon">{{ isPasswordVisible ? '🙈' : '👁️' }}</span>
        </button>

        <!-- Custom Right Addon -->
        <slot v-if="$slots.rightAddon" name="rightAddon" />
        <span v-else-if="rightIcon" class="input-icon">{{ rightIcon }}</span>
      </div>

      <!-- Loading Spinner -->
      <div v-if="loading" class="input-loading">
        <svg class="animate-spin h-4 w-4 text-brand-600" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
        </svg>
      </div>
    </div>

    <!-- Helper Text / Error Message -->
    <div v-if="helperText || errorMessage || $slots.helper" class="input-helper">
      <div v-if="errorMessage" class="input-error">
        <span class="input-error-icon">⚠️</span>
        {{ errorMessage }}
      </div>
      <div v-else-if="helperText || $slots.helper" class="input-help">
        <slot v-if="$slots.helper" name="helper" />
        <template v-else>{{ helperText }}</template>
      </div>
    </div>

    <!-- Character Counter -->
    <div v-if="showCounter" class="input-counter">
      {{ characterCount }}/{{ maxlength }}
    </div>
  </div>
</template>

<script setup>
import { ref, computed, nextTick, watch } from 'vue'
import { generateId } from '@/utils/helpers'

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

const inputType = computed(() => {
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
  const classes = ['input-group']
  if (hasError.value) classes.push('input-group--error')
  if (props.disabled) classes.push('input-group--disabled')
  if (isFocused.value) classes.push('input-group--focused')
  return classes.join(' ')
})

const labelClasses = computed(() => {
  const classes = [`input-label--${props.size}`]
  if (hasError.value) classes.push('input-label--error')
  if (props.disabled) classes.push('input-label--disabled')
  return classes.join(' ')
})

const containerClasses = computed(() => {
  const classes = [
    'input-container--base',
    `input-container--${props.variant}`,
    `input-container--${props.size}`
  ]

  if (hasError.value) classes.push('input-container--error')
  if (props.disabled) classes.push('input-container--disabled')
  if (props.readonly) classes.push('input-container--readonly')
  if (isFocused.value) classes.push('input-container--focused')
  if (props.rounded) classes.push('input-container--rounded')
  if (props.loading) classes.push('input-container--loading')

  return classes.join(' ')
})

const inputClasses = computed(() => {
  const classes = [
    'input-field',
    `input-field--${props.size}`,
    `input-field--${props.variant}`
  ]

  if (props.leftIcon || props.$slots.leftAddon) classes.push('input-field--has-left')
  if (props.rightIcon || props.$slots.rightAddon || showPasswordToggle.value || showClearButton.value) {
    classes.push('input-field--has-right')
  }

  return classes.join(' ')
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

<style scoped>
/* Input Group */
.input-group {
  @apply space-y-2;
}

.input-group--disabled {
  @apply opacity-60;
}

/* Label */
.input-label {
  @apply block font-medium text-neutral-700;
}

.input-label--sm {
  @apply text-sm;
}

.input-label--md {
  @apply text-sm;
}

.input-label--lg {
  @apply text-base;
}

.input-label--error {
  @apply text-danger-700;
}

.input-label--disabled {
  @apply text-neutral-500;
}

.input-required {
  @apply text-danger-500 ml-1;
}

/* Container */
.input-container {
  @apply relative flex items-center;
}

.input-container--base {
  @apply border border-neutral-300 bg-white;
  @apply transition-all duration-200 ease-in-out;
}

.input-container--default {
  @apply rounded-md;
}

.input-container--filled {
  @apply bg-neutral-100 border-transparent;
}

.input-container--outlined {
  @apply border-2 border-neutral-300 bg-transparent;
}

.input-container--sm {
  @apply text-sm;
}

.input-container--md {
  @apply text-base;
}

.input-container--lg {
  @apply text-lg;
}

.input-container--focused {
  @apply ring-2 ring-brand-500 ring-opacity-50 border-brand-500;
}

.input-container--error {
  @apply border-danger-500 ring-2 ring-danger-500 ring-opacity-50;
}

.input-container--disabled {
  @apply bg-neutral-100 border-neutral-200 cursor-not-allowed;
}

.input-container--readonly {
  @apply bg-neutral-50;
}

.input-container--rounded {
  @apply rounded-full;
}

.input-container--loading {
  @apply pointer-events-none;
}

/* Input Field */
.input-field {
  @apply flex-1 border-0 bg-transparent placeholder-neutral-400;
  @apply focus:outline-none focus:ring-0;
  @apply transition-colors duration-200 ease-in-out;
}

.input-field--sm {
  @apply px-3 py-2 text-sm;
}

.input-field--md {
  @apply px-4 py-2.5 text-base;
}

.input-field--lg {
  @apply px-5 py-3 text-lg;
}

.input-field--has-left {
  @apply pl-0;
}

.input-field--has-right {
  @apply pr-0;
}

.input-field:disabled {
  @apply cursor-not-allowed text-neutral-500;
}

.input-field:read-only {
  @apply cursor-default;
}

/* Textarea specific */
textarea.input-field {
  @apply resize-y min-h-[2.5rem];
}

/* Addons */
.input-addon {
  @apply flex items-center px-3;
}

.input-addon--left {
  @apply border-r border-neutral-200 bg-neutral-50;
}

.input-addon--right {
  @apply border-l border-neutral-200 bg-neutral-50 space-x-1;
}

.input-icon {
  @apply text-neutral-500 text-base;
}

/* Special buttons */
.input-clear-btn,
.input-password-toggle {
  @apply p-1 text-neutral-400 hover:text-neutral-600;
  @apply transition-colors duration-150 ease-in-out;
  @apply focus:outline-none focus:text-neutral-600;
}

.input-clear-btn:hover,
.input-password-toggle:hover {
  @apply bg-neutral-100 rounded;
}

/* Loading */
.input-loading {
  @apply absolute right-3 top-1/2 transform -translate-y-1/2;
  @apply pointer-events-none;
}

/* Helper text */
.input-helper {
  @apply text-sm;
}

.input-error {
  @apply flex items-center text-danger-600;
}

.input-error-icon {
  @apply mr-1;
}

.input-help {
  @apply text-neutral-600;
}

/* Character counter */
.input-counter {
  @apply text-xs text-neutral-500 text-right;
}

/* Focus states */
.input-group--focused .input-addon {
  @apply border-brand-500;
}

.input-group--error .input-addon {
  @apply border-danger-500;
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
  .input-label {
    @apply text-neutral-300;
  }

  .input-container--base {
    @apply bg-neutral-800 border-neutral-600;
  }

  .input-container--filled {
    @apply bg-neutral-700;
  }

  .input-field {
    @apply text-neutral-100 placeholder-neutral-500;
  }

  .input-addon {
    @apply bg-neutral-700 border-neutral-600;
  }

  .input-help {
    @apply text-neutral-400;
  }

  .input-counter {
    @apply text-neutral-500;
  }
}

/* Accessibility improvements */
.input-field:focus {
  @apply outline-none;
}

.input-group--error .input-field {
  @apply caret-danger-500;
}

/* Animation for error state */
.input-group--error .input-container {
  animation: shake 0.3s ease-in-out;
}

@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-2px); }
  75% { transform: translateX(2px); }
}

/* Number input arrows hiding */
.input-field[type="number"] {
  -moz-appearance: textfield;
}

.input-field[type="number"]::-webkit-outer-spin-button,
.input-field[type="number"]::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

/* Search input clear button hiding */
.input-field[type="search"]::-webkit-search-cancel-button {
  -webkit-appearance: none;
}
</style>