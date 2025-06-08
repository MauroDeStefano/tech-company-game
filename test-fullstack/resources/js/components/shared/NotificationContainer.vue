<template>
  <Teleport to="body">
    <div class="fixed top-4 right-4 z-50 space-y-4 max-w-sm w-full">
      <TransitionGroup
        enter-active-class="transition-all duration-300"
        leave-active-class="transition-all duration-300"
        enter-from-class="opacity-0 translate-x-full scale-95"
        enter-to-class="opacity-100 translate-x-0 scale-100"
        leave-from-class="opacity-100 translate-x-0 scale-100"
        leave-to-class="opacity-0 translate-x-full scale-95"
        tag="div"
        class="space-y-4"
      >
        <div
          v-for="notification in notificationStore.notifications"
          :key="notification.id"
          class="bg-white rounded-xl shadow-lg border border-gray-100 p-4 transform transition-all duration-200 hover:scale-105"
          :class="getNotificationClasses(notification.type)"
        >
          <!-- Notification Content -->
          <div class="flex items-start gap-3">
            <!-- Icon -->
            <div class="flex-shrink-0 text-lg">
              {{ notification.icon }}
            </div>

            <!-- Text Content -->
            <div class="flex-1 min-w-0">
              <!-- Title -->
              <h4 v-if="notification.title" class="text-sm font-semibold text-gray-900 mb-1">
                {{ notification.title }}
              </h4>

              <!-- Message -->
              <p class="text-sm text-gray-600">
                {{ notification.message }}
              </p>
            </div>

            <!-- Close Button -->
            <button
              class="flex-shrink-0 p-1 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors duration-200"
              @click="notificationStore.remove(notification.id)"
              aria-label="Chiudi notifica"
            >
              <span class="text-sm">✕</span>
            </button>
          </div>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<script setup>
import { useNotificationStore } from '@/js/stores/notifications'

// Store
const notificationStore = useNotificationStore()

// Style helpers
function getNotificationClasses(type) {
  const typeClasses = {
    success: 'border-l-4 border-l-green-500 bg-green-50',
    error: 'border-l-4 border-l-red-500 bg-red-50',
    warning: 'border-l-4 border-l-yellow-500 bg-yellow-50',
    info: 'border-l-4 border-l-blue-500 bg-blue-50'
  }
  return typeClasses[type] || typeClasses.info
}
</script>
