<script lang="ts" setup>
// Public layout - no authentication required
// Used for landing page and other public pages
</script>

<template>
  <VApp class="public-layout">
    <VMain>
      <slot />
    </VMain>
  </VApp>
</template>

<style scoped>
.public-layout {
  min-height: 100vh;
}
</style>
