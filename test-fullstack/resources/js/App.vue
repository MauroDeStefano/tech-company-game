<template>
  <div>
    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
      <div class="container">
        <a class="navbar-brand" href="#">
          <strong>Laravel + Vue.js</strong>
        </a>
        <span class="navbar-text">
          Template pronto all'uso
        </span>
      </div>
    </nav>
    
    <!-- Main Content -->
    <div class="py-5">
      <example-component></example-component>
    </div>
    
    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
      <div class="container text-center">
        <p class="mb-0">Powered by Laravel + Vue.js 3 + Vite</p>
      </div>
    </footer>
  </div>
</template>

<script>
import ExampleComponent from './components/ExampleComponent.vue';

export default {
  name: 'App',
  components: {
    ExampleComponent
  }
}
</script>
