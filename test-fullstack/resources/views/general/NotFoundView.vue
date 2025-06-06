<template>
  <div class="not-found-view">
    <div class="not-found-container">
      <!-- Error Illustration -->
      <div class="error-illustration">
        <div class="error-code">404</div>
        <div class="error-icon">🔍</div>
      </div>

      <!-- Error Content -->
      <div class="error-content">
        <h1 class="error-title">Pagina non trovata</h1>
        <p class="error-description">
          La pagina che stai cercando non esiste o è stata spostata.
        </p>

        <!-- Suggestions -->
        <div class="error-suggestions">
          <h3 class="suggestions-title">Cosa puoi fare:</h3>
          <ul class="suggestions-list">
            <li>Controlla l'URL per errori di battitura</li>
            <li>Torna alla homepage e naviga da lì</li>
            <li>Usa il menu di navigazione</li>
          </ul>
        </div>

        <!-- Actions -->
        <div class="error-actions">
          <BaseButton
            variant="primary"
            @click="goHome"
            class="mr-3"
          >
            🏠 Torna alla Home
          </BaseButton>

          <BaseButton
            variant="secondary"
            @click="goBack"
          >
            ← Indietro
          </BaseButton>
        </div>

        <!-- Quick Links -->
        <div class="quick-links">
          <h4 class="quick-links-title">Link utili:</h4>
          <div class="quick-links-grid">
            <router-link to="/welcome" class="quick-link">
              <span class="quick-link-icon">🎮</span>
              <span class="quick-link-text">Benvenuto</span>
            </router-link>

            <router-link to="/games" class="quick-link" v-if="authStore.isAuthenticated">
              <span class="quick-link-icon">📋</span>
              <span class="quick-link-text">Le Tue Partite</span>
            </router-link>

            <router-link to="/about" class="quick-link">
              <span class="quick-link-icon">ℹ️</span>
              <span class="quick-link-text">Info</span>
            </router-link>

            <router-link to="/help" class="quick-link">
              <span class="quick-link-icon">❓</span>
              <span class="quick-link-text">Aiuto</span>
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/js/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const goHome = () => {
  if (authStore.isAuthenticated) {
    router.push('/games')
  } else {
    router.push('/welcome')
  }
}

const goBack = () => {
  if (window.history.length > 1) {
    router.go(-1)
  } else {
    goHome()
  }
}
</script>

<style scoped>
.not-found-view {
  @apply min-h-screen flex items-center justify-center;
  @apply bg-gradient-to-br from-neutral-50 to-neutral-100;
  @apply px-4 py-8;
}

.not-found-container {
  @apply max-w-2xl mx-auto text-center;
}

/* Error Illustration */
.error-illustration {
  @apply relative mb-8;
}

.error-code {
  @apply text-8xl md:text-9xl font-black text-neutral-200;
  @apply select-none;
  font-family: 'Inter', sans-serif;
}

.error-icon {
  @apply absolute inset-0 flex items-center justify-center;
  @apply text-4xl md:text-5xl;
  animation: float 3s ease-in-out infinite;
}

@keyframes float {
  0%, 100% { transform: translateY(0px); }
  50% { transform: translateY(-10px); }
}

/* Error Content */
.error-content {
  @apply space-y-6;
}

.error-title {
  @apply text-3xl md:text-4xl font-bold text-neutral-900;
}

.error-description {
  @apply text-lg text-neutral-600 max-w-md mx-auto;
}

/* Suggestions */
.error-suggestions {
  @apply bg-white rounded-lg p-6 shadow-sm border border-neutral-200;
  @apply text-left max-w-md mx-auto;
}

.suggestions-title {
  @apply text-lg font-semibold text-neutral-900 mb-3;
}

.suggestions-list {
  @apply space-y-2 text-neutral-600;
}

.suggestions-list li {
  @apply flex items-start;
}

.suggestions-list li::before {
  content: "•";
  @apply text-brand-500 font-bold mr-2 mt-1;
}

/* Actions */
.error-actions {
  @apply flex flex-col sm:flex-row items-center justify-center space-y-3 sm:space-y-0 sm:space-x-3;
}

/* Quick Links */
.quick-links {
  @apply bg-white rounded-lg p-6 shadow-sm border border-neutral-200;
}

.quick-links-title {
  @apply text-base font-semibold text-neutral-900 mb-4;
}

.quick-links-grid {
  @apply grid grid-cols-2 sm:grid-cols-4 gap-3;
}

.quick-link {
  @apply flex flex-col items-center p-3 rounded-lg;
  @apply border border-neutral-200 hover:border-brand-300;
  @apply transition-all duration-200 hover:shadow-sm;
  @apply text-decoration-none;
}

.quick-link:hover {
  @apply transform -translate-y-0.5;
}

.quick-link-icon {
  @apply text-2xl mb-2;
}

.quick-link-text {
  @apply text-sm font-medium text-neutral-700;
}

/* Responsive */
@media (max-width: 640px) {
  .error-code {
    @apply text-6xl;
  }

  .error-icon {
    @apply text-3xl;
  }

  .error-title {
    @apply text-2xl;
  }

  .error-description {
    @apply text-base;
  }

  .quick-links-grid {
    @apply grid-cols-2 gap-2;
  }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
  .not-found-view {
    @apply bg-gradient-to-br from-neutral-900 to-neutral-800;
  }

  .error-code {
    @apply text-neutral-700;
  }

  .error-title {
    @apply text-neutral-100;
  }

  .error-description {
    @apply text-neutral-400;
  }

  .error-suggestions,
  .quick-links {
    @apply bg-neutral-800 border-neutral-700;
  }

  .suggestions-title,
  .quick-links-title {
    @apply text-neutral-100;
  }

  .suggestions-list {
    @apply text-neutral-400;
  }

  .quick-link {
    @apply border-neutral-700 hover:border-brand-500;
  }

  .quick-link-text {
    @apply text-neutral-300;
  }
}

/* Print styles */
@media print {
  .error-actions {
    @apply hidden;
  }

  .quick-links {
    @apply hidden;
  }
}
</style>