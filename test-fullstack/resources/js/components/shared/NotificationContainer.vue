<template>
  <Teleport to="body">
    <div class="notification-container">
      <TransitionGroup
        name="notification"
        tag="div"
        class="notification-list"
      >
        <div
          v-for="notification in notificationStore.notifications"
          :key="notification.id"
          class="notification-item"
          :class="getNotificationClasses(notification.type)"
        >
          <!-- Notification Content -->
          <div class="notification-content">
            <!-- Icon -->
            <div class="notification-icon">
              {{ notification.icon }}
            </div>

            <!-- Text Content -->
            <div class="notification-text">
              <!-- Title -->
              <h4 v-if="notification.title" class="notification-title">
                {{ notification.title }}
              </h4>

              <!-- Message -->
              <p class="notification-message">
                {{ notification.message }}
              </p>
            </div>

            <!-- Close Button -->
            <button
              class="notification-close"
              @click="notificationStore.remove(notification.id)"
              aria-label="Chiudi notifica"
            >
              ✕
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
    success: 'notification-item--success',
    error: 'notification-item--error',
    warning: 'notification-item--warning',
    info: 'notification-item--info'
  }
  return typeClasses[type] || typeClasses.info
}
</script>

<style scoped>
.notification-container {
  @apply fixed top-4 right-4 z-50 pointer-events-none;
  @apply flex flex-col items-end space-y-3;
  @apply max-w-sm w-full;
}

.notification-list {
  @apply flex flex-col items-end space-y-3 w-full;
}

.notification-item {
  @apply relative w-full max-w-sm;
  @apply bg-white border rounded-lg shadow-lg;
  @apply pointer-events-auto;
  @apply overflow-hidden;
}

.notification-item--success {
  @apply border-l-4 border-l-success-500 bg-success-50;
}

.notification-item--error {
  @apply border-l-4 border-l-danger-500 bg-danger-50;
}

.notification-item--warning {
  @apply border-l-4 border-l-warning-500 bg-warning-50;
}

.notification-item--info {
  @apply border-l-4 border-l-blue-500 bg-blue-50;
}

.notification-content {
  @apply flex items-start p-4;
}

.notification-icon {
  @apply flex-shrink-0 text-xl mr-3 mt-0.5;
}

.notification-text {
  @apply flex-1 min-w-0;
}

.notification-title {
  @apply text-sm font-semibold text-neutral-900 mb-1;
}

.notification-message {
  @apply text-sm text-neutral-700 leading-relaxed;
}

.notification-close {
  @apply flex-shrink-0 ml-3 text-neutral-400;
  @apply hover:text-neutral-600 transition-colors;
  @apply focus:outline-none focus:text-neutral-600;
  @apply p-1 rounded;
}

/* Animations */
.notification-enter-active,
.notification-leave-active {
  @apply transition-all duration-300 ease-out;
}

.notification-enter-from {
  @apply opacity-0 transform translate-x-full;
}

.notification-leave-to {
  @apply opacity-0 transform translate-x-full;
}

/* Responsive */
@media (max-width: 640px) {
  .notification-container {
    @apply top-2 right-2 left-2 max-w-none;
  }

  .notification-item {
    @apply max-w-none;
  }
}
</style>