<!--
  ⚡ LoadingView.vue - Smart Redirect Component
  
  Questo componente viene mostrato solo per un attimo durante il
  "smart redirect" dalla root (/). Il guard si occupa di redirectare
  subito all'URL corretto.
-->
<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-900 via-purple-800 to-indigo-900 flex items-center justify-center">
    <div class="text-center">
      <!-- Logo o icona animata -->
      <div class="mb-8">
        <div class="w-20 h-20 mx-auto">
          <!-- Spinner animato -->
          <div class="animate-spin rounded-full h-20 w-20 border-4 border-white border-t-transparent"></div>
        </div>
      </div>

      <!-- Titolo -->
      <h1 class="text-4xl font-bold text-white mb-4">
        Tech Company Game
      </h1>

      <!-- Sottotitolo -->
      <p class="text-xl text-gray-300 mb-8">
        Caricamento in corso...
      </p>

      <!-- Progress indicator -->
      <div class="w-64 mx-auto bg-gray-700 rounded-full h-2">
        <div class="bg-gradient-to-r from-blue-400 to-purple-500 h-2 rounded-full animate-pulse"
             :style="{ width: progress + '%' }">
        </div>
      </div>

      <!-- Fallback manual redirect (solo per debug) -->
      <div v-if="showFallback" class="mt-8 space-x-4">
        <router-link 
          to="/welcome" 
          class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors"
        >
          🏠 Vai a Welcome
        </router-link>
        <router-link 
          to="/games" 
          class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg transition-colors"
        >
          🎮 Vai ai Giochi
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

// Props reattive
const progress = ref(0)
const showFallback = ref(false)

// Simula loading progress
onMounted(() => {
  // Progress animation
  const progressInterval = setInterval(() => {
    progress.value += 10
    if (progress.value >= 100) {
      clearInterval(progressInterval)
    }
  }, 50)

  // Fallback se il redirect non funziona (solo per debug)
  setTimeout(() => {
    showFallback.value = true
    console.warn('🚨 Smart redirect fallback activated. Check your guards!')
  }, 3000)
})
</script>

<style scoped>
/* Animazioni personalizzate se necessario */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.animate-fadeIn {
  animation: fadeIn 0.6s ease-out;
}
</style>