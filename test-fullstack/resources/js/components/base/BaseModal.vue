<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition-all duration-300"
      leave-active-class="transition-all duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
      @before-enter="beforeEnter"
      @after-enter="afterEnter"
      @before-leave="beforeLeave"
      @after-leave="afterLeave"
    >
      <div
        v-if="isOpen"
        class="fixed inset-0 bg-black bg-opacity-50 z-50"
        :class="overlayClasses"
        @click="handleOverlayClick"
      >
        <!-- Modal Container -->
        <Transition
          enter-active-class="transition-all duration-300"
          leave-active-class="transition-all duration-300"
          enter-from-class="opacity-0 scale-95 translate-y-4"
          enter-to-class="opacity-100 scale-100 translate-y-0"
          leave-from-class="opacity-100 scale-100 translate-y-0"
          leave-to-class="opacity-0 scale-95 translate-y-4"
        >
          <div
            v-if="isOpen"
            ref="modalRef"
            class="bg-white rounded-2xl shadow-2xl transform transition-all duration-300"
            :class="containerClasses"
            role="dialog"
            :aria-modal="true"
            :aria-labelledby="headerId"
            :aria-describedby="bodyId"
            @click.stop
          >
            <!-- Modal Header -->
            <header v-if="showHeader" class="border-b border-gray-200" :class="headerClasses">
              <div class="p-6">
                <!-- Title Section -->
                <div class="flex items-start gap-4">
                  <!-- Icon -->
                  <div v-if="icon" class="flex-shrink-0 text-3xl" :class="iconClasses">
                    {{ icon }}
                  </div>

                  <div class="flex-1 min-w-0">
                    <!-- Title -->
                    <h3 v-if="title" :id="headerId" class="text-xl font-bold text-gray-900 mb-1">
                      {{ title }}
                    </h3>
                    <slot v-else name="title" />

                    <!-- Subtitle -->
                    <p v-if="subtitle" class="text-gray-600">
                      {{ subtitle }}
                    </p>
                    <slot v-else name="subtitle" />
                  </div>

                  <!-- Header Actions -->
                  <div class="flex items-center gap-2">
                    <slot name="headerActions" />

                    <!-- Close Button -->
                    <button
                      v-if="showCloseButton"
                      type="button"
                      class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors duration-200"
                      @click="handleClose"
                      :disabled="loading"
                      aria-label="Chiudi modale"
                    >
                      <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
            </header>

            <!-- Modal Body -->
            <main
              :id="bodyId"
              class="p-6"
              :class="bodyClasses"
            >
              <!-- Loading State -->
              <div v-if="loading" class="flex flex-col items-center justify-center py-8">
                <div class="mb-4">
                  <svg class="animate-spin h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                  </svg>
                </div>
                <p class="text-gray-600">{{ loadingText }}</p>
              </div>

              <!-- Main Content -->
              <div v-else>
                <slot />
              </div>
            </main>

            <!-- Modal Footer -->
            <footer v-if="showFooter" class="border-t border-gray-200 bg-gray-50 rounded-b-2xl" :class="footerClasses">
              <div class="p-6">
                <slot name="footer">
                  <div class="flex items-center gap-3">
                    <!-- Cancel Button -->
                    <button
                      v-if="showCancelButton"
                      class="flex-1 px-4 py-3 border border-gray-300 hover:border-gray-400 hover:bg-gray-50 text-gray-700 font-semibold rounded-xl transition-colors duration-200"
                      :disabled="loading"
                      @click="handleCancel"
                    >
                      {{ cancelText }}
                    </button>

                    <!-- Confirm Button -->
                    <button
                      v-if="showConfirmButton"
                      class="flex-1 px-4 py-3 font-semibold rounded-xl transition-colors duration-200"
                      :class="confirmButtonClasses"
                      :disabled="loading"
                      @click="handleConfirm"
                    >
                      <span v-if="loading" class="flex items-center justify-center gap-2">
                        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                        </svg>
                        Caricamento...
                      </span>
                      <span v-else>{{ confirmText }}</span>
                    </button>
                  </div>
                </slot>
              </div>
            </footer>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch, nextTick, onUnmounted } from 'vue'
import { generateId } from '@/js/utils/helpers'

// Props
const props = defineProps({
  // Visibility
  isOpen: {
    type: Boolean,
    default: false
  },
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
  // Behavior
  closeOnOverlay: {
    type: Boolean,
    default: true
  },
  closeOnEscape: {
    type: Boolean,
    default: true
  },
  persistent: {
    type: Boolean,
    default: false
  },
  loading: {
    type: Boolean,
    default: false
  },
  loadingText: {
    type: String,
    default: 'Caricamento...'
  },
  // Visual options
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl', 'full'].includes(value)
  },
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'success', 'warning', 'danger'].includes(value)
  },
  centered: {
    type: Boolean,
    default: true
  },
  scrollable: {
    type: Boolean,
    default: false
  },
  // Header options
  showHeader: {
    type: Boolean,
    default: true
  },
  showCloseButton: {
    type: Boolean,
    default: true
  },
  // Footer options
  showFooter: {
    type: Boolean,
    default: false
  },
  showCancelButton: {
    type: Boolean,
    default: true
  },
  showConfirmButton: {
    type: Boolean,
    default: true
  },
  cancelText: {
    type: String,
    default: 'Annulla'
  },
  confirmText: {
    type: String,
    default: 'Conferma'
  },
  confirmVariant: {
    type: String,
    default: 'primary'
  }
})

// Emits
const emit = defineEmits([
  'update:isOpen',
  'close',
  'cancel',
  'confirm',
  'opened',
  'closed'
])

// Reactive state
const modalRef = ref(null)
const previousFocus = ref(null)

