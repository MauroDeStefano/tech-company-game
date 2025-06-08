<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition-all duration-300"
      leave-active-class="transition-all duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="isOpen"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-start justify-start z-50"
        @click="$emit('close')"
      >
        <Transition
          enter-active-class="transition-transform duration-300"
          leave-active-class="transition-transform duration-300"
          enter-from-class="-translate-x-full"
          enter-to-class="translate-x-0"
          leave-from-class="translate-x-0"
          leave-to-class="-translate-x-full"
        >
          <div
            v-if="isOpen"
            class="bg-white w-80 h-full shadow-2xl overflow-y-auto"
            @click.stop
          >
            <!-- Header -->
            <div class="p-6 border-b border-gray-200">
              <div class="flex items-center justify-between">
                <h3 class="text-xl font-bold text-gray-900">Menu</h3>
                <button
                  @click="$emit('close')"
                  class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors duration-200"
                  aria-label="Chiudi menu"
                >
                  <span class="text-lg">✕</span>
                </button>
              </div>
            </div>

            <!-- Content -->
            <div class="p-6">
              <p class="text-gray-600 text-base">Menu content here</p>
            </div>
          </div>
        </Transition>
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
