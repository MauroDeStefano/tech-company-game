<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50">
    <div class="w-full max-w-md">
      <!-- Logo Area -->
      <div class="mb-8 text-center">
        <img
          src="/logo.svg"
          alt="Tech Company Game"
          class="mx-auto h-12 mb-4"
        >
        <h1 class="text-2xl font-bold text-gray-900">
          Tech Company Game
        </h1>
      </div>

      <!-- Auth Content Area -->
      <div class="bg-white shadow-lg rounded-lg p-8 border border-gray-200">
        <router-view v-slot="{ Component }">
          <transition
            enter-active-class="transition-all duration-300"
            leave-active-class="transition-all duration-300"
            enter-from-class="opacity-0 translate-x-4"
            enter-to-class="opacity-100 translate-x-0"
            leave-from-class="opacity-100 translate-x-0"
            leave-to-class="opacity-0 -translate-x-4"
            mode="out-in"
          >
            <component :is="Component" />
          </transition>
        </router-view>
      </div>

      <!-- Footer Links -->
      <div class="mt-6 text-center">
        <router-link
          to="/help"
          class="text-gray-500 hover:text-blue-600 mr-4 transition-colors duration-200"
        >
          Aiuto
        </router-link>
        <router-link
          to="/about"
          class="text-gray-500 hover:text-blue-600 transition-colors duration-200"
        >
          Chi Siamo
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
// Eventuali logiche specifiche del layout auth
</script>
