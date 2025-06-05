<template>
  <div class="layout-default">
    <!-- Header -->
    <header class="layout-header">
      <nav class="container mx-auto flex items-center justify-between py-4">
        <div class="logo flex items-center">
          <img src="/logo.svg" alt="Tech Company Game" class="h-8 mr-3">
          <h1 class="text-xl font-bold text-neutral-900">Tech Company Game</h1>
        </div>
        <div class="nav-links space-x-4">
          <router-link
            to="/welcome"
            class="text-neutral-600 hover:text-brand-600 transition-colors"
          >
            Home
          </router-link>
          <router-link
            to="/about"
            class="text-neutral-600 hover:text-brand-600 transition-colors"
          >
            About
          </router-link>
        </div>
      </nav>
    </header>

    <!-- Main Content Area -->
    <main class="layout-content container mx-auto px-4 py-6">
      <router-view v-slot="{ Component }">
        <transition
          name="fade"
          mode="out-in"
        >
          <component :is="Component" />
        </transition>
      </router-view>
    </main>

    <!-- Footer -->
    <footer class="layout-footer bg-neutral-50 py-8">
      <div class="container mx-auto text-center">
        <p class="text-neutral-600">
          © {{ new Date().getFullYear() }} Tech Company Game. Tutti i diritti riservati.
        </p>
        <div class="mt-4 space-x-4">
          <a href="/privacy" class="text-neutral-500 hover:text-brand-600">Privacy</a>
          <a href="/terms" class="text-neutral-500 hover:text-brand-600">Termini</a>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
// Eventuali logiche specifiche del layout
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>