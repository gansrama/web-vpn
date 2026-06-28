<script setup lang="ts">
// Simple auth provider component for development
const handleSocialLogin = (provider: string) => {
  console.log(`Login with ${provider}`)
}
</script>

<template>
  <div class="auth-providers">
    <VDivider class="my-4">
      <span class="text-caption">Or</span>
    </VDivider>
    
    <div class="d-flex gap-2">
      <VBtn
        variant="outlined"
        size="small"
        @click="handleSocialLogin('google')"
      >
        <VIcon icon="ri-google-line" class="me-1" />
        Google
      </VBtn>
      
      <VBtn
        variant="outlined"
        size="small"
        @click="handleSocialLogin('github')"
      >
        <VIcon icon="ri-github-line" class="me-1" />
        GitHub
      </VBtn>
    </div>
  </div>
</template>
