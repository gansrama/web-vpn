<script lang="ts" setup>
import { ref, onErrorCaptured } from 'vue'
import DefaultLayoutWithVerticalNav from './components/DefaultLayoutWithVerticalNav.vue'

const error = ref<Error | null>(null)

onErrorCaptured((err) => {
  console.error('Error in default layout:', err)
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
      <h3 class="mt-4">Layout Error</h3>
      <p class="text-grey-600 mt-2">{{ error.message }}</p>
      <v-btn color="primary" class="mt-4" @click="retry">
        Retry
      </v-btn>
    </div>
  </div>

  <!-- Normal layout -->
  <DefaultLayoutWithVerticalNav v-else>
    <RouterView />
  </DefaultLayoutWithVerticalNav>
</template>

<style lang="scss">
// As we are using `layouts` plugin we need its styles to be imported
@use "@layouts/styles/default-layout";

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
