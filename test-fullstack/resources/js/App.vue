<template>
  <div>
    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
      <div class="container">
        <a class="navbar-tail" href="#">
          <strong>🎮 Tech Company Game</strong>
        </a>
        <span class="navbar-text">
          Gestionale Software House
        </span>
      </div>
    </nav>
    
    <!-- Main Content - QUI è dove verranno renderizzate le tue pagine -->
    <div class="py-5">
      <!-- 🔥 QUESTA è la magia: router-view renderizza dinamicamente i componenti delle route -->
      <router-view />
    </div>
    
    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
      <div class="container text-center">
        <p class="mb-0">Powered by Laravel + Vue.js 3 + Vite</p>
      </div>
    </footer>
  </div>
</template>

<script setup>
// 🎯 Con <script setup>, non serve importare router-view
// Vue Router lo registra automaticamente come componente globale
</script>

<style>
/* Stili globali dell'app se necessari */
body {
  margin: 0;
  padding: 0;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 15px;
}
</style>