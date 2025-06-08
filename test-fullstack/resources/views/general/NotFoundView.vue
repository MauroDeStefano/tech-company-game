<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-white flex items-center justify-center px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl w-full text-center">
      <!-- Error Illustration -->
      <div class="mb-8">
        <div class="text-8xl sm:text-9xl font-bold text-gray-300 mb-4">404</div>
        <div class="text-6xl mb-4">🔍</div>
      </div>

      <!-- Error Content -->
      <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8 mb-8">
        <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">Pagina non trovata</h1>
        <p class="text-lg text-gray-600 mb-8">
          La pagina che stai cercando non esiste o è stata spostata.
        </p>

        <!-- Suggestions -->
        <div class="bg-blue-50 rounded-xl p-6 mb-8 border border-blue-200">
          <h3 class="text-lg font-semibold text-blue-900 mb-4">Cosa puoi fare:</h3>
          <ul class="text-left space-y-2 text-blue-800">
            <li class="flex items-start gap-2">
              <span class="text-blue-500 mt-1">•</span>
              <span>Controlla l'URL per errori di battitura</span>
            </li>
            <li class="flex items-start gap-2">
              <span class="text-blue-500 mt-1">•</span>
              <span>Torna alla homepage e naviga da lì</span>
            </li>
            <li class="flex items-start gap-2">
              <span class="text-blue-500 mt-1">•</span>
              <span>Usa il menu di navigazione</span>
            </li>
          </ul>
        </div>

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center mb-8">
          <button
            @click="goHome"
            class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors duration-200 flex items-center justify-center gap-2"
          >
            <span class="text-lg">🏠</span>
            Torna alla Home
          </button>

          <button
            @click="goBack"
            class="px-6 py-3 border border-gray-300 hover:border-gray-400 hover:bg-gray-50 text-gray-700 font-semibold rounded-xl transition-colors duration-200 flex items-center justify-center gap-2"
          >
            <span class="text-lg">←</span>
            Indietro
          </button>
        </div>

        <!-- Quick Links -->
        <div class="bg-gray-50 rounded-xl p-6 border border-gray-200">
          <h4 class="text-lg font-semibold text-gray-900 mb-4">Link utili:</h4>
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <router-link 
              to="/welcome" 
              class="flex flex-col items-center gap-2 p-4 bg-white hover:bg-blue-50 border border-gray-200 hover:border-blue-300 rounded-lg transition-all duration-200 group"
            >
              <span class="text-2xl group-hover:scale-110 transition-transform duration-200">🎮</span>
              <span class="text-sm font-medium text-gray-700 group-hover:text-blue-700">Benvenuto</span>
            </router-link>

            <router-link 
              v-if="authStore.isAuthenticated"
              to="/games" 
              class="flex flex-col items-center gap-2 p-4 bg-white hover:bg-blue-50 border border-gray-200 hover:border-blue-300 rounded-lg transition-all duration-200 group"
            >
              <span class="text-2xl group-hover:scale-110 transition-transform duration-200">📋</span>
              <span class="text-sm font-medium text-gray-700 group-hover:text-blue-700">Le Tue Partite</span>
            </router-link>

            <router-link 
              to="/about" 
              class="flex flex-col items-center gap-2 p-4 bg-white hover:bg-blue-50 border border-gray-200 hover:border-blue-300 rounded-lg transition-all duration-200 group"
            >
              <span class="text-2xl group-hover:scale-110 transition-transform duration-200">ℹ️</span>
              <span class="text-sm font-medium text-gray-700 group-hover:text-blue-700">Info</span>
            </router-link>

            <router-link 
              to="/help" 
              class="flex flex-col items-center gap-2 p-4 bg-white hover:bg-blue-50 border border-gray-200 hover:border-blue-300 rounded-lg transition-all duration-200 group"
            >
              <span class="text-2xl group-hover:scale-110 transition-transform duration-200">❓</span>
              <span class="text-sm font-medium text-gray-700 group-hover:text-blue-700">Aiuto</span>
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