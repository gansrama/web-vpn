<script setup lang="ts">
interface Props {
  to?: string
  icon?: boolean
  color?: string
  variant?: string
  size?: string | number
}

const props = withDefaults(defineProps<Props>(), {
  icon: true,
  color: 'default',
  variant: 'text',
  size: 'default'
})
</script>

<template>
  <VBtn
    :icon="icon"
    :color="color"
    :variant="variant"
    :size="size"
    :to="to"
  >
    <slot />
  </VBtn>
</template>
