import { ref } from 'vue'

export interface PopupItem {
  id: number
  type: 'akses-logic' | 'teleworking'
  [key: string]: any
}

const isPopupOpen = ref(false)
const popupItem = ref<PopupItem | null>(null)
const popupType = ref<'akses-logic' | 'teleworking'>('akses-logic')

export const usePopupService = () => {
  const showPopup = (item: PopupItem, type: 'akses-logic' | 'teleworking') => {
    console.log('showPopup called with:', { item, type })
    popupItem.value = item
    popupType.value = type
    isPopupOpen.value = true
  }

  const closePopup = () => {
    isPopupOpen.value = false
    popupItem.value = null
  }

  const fetchRequestDetails = async (requestId: string, type: 'akses-logic' | 'teleworking') => {
    try {
      // Use the appropriate show endpoint to get complete data with relationships
      const endpoint = type === 'teleworking' ? `/api/teleworking-requests/${requestId}` : `/api/akses-logic-requests/${requestId}`
      console.log(`Fetching from ${endpoint} for ID ${requestId}`)
      const response = await fetch(endpoint)
      const result = await response.json()
      
      console.log(`API Response for ${type}:`, result)
      
      if (result.success && result.data) {
        const item = result.data
        console.log(`Found item for ID ${requestId}:`, item)
        if (item) {
          showPopup(item, type)
          return true
        }
      }
      return false
    } catch (error) {
      console.error('Error fetching request details:', error)
      return false
    }
  }

  return {
    isPopupOpen,
    popupItem,
    popupType,
    showPopup,
    closePopup,
    fetchRequestDetails
  }
}
