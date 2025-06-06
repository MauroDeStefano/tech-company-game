<template>
  <Teleport to="body">
    <Transition
      name="modal"
      @before-enter="beforeEnter"
      @after-enter="afterEnter"
      @before-leave="beforeLeave"
      @after-leave="afterLeave"
    >
      <div
        v-if="isOpen"
        class="modal-overlay"
        :class="overlayClasses"
        @click="handleOverlayClick"
      >
        <!-- Modal Container -->
        <div
          ref="modalRef"
          class="modal-container"
          :class="containerClasses"
          role="dialog"
          :aria-modal="true"
          :aria-labelledby="headerId"
          :aria-describedby="bodyId"
          @click.stop
        >
          <!-- Modal Header -->
          <header v-if="showHeader" class="modal-header" :class="headerClasses">
            <div class="modal-header-content">
              <!-- Title Section -->
              <div class="modal-title-section">
                <!-- Icon -->
                <div v-if="icon" class="modal-icon" :class="iconClasses">
                  {{ icon }}
                </div>

                <!-- Title -->
                <h3 v-if="title" :id="headerId" class="modal-title">
                  {{ title }}
                </h3>
                <slot v-else name="title" />

                <!-- Subtitle -->
                <p v-if="subtitle" class="modal-subtitle">
                  {{ subtitle }}
                </p>
                <slot v-else name="subtitle" />
              </div>

              <!-- Header Actions -->
              <div class="modal-header-actions">
                <slot name="headerActions" />

                <!-- Close Button -->
                <button
                  v-if="showCloseButton"
                  type="button"
                  class="modal-close-btn"
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
          </header>

          <!-- Modal Body -->
          <main
            :id="bodyId"
            class="modal-body"
            :class="bodyClasses"
          >
            <!-- Loading State -->
            <div v-if="loading" class="modal-loading">
              <div class="loading-spinner">
                <svg class="animate-spin h-8 w-8 text-brand-600" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                </svg>
              </div>
              <p class="loading-text">{{ loadingText }}</p>
            </div>

            <!-- Main Content -->
            <div v-else class="modal-content">
              <slot />
            </div>
          </main>

          <!-- Modal Footer -->
          <footer v-if="showFooter" class="modal-footer" :class="footerClasses">
            <slot name="footer">
              <div class="modal-footer-actions">
                <!-- Cancel Button -->
                <BaseButton
                  v-if="showCancelButton"
                  variant="secondary"
                  :disabled="loading"
                  @click="handleCancel"
                >
                  {{ cancelText }}
                </BaseButton>

                <!-- Confirm Button -->
                <BaseButton
                  v-if="showConfirmButton"
                  :variant="confirmVariant"
                  :loading="loading"
                  @click="handleConfirm"
                >
                  {{ confirmText }}
                </BaseButton>
              </div>
            </slot>
          </footer>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue'
import { generateId } from '@/js/utils/helpers'
import BaseButton from './BaseButton.vue'

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
  const classes = ['modal-overlay--base']
  if (props.centered) classes.push('modal-overlay--centered')
  return classes.join(' ')
})

const containerClasses = computed(() => {
  const classes = [
    'modal-container--base',
    `modal-container--${props.size}`,
    `modal-container--${props.variant}`
  ]

  if (props.scrollable) classes.push('modal-container--scrollable')
  return classes.join(' ')
})

const headerClasses = computed(() => {
  const classes = [`modal-header--${props.variant}`]
  return classes.join(' ')
})

const bodyClasses = computed(() => {
  const classes = ['modal-body--base']
  if (props.scrollable) classes.push('modal-body--scrollable')
  return classes.join(' ')
})

const footerClasses = computed(() => {
  const classes = [`modal-footer--${props.variant}`]
  return classes.join(' ')
})

const iconClasses = computed(() => {
  const variantMap = {
    default: 'text-neutral-500',
    success: 'text-success-500',
    warning: 'text-warning-500',
    danger: 'text-danger-500'
  }
  return variantMap[props.variant] || variantMap.default
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
      // Prima prova con elementi focusable nel modal
      const focusableElements = modalRef.value.querySelectorAll(
        'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
      )

      if (focusableElements.length > 0) {
        focusableElements[0].focus()
      } else {
        // Fallback: focus sul container del modal
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
      // Fallback se l'elemento non è più focusable
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
      // Shift + Tab
      if (document.activeElement === firstElement) {
        event.preventDefault()
        lastElement.focus()
      }
    } else {
      // Tab
      if (document.activeElement === lastElement) {
        event.preventDefault()
        firstElement.focus()
      }
    }
  }
}

// Lifecycle hooks for transitions
const beforeEnter = () => {
  // Save currently focused element
  previousFocus.value = document.activeElement

  // Prevent body scroll
  document.body.style.overflow = 'hidden'
  document.body.style.paddingRight = getScrollbarWidth() + 'px'
}

