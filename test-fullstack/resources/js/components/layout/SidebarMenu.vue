<template>
  <Teleport to="body">
    <Transition name="sidebar">
      <div
        v-if="isOpen"
        class="sidebar-overlay"
        @click="$emit('close')"
      >
        <div class="sidebar-menu" @click.stop>
          <!-- Header -->
          <div class="sidebar-header">
            <h3 class="sidebar-title">Menu</h3>
            <button
              class="sidebar-close"
              @click="$emit('close')"
              aria-label="Chiudi menu"
            >
              ✕
            </button>
          </div>

          <!-- Content -->
          <div class="sidebar-content">
            <p class="sidebar-placeholder">Menu content here</p>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
defineProps({
  isOpen: {
    type: Boolean,
    default: false
  }
})

defineEmits(['close'])
</script>

<style scoped>
.sidebar-overlay {
  @apply fixed inset-0 bg-black bg-opacity-50 z-50;
}

.sidebar-menu {
  @apply absolute right-0 top-0 bottom-0 w-80 max-w-full;
  @apply bg-white shadow-xl;
}

.sidebar-header {
  @apply flex items-center justify-between p-4 border-b border-neutral-200;
}

.sidebar-title {
  @apply text-lg font-semibold;
}

.sidebar-close {
  @apply p-2 hover:bg-neutral-100 rounded;
}

.sidebar-content {
  @apply p-4;
}

.sidebar-placeholder {
  @apply text-neutral-600;
}

/* Transitions */
.sidebar-enter-active,
.sidebar-leave-active {
  transition: opacity 0.3s ease;
}

.sidebar-enter-from,
.sidebar-leave-to {
  opacity: 0;
}

.sidebar-enter-active .sidebar-menu,
.sidebar-leave-active .sidebar-menu {
  transition: transform 0.3s ease;
}

.sidebar-enter-from .sidebar-menu,
.sidebar-leave-to .sidebar-menu {
  transform: translateX(100%);
}
</style>