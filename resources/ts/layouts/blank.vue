<script setup lang="ts">
import { ref, onErrorCaptured } from 'vue'

const error = ref<Error | null>(null)

onErrorCaptured((err) => {
  console.error('Error in blank layout:', err)
  error.value = err
  return false
})

const retry = () => {
  error.value = null
  window.location.reload()
}
</script>

<template>
  <!-- Error state -->
  <div v-if="error" class="layout-error">
    <div class="error-content">
      <v-icon icon="mdi-alert-circle" size="60" color="error"></v-icon>
      <h3 class="mt-4">Error</h3>
      <p class="text-grey-600 mt-2">{{ error.message }}</p>
      <v-btn color="primary" class="mt-4" @click="retry">
        Retry
      </v-btn>
    </div>
  </div>

  <!-- Normal layout -->
  <div
    v-else
    class="layout-wrapper layout-blank"
    data-allow-mismatch
  >
    <RouterView />
  </div>
</template>

<style>
.layout-wrapper.layout-blank {
  flex-direction: column;
}

.layout-error {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  background: #f5f5f5;
}

.error-content {
  text-align: center;
}
</style>
