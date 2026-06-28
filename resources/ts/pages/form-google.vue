<script lang="ts" setup>
import { ref } from 'vue'

</script>

<template>
  <div>
    <VCard>
      <VCardTitle class="d-flex align-center gap-2 pa-4">
        <VIcon icon="ri-google-line" size="24" />
        Form Google
      </VCardTitle>
      
      <VCardText>
        <p class="mb-3">
          Klik link di bawah ini untuk mengakses form Google Forms resmi:
        </p>
        <VBtn
          href="https://docs.google.com/forms/d/e/1FAIpQLScvkV7-MhI1WZVLRqYSZV7gQ2_9RBOFFXu-mrDdzKpdBxsueQ/viewform"
          target="_blank"
          color="primary"
          size="large"
          prepend-icon="ri-external-link-line"
          class="mb-3"
        >
          Buka Google Forms
        </VBtn>
        <p class="text-caption text-medium-emphasis">
          Link akan membuka Google Forms di tab baru
        </p>
      </VCardText>
    </VCard>
  </div>
</template>

<style scoped>
.v-card {
  border-radius: 12px;
}
</style>