// Computed
const headerId = computed(() => generateId('modal-title'))
const bodyId = computed(() => generateId('modal-body'))

const overlayClasses = computed(() => {
  return props.centered 
    ? 'flex items-center justify-center p-4' 
    : 'flex items-start justify-center p-4 pt-16'
})

const containerClasses = computed(() => {
  const sizeClasses = {
    xs: 'max-w-xs w-full',
    sm: 'max-w-sm w-full',
    md: 'max-w-md w-full',
    lg: 'max-w-lg w-full',
    xl: 'max-w-2xl w-full',
    full: 'max-w-[95vw] w-full h-[95vh]'
  }

  const classes = [sizeClasses[props.size]]
  
  if (props.scrollable) {
    classes.push('max-h-[90vh] overflow-hidden flex flex-col')
  } else {
    classes.push('max-h-[90vh]')
  }

  return classes.join(' ')
})

const headerClasses = computed(() => {
  const variantClasses = {
    default: 'bg-white',
    success: 'bg-green-50',
    warning: 'bg-yellow-50',
    danger: 'bg-red-50'
  }
  return variantClasses[props.variant] || variantClasses.default
})

const bodyClasses = computed(() => {
  const classes = []
  
  if (props.scrollable) {
    classes.push('flex-1 overflow-y-auto')
  }

  return classes.join(' ')
})

const footerClasses = computed(() => {
  return 'bg-gray-50'
})

const iconClasses = computed(() => {
  const variantMap = {
    default: 'text-gray-500',
    success: 'text-green-500',
    warning: 'text-yellow-500',
    danger: 'text-red-500'
  }
  return variantMap[props.variant] || variantMap.default
})

const confirmButtonClasses = computed(() => {
  const variantClasses = {
    primary: 'bg-blue-600 hover:bg-blue-700 text-white',
    success: 'bg-green-600 hover:bg-green-700 text-white',
    warning: 'bg-yellow-600 hover:bg-yellow-700 text-white',
    danger: 'bg-red-600 hover:bg-red-700 text-white'
  }
  
  if (props.loading) {
    return variantClasses[props.confirmVariant]?.replace('hover:bg-', 'bg-') || variantClasses.primary
  }
  
  return variantClasses[props.confirmVariant] || variantClasses.primary
})

// Methods
const handleOverlayClick = () => {
  if (props.closeOnOverlay && !props.persistent && !props.loading) {
    handleClose()
  }
}

const handleClose = () => {
  if (props.persistent && !props.loading) return

  emit('update:isOpen', false)
  emit('close')
}

const handleCancel = () => {
  if (props.loading) return

  emit('cancel')
  handleClose()
}

const handleConfirm = () => {
  if (props.loading) return

  emit('confirm')
}

const handleEscape = (event) => {
  if (event.key === 'Escape' && props.closeOnEscape && !props.persistent && !props.loading) {
    handleClose()
  }
}

// Focus management
const focusFirstElement = () => {
  nextTick(() => {
    if (modalRef.value) {
      const focusableElements = modalRef.value.querySelectorAll(
        'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
      )

      if (focusableElements.length > 0) {
        focusableElements[0].focus()
      } else {
        modalRef.value.focus()
      }
    }
  })
}

const restoreFocus = () => {
  if (previousFocus.value && typeof previousFocus.value.focus === 'function') {
    try {
      previousFocus.value.focus()
    } catch (error) {
      document.body.focus()
    }
  }
}

// Focus trap
const trapFocus = (event) => {
  if (!modalRef.value) return

  const focusableElements = modalRef.value.querySelectorAll(
    'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
  )

  const firstElement = focusableElements[0]
  const lastElement = focusableElements[focusableElements.length - 1]

  if (event.key === 'Tab') {
    if (event.shiftKey) {
      if (document.activeElement === firstElement) {
        event.preventDefault()
        lastElement.focus()
      }
    } else {
      if (document.activeElement === lastElement) {
        event.preventDefault()
        firstElement.focus()
      }
    }
  }
}

// Lifecycle hooks for transitions
const beforeEnter = () => {
  previousFocus.value = document.activeElement
  document.body.style.overflow = 'hidden'
  document.body.style.paddingRight = getScrollbarWidth() + 'px'
}

const afterEnter = () => {
  focusFirstElement()
  document.addEventListener('keydown', handleEscape)
  document.addEventListener('keydown', trapFocus)
  emit('opened')
}

const beforeLeave = () => {
  document.removeEventListener('keydown', handleEscape)
  document.removeEventListener('keydown', trapFocus)
}

const afterLeave = () => {
  document.body.style.overflow = ''
  document.body.style.paddingRight = ''
  restoreFocus()
  emit('closed')
}

// Utility function to get scrollbar width
const getScrollbarWidth = () => {
  const scrollDiv = document.createElement('div')
  scrollDiv.style.cssText = 'width: 100px; height: 100px; overflow: scroll; position: absolute; top: -9999px;'
  document.body.appendChild(scrollDiv)
  const scrollbarWidth = scrollDiv.offsetWidth - scrollDiv.clientWidth
  document.body.removeChild(scrollDiv)
  return scrollbarWidth
}

// Watch for prop changes
watch(() => props.isOpen, (newValue) => {
  if (newValue) {
    nextTick(() => {
      if (modalRef.value) {
        modalRef.value.scrollTop = 0
      }
    })
  }
})

// Cleanup on unmount
onUnmounted(() => {
  document.removeEventListener('keydown', handleEscape)
  document.removeEventListener('keydown', trapFocus)

  if (props.isOpen) {
    document.body.style.overflow = ''
    document.body.style.paddingRight = ''
  }
})
</script>
