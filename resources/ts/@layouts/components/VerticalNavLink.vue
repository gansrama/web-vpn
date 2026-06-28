<script lang="ts" setup>
import type { NavLink } from '@layouts/types'

interface Props {
  item: NavLink
}

const props = defineProps<Props>()

// Validation - only in development
if (process.env.NODE_ENV === 'development') {
  if (!props.item.title) {
    console.warn('VerticalNavLink: item.title is required')
  }

  if (!props.item.to && !props.item.href) {
    console.warn('VerticalNavLink: item.to or item.href is required for navigation')
  }
}
</script>

<template>
  <li
    class="nav-link"
    :class="{ disabled: item.disable || false }"
  >
    <Component
      :is="item.to ? 'RouterLink' : 'a'"
      :to="item.to || undefined"
      :href="item.href || undefined"
      :target="item.target || undefined"
      :class="item.to ? 'router-link' : 'external-link'"
    >
      <VIcon
        :icon="(item.icon as string) || 'ri-checkbox-blank-circle-line'"
        class="nav-item-icon"
      />
      <!-- 👉 Title -->
      <span class="nav-item-title">
        {{ item.title || 'Untitled' }}
      </span>
      <!-- 👉 Badge -->
      <span
        v-if="item.badgeContent"
        class="nav-item-badge"
        :class="item.badgeClass || ''"
      >
        {{ item.badgeContent }}
      </span>
    </Component>
  </li>
</template>

<style lang="scss">
.layout-vertical-nav {
  .nav-link {
    margin: 0.125rem 0;
    
    &.disabled {
      opacity: 0.5;
      pointer-events: none;
    }
    
    a {
      display: flex;
      align-items: center;
      cursor: pointer;
      padding: 0.75rem 1rem;
      border-radius: 0.375rem;
      transition: all 0.2s ease-in-out;
      text-decoration: none;
      color: rgba(var(--v-theme-on-surface), var(--v-medium-emphasis-opacity));
      
      &:hover {
        background-color: rgba(var(--v-theme-primary), 0.08);
        color: rgb(var(--v-theme-primary));
      }
      
      &.router-link-active {
        background-color: rgba(var(--v-theme-primary), 0.12);
        color: rgb(var(--v-theme-primary));
        font-weight: 500;
      }
      
      &.external-link {
        &:hover {
          background-color: rgba(var(--v-theme-secondary), 0.08);
          color: rgb(var(--v-theme-secondary));
        }
      }
      
      .nav-item-icon {
        margin-right: 0.75rem;
        font-size: 1.25rem;
        min-width: 1.25rem;
        display: flex;
        align-items: center;
        justify-content: center;
      }
      
      .nav-item-title {
        flex: 1;
        font-size: 0.875rem;
        font-weight: 400;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
      }
      
      .nav-item-badge {
        margin-left: auto;
        font-size: 0.75rem;
        padding: 0.125rem 0.5rem;
        border-radius: 9999px;
        background-color: rgba(var(--v-theme-primary), 0.12);
        color: rgb(var(--v-theme-primary));
        font-weight: 500;
        min-width: fit-content;
      }
    }
  }
}
</style>
