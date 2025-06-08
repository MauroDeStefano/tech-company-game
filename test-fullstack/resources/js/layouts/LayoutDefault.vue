<template>
  <div class="min-h-screen flex flex-col">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
      <nav class="max-w-7xl mx-auto flex items-center justify-between py-4 px-4 sm:px-6 lg:px-8">
        <div class="flex items-center">
          <img src="/logo.svg" alt="Tech Company Game" class="h-8 mr-3">
          <h1 class="text-xl font-bold text-gray-900">Tech Company Game</h1>
        </div>
        <div class="flex items-center space-x-6">
          <router-link
            to="/welcome"
            class="text-gray-600 hover:text-blue-600 transition-colors duration-200"
          >
            Home
          </router-link>
          <router-link
            to="/about"
            class="text-gray-600 hover:text-blue-600 transition-colors duration-200"
          >
            About
          </router-link>
        </div>
      </nav>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-6">
      <router-view v-slot="{ Component }">
        <transition
          enter-active-class="transition-all duration-300"
          leave-active-class="transition-all duration-300"
          enter-from-class="opacity-0 translate-y-4"
          enter-to-class="opacity-100 translate-y-0"
          leave-from-class="opacity-100 translate-y-0"
          leave-to-class="opacity-0 translate-y-4"
          mode="out-in"
        >
          <component :is="Component" />
        </transition>
      </router-view>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-50 border-t border-gray-200 py-8">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <p class="text-gray-600">
          © {{ new Date().getFullYear() }} Tech Company Game. Tutti i diritti riservati.
        </p>
        <div class="mt-4 flex justify-center space-x-6">
          <a href="/privacy" class="text-gray-500 hover:text-blue-600 transition-colors duration-200">Privacy</a>
          <a href="/terms" class="text-gray-500 hover:text-blue-600 transition-colors duration-200">Termini</a>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
// Eventuali logiche specifiche del layout
</script>
