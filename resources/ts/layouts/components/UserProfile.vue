<script setup lang="ts">
import avatar1 from '@images/avatars/avatar-1.png'
import { useAuth } from '@/composables/useAuth'
import { useNotifications } from '@/composables/useNotifications'
import NotificationDropdown from '@/components/NotificationDropdown.vue'
import RequestDetailPopup from '@/components/RequestDetailPopup.vue'
import { ref, computed, onMounted } from 'vue'

const { logout, user, isAuthenticated, checkAuth, updateUser } = useAuth()
const { notificationCount, loadNotifications } = useNotifications()

// Use avatar from user data or localStorage, fallback to default avatar
const profileAvatar = computed(() => {
  if (user.value?.avatar) {
    return user.value.avatar
  }
  return localStorage.getItem('userAvatar') || avatar1
})

const handleLogout = async () => {
  await logout()
}

// Listen for avatar update events
window.addEventListener('avatarUpdated', (event: any) => {
  localStorage.setItem('userAvatar', event.detail.avatar)
  // Update user data if available
  if (user.value) {
    updateUser({ ...user.value, avatar: event.detail.avatar })
  }
})

// Listen for user update events
window.addEventListener('userUpdated', (event: any) => {
  updateUser(event.detail)
  console.log('User updated:', event.detail)
})

onMounted(() => {
  // Check authentication and load user data
  checkAuth()
  loadNotifications()
})
</script>

<template>
  <div class="d-flex align-center">
    <!-- Notification Icon -->
    <div class="me-2">
      <NotificationDropdown />
    </div>

    <VBadge
      dot
      location="bottom right"
      offset-x="3"
      offset-y="3"
      color="success"
      bordered
    >
      <VAvatar
        class="cursor-pointer"
        color="primary"
        variant="tonal"
      >
        <VImg :src="profileAvatar" />

        <!-- SECTION Menu -->
        <VMenu
          activator="parent"
          width="230"
          location="bottom end"
          offset="14px"
        >
        <VList>
          <!-- 👉 User Avatar & Name -->
          <VListItem>
            <template #prepend>
              <VListItemAction start>
                <VBadge
                  dot
                  location="bottom right"
                  offset-x="3"
                  offset-y="3"
                  color="success"
                >
                  <VAvatar
                    color="primary"
                    variant="tonal"
                  >
                    <VImg :src="profileAvatar" />
                  </VAvatar>
                </VBadge>
              </VListItemAction>
            </template>

            <VListItemTitle class="font-weight-semibold">
              {{ user?.name || 'Guest User' }}
            </VListItemTitle>
            <VListItemSubtitle>{{ user?.email || 'Not logged in' }}</VListItemSubtitle>
          </VListItem>
          <VDivider class="my-2" />

          <!-- 👉 Profile -->
          <VListItem link to="/account-settings">
            <template #prepend>
              <VIcon
                class="me-2"
                icon="ri-user-line"
                size="22"
              />
            </template>

            <VListItemTitle>Profile</VListItemTitle>
          </VListItem>

          <!-- 👉 Settings -->
          <VListItem link to="/account-settings#security">
            <template #prepend>
              <VIcon
                class="me-2"
                icon="ri-settings-4-line"
                size="22"
              />
            </template>

            <VListItemTitle>Settings</VListItemTitle>
          </VListItem>

          <!-- 👉 Notifications -->
          <VListItem link to="/account-settings#notification">
            <template #prepend>
              <VIcon
                class="me-2"
                icon="ri-notification-3-line"
                size="22"
              />
            </template>

            <VListItemTitle>Notifications</VListItemTitle>
          </VListItem>

          

          <!-- 👉 FAQ -->
          <VListItem link>
            <template #prepend>
              <VIcon
                class="me-2"
                icon="ri-question-line"
                size="22"
              />
            </template>

            <VListItemTitle>FAQ</VListItemTitle>
          </VListItem>

          <!-- Divider -->
          <VDivider class="my-2" />

          <!-- 👉 Logout -->
          <VListItem @click="handleLogout" style="cursor: pointer;">
            <template #prepend>
              <VIcon
                class="me-2"
                icon="ri-logout-box-r-line"
                size="22"
              />
            </template>

            <VListItemTitle>Logout</VListItemTitle>
          </VListItem>
        </VList>
      </VMenu>
      <!-- !SECTION -->
    </VAvatar>
  </VBadge>
  
  <!-- Request Detail Popup -->
  <RequestDetailPopup />
  </div>
</template>
