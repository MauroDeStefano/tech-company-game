<template>
  <div class="layout-auth min-h-screen flex items-center justify-center bg-neutral-50">
    <div class="auth-container w-full max-w-md">
      <!-- Logo Area -->
      <div class="mb-8 text-center">
        <img
          src="/logo.svg"
          alt="Tech Company Game"
          class="mx-auto h-12 mb-4"
        >
        <h1 class="text-2xl font-bold text-neutral-900">
          Tech Company Game
        </h1>
      </div>

      <!-- Auth Content Area -->
      <div class="bg-white shadow-lg rounded-lg p-8 border border-neutral-200">
        <router-view v-slot="{ Component }">
          <transition
            name="slide"
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
          class="text-neutral-500 hover:text-brand-600 mr-4"
        >
          Aiuto
        </router-link>
        <router-link
          to="/about"
          class="text-neutral-500 hover:text-brand-600"
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

<style scoped>
.slide-enter-active,
.slide-leave-active {
  transition: all 0.2s ease;
}

.slide-enter-from {
  opacity: 0;
  transform: translateX(20px);
}

.slide-leave-to {
  opacity: 0;
  transform: translateX(-20px);
}
</style>