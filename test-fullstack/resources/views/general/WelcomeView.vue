<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 via-purple-50 to-pink-50 flex items-center justify-center px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl w-full text-center">
      <!-- Main Welcome Content -->
      <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 p-8 sm:p-12 mb-8">
        <!-- Title with Animation -->
        <div class="mb-8">
          <h1 class="text-4xl sm:text-5xl font-bold text-gray-900 mb-4 tracking-tight">
            <span class="text-6xl mb-4 block animate-bounce">🎮</span>
            Benvenuto in Tech Company Game!
          </h1>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center mb-8">
          <button
            @click="goToLogin"
            class="px-8 py-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl"
          >
            Vai al Login
          </button>
          
        </div>

        <!-- Feature Highlights -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
          <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
            <div class="text-2xl mb-2">💼</div>
            <h3 class="font-semibold text-blue-900 mb-1">Gestisci la tua Software House</h3>
            <p class="text-sm text-blue-700">Costruisci e fai crescere la tua azienda tecnologica</p>
          </div>

          <div class="bg-green-50 rounded-lg p-4 border border-green-200">
            <div class="text-2xl mb-2">👥</div>
            <h3 class="font-semibold text-green-900 mb-1">Assembla il Team</h3>
            <p class="text-sm text-green-700">Assumi sviluppatori e commerciali di talento</p>
          </div>

          <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
            <div class="text-2xl mb-2">🚀</div>
            <h3 class="font-semibold text-purple-900 mb-1">Completa Progetti</h3>
            <p class="text-sm text-purple-700">Sviluppa software e genera profitti</p>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'

const router = useRouter()
const route = useRoute()

const debugInfo = computed(() => {
  return {
    currentRoute: route.name,
    currentPath: route.path,
    timestamp: new Date().toLocaleTimeString(),
    userAgent: navigator.userAgent.substring(0, 50) + '...',
    viewport: {
      width: window.innerWidth,
      height: window.innerHeight
    }
  }
})

const goToLogin = () => {
  console.log('Going to login...')
  router.push('/login')
}

</script>