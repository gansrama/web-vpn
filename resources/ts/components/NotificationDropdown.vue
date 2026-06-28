<script setup lang="ts">
import { ref, computed } from 'vue'
import { useNotifications } from '@/composables/useNotifications'
import { usePopupService } from '@/composables/usePopupService'

const { notifications, notificationCount, markAsRead, markAllAsRead } = useNotifications()
const { fetchRequestDetails } = usePopupService()

const isDropdownOpen = ref(false)
const dropdownRef = ref<HTMLElement>()

const unreadNotifications = computed(() => 
  notifications.value.filter(n => {
    // Since we don't have read status, consider notifications from last 24 hours as unread
    const oneDayAgo = new Date(Date.now() - 24 * 60 * 60 * 1000)
    return new Date(n.created_at) > oneDayAgo
  })
)

const readNotifications = computed(() => 
  notifications.value.filter(n => {
    // Consider notifications older than 24 hours as read
    const oneDayAgo = new Date(Date.now() - 24 * 60 * 60 * 1000)
    return new Date(n.created_at) <= oneDayAgo
  })
)

const getNotificationIcon = (type: string) => {
  switch (type) {
    case 'success':
      return 'ri-checkbox-circle-line'
    case 'warning':
      return 'ri-alert-line'
    case 'error':
      return 'ri-error-warning-line'
    default:
      return 'ri-information-line'
  }
}

const getNotificationColor = (type: string) => {
  switch (type) {
    case 'success':
      return 'success'
    case 'warning':
      return 'warning'
    case 'error':
      return 'error'
    default:
      return 'info'
  }
}

const formatDate = (dateString: string) => {
  const date = new Date(dateString)
  const now = new Date()
  const diffInHours = Math.floor((now.getTime() - date.getTime()) / (1000 * 60 * 60))
  
  if (diffInHours < 1) {
    const diffInMinutes = Math.floor((now.getTime() - date.getTime()) / (1000 * 60))
    return `${diffInMinutes} minute${diffInMinutes !== 1 ? 's' : ''} ago`
  } else if (diffInHours < 24) {
    return `${diffInHours} hour${diffInHours !== 1 ? 's' : ''} ago`
  } else {
    const diffInDays = Math.floor(diffInHours / 24)
    return `${diffInDays} day${diffInDays !== 1 ? 's' : ''} ago`
  }
}

const handleMarkAsRead = async (notificationId: number) => {
  await markAsRead(notificationId)
}

const handleMarkAllAsRead = async () => {
  await markAllAsRead()
  isDropdownOpen.value = false
}

const handleNotificationClick = async (notification: any) => {
  // Extract request data from notification
  let requestData = null
  let requestType = 'akses-logic' // default
  
  if (notification.data) {
    try {
      const data = JSON.parse(notification.data)
      console.log('Notification data:', data)
      
      // Determine request type and extract request ID
      if (data.formType === 'teleworking') {
        requestType = 'teleworking'
      }
      
      // Extract request ID from message or data
      let requestId = data.requestId
      if (!requestId && notification.message) {
        const match = notification.message.match(/Request ID: ([^\s]+)/)
        if (match) {
          requestId = match[1]
        }
      }
      
      if (requestId) {
        console.log(`Opening request details for ${requestType} ID: ${requestId}`)
        await fetchRequestDetails(String(requestId), requestType)
        isDropdownOpen.value = false
      }
    } catch (error) {
      console.error('Error parsing notification data:', error)
    }
  }
  
  // Also mark as read
  await markAsRead(notification.id)
}
</script>

