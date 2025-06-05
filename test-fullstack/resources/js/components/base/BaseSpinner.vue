<template>
  <div class="layout-auth">
    <h1>Loading...</h1>
    <div class="spinner"></div>
  </div>
</template>

<script>
export default {
  name: 'LayoutAuth'
}
</script>

<style scoped>
.layout-auth {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100vh;
  font-family: sans-serif;
}

.spinner {
  margin-top: 20px;
  width: 40px;
  height: 40px;
  border: 4px solid #ddd;
  border-top: 4px solid #3490dc;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>
