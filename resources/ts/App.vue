<script setup lang="ts">
import { ref, onErrorCaptured } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const error = ref<Error | null>(null)
const isLoading = ref(true)
const isNavigating = ref(false)

// Capture errors from child components
onErrorCaptured((err) => {
  console.error('Error caught in App.vue:', err)
  error.value = err
  // Return false to prevent error from propagating further
  return false
})

// Handle global errors
window.addEventListener('error', (event) => {
  console.error('Global error:', event.error)
  error.value = event.error
})

window.addEventListener('unhandledrejection', (event) => {
  console.error('Unhandled promise rejection:', event.reason)
  error.value = event.reason as Error
})

// Track navigation state
router.beforeEach(() => {
  isNavigating.value = true
})

router.afterEach(() => {
  isNavigating.value = false
})

// Hide loading when app is ready
setTimeout(() => {
  isLoading.value = false
}, 100)

const retry = () => {
  error.value = null
  isLoading.value = true
  window.location.reload()
}

const goHome = () => {
  window.location.href = '/'
}
</script>

<template>
  <VApp>
    <!-- Initial loading state -->
    <div v-if="isLoading && !error" class="loading-screen">
      <div class="loading-content">
        <v-progress-circular indeterminate color="primary" size="50"></v-progress-circular>
        <p class="mt-4">Loading...</p>
      </div>
    </div>

    <!-- Navigation loading indicator -->
    <div v-if="isNavigating && !isLoading && !error" class="navigation-loading">
      <v-progress-linear indeterminate color="primary" height="3"></v-progress-linear>
    </div>

    <!-- Error state -->
    <div v-else-if="error" class="error-screen">
      <div class="error-content">
        <v-icon icon="mdi-alert-circle" size="80" color="error"></v-icon>
        <h2 class="mt-4">Something went wrong</h2>
        <p class="text-grey-600 mt-2">{{ error.message }}</p>
        <v-btn color="primary" class="mt-4" @click="retry">
          Retry
        </v-btn>
        <v-btn color="grey" class="mt-4 ml-2" @click="goHome">
          Go Home
        </v-btn>
      </div>
    </div>

    <!-- Normal app -->
    <RouterView v-else />
  </VApp>
</template>

<style scoped>
.loading-screen,
.error-screen {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f5f5f5;
  z-index: 9999;
}

.loading-content,
.error-content {
  text-align: center;
}

.navigation-loading {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  z-index: 9998;
}
</style>
