<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-white flex items-center justify-center p-4">
    <div class="text-center">
      <h1 class="text-2xl font-bold text-gray-900 mb-6">Loading...</h1>
      <div class="inline-flex items-center justify-center">
        <svg class="animate-spin h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
        </svg>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'LayoutAuth'
}
</script>