const afterEnter = () => {
  // Focus management
  focusFirstElement()

  // Add keyboard listeners
  document.addEventListener('keydown', handleEscape)
  document.addEventListener('keydown', trapFocus)

  emit('opened')
}

const beforeLeave = () => {
  // Remove keyboard listeners
  document.removeEventListener('keydown', handleEscape)
  document.removeEventListener('keydown', trapFocus)
}

const afterLeave = () => {
  // Restore body scroll
  document.body.style.overflow = ''
  document.body.style.paddingRight = ''

  // Restore focus
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
    // Modal is opening
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

  // Restore body styles if component is destroyed while modal is open
  if (props.isOpen) {
    document.body.style.overflow = ''
    document.body.style.paddingRight = ''
  }
})
</script>

<style scoped>
/* === MODAL OVERLAY === */
.modal-overlay {
  @apply fixed inset-0 z-50 flex;
  background-color: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
}

.modal-overlay--base {
  @apply items-end justify-center p-4;
}

.modal-overlay--centered {
  @apply items-center;
}

/* === MODAL CONTAINER === */
.modal-container--base {
  @apply relative w-full rounded-lg bg-white shadow-xl;
  max-height: calc(100vh - 2rem);
  display: flex;
  flex-direction: column;
}

.modal-container--scrollable {
  @apply overflow-hidden;
}

/* Container sizes */
.modal-container--xs { @apply max-w-xs; }
.modal-container--sm { @apply max-w-sm; }
.modal-container--md { @apply max-w-md; }
.modal-container--lg { @apply max-w-lg; }
.modal-container--xl { @apply max-w-xl; }
.modal-container--full {
  @apply max-w-none w-full h-full;
  max-height: none;
  border-radius: 0;
}

/* Container variants */
.modal-container--default {
  @apply border border-neutral-200;
}

.modal-container--success {
  @apply border-l-4 border-l-success-500;
}

.modal-container--warning {
  @apply border-l-4 border-l-warning-500;
}

.modal-container--danger {
  @apply border-l-4 border-l-danger-500;
}

/* === MODAL HEADER === */
.modal-header {
  @apply px-6 py-4 border-b border-neutral-200;
  flex-shrink: 0;
}

.modal-header-content {
  @apply flex items-start justify-between;
}

.modal-title-section {
  @apply flex-1 min-w-0;
}

.modal-icon {
  @apply text-2xl mb-2;
}

.modal-title {
  @apply text-lg font-semibold text-neutral-900 mb-1;
}

.modal-subtitle {
  @apply text-sm text-neutral-600;
}

.modal-header-actions {
  @apply flex items-center gap-2 ml-4;
  flex-shrink: 0;
}

.modal-close-btn {
  @apply p-1 rounded-md text-neutral-400 hover:text-neutral-600 hover:bg-neutral-100 transition-colors;
  @apply disabled:opacity-50 disabled:cursor-not-allowed;
}

/* Header variants */
.modal-header--success {
  @apply bg-success-50 border-success-200;
}

.modal-header--warning {
  @apply bg-warning-50 border-warning-200;
}

.modal-header--danger {
  @apply bg-danger-50 border-danger-200;
}

/* === MODAL BODY === */
.modal-body--base {
  @apply px-6 py-4;
  flex: 1;
  min-height: 0;
}

.modal-body--scrollable {
  @apply overflow-y-auto;
}

.modal-content {
  @apply w-full h-full;
}

/* Loading state */
.modal-loading {
  @apply flex flex-col items-center justify-center py-8;
}

.loading-spinner {
  @apply mb-4;
}

.loading-text {
  @apply text-sm text-neutral-600;
}

/* === MODAL FOOTER === */
.modal-footer {
  @apply px-6 py-4 border-t border-neutral-200;
  flex-shrink: 0;
}

.modal-footer-actions {
  @apply flex items-center justify-end gap-3;
}

/* Footer variants */
.modal-footer--success {
  @apply bg-success-50 border-success-200;
}

.modal-footer--warning {
  @apply bg-warning-50 border-warning-200;
}

.modal-footer--danger {
  @apply bg-danger-50 border-danger-200;
}

/* === TRANSITIONS === */
.modal-enter-active,
.modal-leave-active {
  transition: all 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-from .modal-container,
.modal-leave-to .modal-container {
  transform: scale(0.9) translateY(20px);
}

/* === RESPONSIVE === */
@media (max-width: 640px) {
  .modal-overlay--base {
    @apply p-0;
  }

  .modal-container--base {
    @apply rounded-none;
    max-height: 100vh;
  }

  .modal-container--xs,
  .modal-container--sm,
  .modal-container--md,
  .modal-container--lg,
  .modal-container--xl {
    @apply w-full max-w-none;
  }
}
</style>