<template>
  <div class="notification-dropdown">
    <VMenu
      v-model="isDropdownOpen"
      :close-on-content-click="false"
      location="bottom end"
      :offset="[0, 8]"
      max-width="380"
      transition="slide-y-transition"
    >
      <template #activator="{ props }">
        <VBtn
          icon
          variant="text"
          class="notification-btn"
          v-bind="props"
        >
          <VIcon icon="ri-notification-3-line" />
          <VBadge
            v-if="notificationCount > 0"
            :content="notificationCount > 99 ? '99+' : notificationCount.toString()"
            color="error"
            offset-x="2"
            offset-y="2"
          />
        </VBtn>
      </template>
      <VCard class="notification-card">
        <VCardTitle class="d-flex align-center pa-4">
          <VIcon icon="ri-notification-3-line" class="me-2" />
          Notifications
          <VSpacer />
          <VBtn
            v-if="unreadNotifications.length > 0"
            variant="text"
            size="small"
            @click="handleMarkAllAsRead"
          >
            Mark all as read
          </VBtn>
        </VCardTitle>

        <VDivider />

        <div class="notification-list" style="max-height: 400px; overflow-y: auto;">
          <!-- Unread Notifications -->
          <div v-if="unreadNotifications.length > 0">
            <div
              v-for="notification in unreadNotifications"
              :key="notification.id"
              class="notification-item unread"
              @click="handleNotificationClick(notification)"
            >
              <div class="d-flex align-start pa-3">
                <VAvatar
                  :color="getNotificationColor(notification.type)"
                  size="32"
                  class="me-3"
                >
                  <VIcon :icon="getNotificationIcon(notification.type)" />
                </VAvatar>
                <div class="flex-grow-1">
                  <div class="notification-title font-weight-medium">
                    {{ notification.title }}
                  </div>
                  <div class="notification-message text-body-2 mt-1">
                    {{ notification.message }}
                  </div>
                  <div class="notification-time text-caption text-medium-emphasis mt-1">
                    {{ formatDate(notification.created_at) }}
                  </div>
                </div>
                <VBtn
                  icon="ri-close-line"
                  variant="text"
                  size="x-small"
                  @click.stop="handleMarkAsRead(notification.id)"
                />
              </div>
            </div>
          </div>

          <!-- Read Notifications -->
          <div v-if="readNotifications.length > 0">
            <div
              v-for="notification in readNotifications"
              :key="notification.id"
              class="notification-item read"
              @click="handleNotificationClick(notification)"
            >
              <div class="d-flex align-start pa-3">
                <VAvatar
                  :color="getNotificationColor(notification.type)"
                  size="32"
                  class="me-3"
                  variant="tonal"
                >
                  <VIcon :icon="getNotificationIcon(notification.type)" />
                </VAvatar>
                <div class="flex-grow-1">
                  <div class="notification-title text-medium-emphasis">
                    {{ notification.title }}
                  </div>
                  <div class="notification-message text-body-2 text-medium-emphasis mt-1">
                    {{ notification.message }}
                  </div>
                  <div class="notification-time text-caption text-medium-emphasis mt-1">
                    {{ formatDate(notification.created_at) }}
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- No Notifications -->
          <div v-if="notifications.length === 0" class="text-center pa-6">
            <VIcon icon="ri-notification-off-line" size="48" class="text-medium-emphasis mb-3" />
            <div class="text-body-2 text-medium-emphasis">
              No notifications
            </div>
          </div>
        </div>

        <VDivider v-if="notifications.length > 0" />

        <VCardActions v-if="notifications.length > 0" class="pa-3">
          <VBtn
            variant="text"
            size="small"
            block
            to="/account-settings#notification"
            @click="isDropdownOpen = false"
          >
            View all notifications
          </VBtn>
        </VCardActions>
      </VCard>
    </VMenu>
  </div>
</template>

<style scoped>
.notification-dropdown {
  position: relative;
  display: inline-block;
}

.notification-card {
  border-radius: 12px;
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.12);
  margin-top: 8px;
}

.notification-list {
  max-height: 400px;
  overflow-y: auto;
}

.notification-item {
  cursor: pointer;
  transition: background-color 0.2s ease;
  border-bottom: 1px solid rgba(0, 0, 0, 0.08);
}

.notification-item:last-child {
  border-bottom: none;
}

.notification-item:hover {
  background-color: rgba(0, 0, 0, 0.04);
}

.notification-item.unread {
  background-color: rgba(var(--v-theme-primary), 0.04);
  border-left: 3px solid rgb(var(--v-theme-primary));
}

.notification-item.read {
  opacity: 0.7;
}

.notification-title {
  line-height: 1.4;
}

.notification-message {
  line-height: 1.4;
  max-width: 280px;
}

.notification-time {
  font-size: 11px;
}

.notification-btn {
  position: relative;
}

/* Ensure menu appears below button */
:deep(.v-menu__content) {
  margin-top: 0 !important;
}
</style>
