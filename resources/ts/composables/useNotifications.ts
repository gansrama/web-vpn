import { ref, onMounted } from 'vue'

export interface Notification {
  id: number
  title: string
  message: string
  type: 'info' | 'success' | 'warning' | 'error' | 'general'
  target_type: string
  target_ids?: string
  data?: string
  is_global: number
  expires_at?: string
  created_at: string
  updated_at?: string
}

const notificationCount = ref(0)
const notifications = ref<Notification[]>([])

export function useNotifications() {
  const loadNotifications = async () => {
    try {
      console.log('Loading notifications...')
      const response = await fetch('/api/notifications', {
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
      })

      const result = await response.json()
      
      if (response.ok && result.success) {
        notifications.value = result.notifications || []
        // Since the table doesn't have read status, count recent notifications (last 24 hours)
        const oneDayAgo = new Date(Date.now() - 24 * 60 * 60 * 1000)
        const recentNotifications = notifications.value.filter(n => 
          new Date(n.created_at) > oneDayAgo
        )
        notificationCount.value = recentNotifications.length
        console.log('Notifications loaded:', notifications.value.length, 'Total, Recent:', notificationCount.value)
      }
    } catch (error) {
      console.error('Error loading notifications:', error)
    }
  }

  const markAsRead = async (notificationId: number) => {
    try {
      const response = await fetch(`/api/notifications/${notificationId}/read`, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
          'Content-Type': 'application/json'
        }
      })

      const result = await response.json()
      
      if (response.ok && result.success) {
        // Since marking as read is not implemented, just reduce count
        notificationCount.value = Math.max(0, notificationCount.value - 1)
      }
    } catch (error) {
      console.error('Error marking notification as read:', error)
    }
  }

  const markAllAsRead = async () => {
    try {
      const response = await fetch('/api/notifications/mark-all-read', {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
          'Content-Type': 'application/json'
        }
      })

      const result = await response.json()
      
      if (response.ok && result.success) {
        // Since marking all as read is not implemented, just reset count
        notificationCount.value = 0
      }
    } catch (error) {
      console.error('Error marking all notifications as read:', error)
    }
  }

  const addFormSubmissionNotification = async (formType: string, email: string, requestId: string, action: string) => {
    try {
      console.log('Adding form submission notification...')
      const response = await fetch('/api/notifications/form-submission', {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          formType,
          email,
          requestId,
          action
        })
      })

      const result = await response.json()
      
      if (response.ok && result.success) {
        console.log('Notification created successfully, ID:', result.notification_id)
        // Add notification to local state
        const newNotification: Notification = {
          id: result.notification_id,
          title: `Form ${action}`,
          message: `${formType} form submitted by ${email} - Request ID: ${requestId}`,
          type: 'success',
          target_type: 'all',
          is_global: 0,
          data: JSON.stringify({
            formType,
            email,
            requestId,
            action
          }),
          created_at: new Date().toISOString()
        }
        
        notifications.value.unshift(newNotification)
        // Increment count immediately
        notificationCount.value = notificationCount.value + 1
        console.log('Notification count updated to:', notificationCount.value)
      } else {
        console.error('Failed to create notification:', result)
      }
    } catch (error) {
      console.error('Error adding form submission notification:', error)
    }
  }

  return {
    notificationCount,
    notifications,
    loadNotifications,
    markAsRead,
    markAllAsRead,
    addFormSubmissionNotification
  }
}
