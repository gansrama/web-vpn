<script lang="ts" setup>
import { ref, onMounted, computed } from 'vue'
import { useTheme } from 'vuetify'
import type { VpnServer } from '@/types'

const theme = useTheme()
const search = ref('')
const loading = ref(true)
const currentPage = ref(1)
const itemsPerPage = ref(10)

// Modal states
const showAddServerModal = ref(false)
const showViewModal = ref(false)
const showEditModal = ref(false)
const showDeleteModal = ref(false)
const selectedServer = ref<VpnServer | null>(null)

// VPN Servers data from API
const vpnServers = ref<VpnServer[]>([])

// Table headers
const headers = [
  { title: 'IP ADDRESS', key: 'ip_address', sortable: true },
  { title: 'NAMA SISTEM', key: 'nama_sistem', sortable: true },
  { title: 'PROJECT', key: 'project', sortable: true },
  { title: 'LOKASI SERVER', key: 'server_location', sortable: true },
  { title: 'STATUS', key: 'is_active', sortable: true },
  { title: 'ACTIONS', key: 'actions', sortable: false, align: 'center' as const }
]

// Computed property for filtered data
const filteredVpnServers = computed(() => {
  if (!search.value) return vpnServers.value
  
  const searchTerm = search.value.toLowerCase()
  return vpnServers.value.filter(server =>
    server.ip_address.toLowerCase().includes(searchTerm) ||
    server.nama_sistem.toLowerCase().includes(searchTerm) ||
    server.project?.toLowerCase().includes(searchTerm) ||
    server.server_location.toLowerCase().includes(searchTerm)
  )
})

// Status color helper
const getStatusColor = (isActive: boolean) => {
  return isActive ? 'success' : 'error'
}

// Status text helper
const getStatusText = (isActive: boolean) => {
  return isActive ? 'Active' : 'Inactive'
}

// Fetch VPN servers from API
const fetchVpnServers = async () => {
  try {
    loading.value = true
    const response = await fetch('/api/vpn-servers')
    const data = await response.json()
    
    if (data.success) {
      vpnServers.value = data.data
      // Add static "All Product" data if not exists
      const allProductExists = vpnServers.value.some(server => 
        server.nama_sistem === 'All Product' && server.ip_address === 'IP All Product'
      )
      if (!allProductExists) {
        vpnServers.value.unshift({
          id: 0, // Static ID
          ip_address: 'IP All Product',
          nama_sistem: 'All Product',
          project: '',
          server_location: 'Jakarta, Indonesia',
          is_active: true,
          created_at: new Date().toISOString(),
          updated_at: new Date().toISOString()
        })
      }
    } else {
      console.error('Failed to fetch VPN servers:', data.message)
    }
  } catch (error) {
    console.error('Error fetching VPN servers:', error)
  } finally {
    loading.value = false
  }
}

// Statistics
const totalServers = computed(() => vpnServers.value.length)
const activeServers = computed(() => vpnServers.value.filter(server => server.is_active).length)
const inactiveServers = computed(() => vpnServers.value.filter(server => !server.is_active).length)

// Form data for new server
const newServerForm = ref({
  nama_sistem: '',
  server_location: '',
  ip_address: '',
  project: '',
  is_active: true
})

// Form data for editing server
const editServerForm = ref({
  id: 0,
  nama_sistem: '',
  server_location: '',
  ip_address: '',
  project: '',
  is_active: true
})

// Form loading state
const formLoading = ref(false)

// Static data for locations and projects
const locations = ref([
  'Jakarta, Indonesia',
  'Singapore',
  'United States',
  'Europe',
  'Australia'
])

const projects = ref([
  'Jaki Dev',
  'Jaki Prod',
  'Jaki Staging',
  'CRM Dev',
  'CRM Prod',
  'CRM-Staging',
  'Website Corona Dev',
  'Website Corona Prod',
  'Website SIB Dev',
  'Website SIB Prod',
  'Web Corp Dev',
  'Web Corp Prod',
  'Smart City Apps Dev',
  'Smart City Apps Prod',
  'KSBB Dinamis Dev',
  'KSBB Dinamis Prod',
  'Pantau Banjir Dev',
  'Pantau Banjir Prod',
  'Smart Employee Dev',
  'Smart Employee Prod',
  'Plus Jakarta Dev',
  'Plus Jakarta Prod',
  'Plus Jakarta On-Going',
  'Vaksinasi Corona Dev',
  'Vaksinasi Corona Prod',
  'Monitoring Apel Dev',
  'Portal Stunting Dev',
  'Portal Stunting Prod',
  'SIMPDP Dev',
  'City Data / Open Data Dev',
  'City Data / Open Data Prod',
  'Smart Change Prod',
  'LMS Study Jakarta Dev',
  'LMS Study Jakarta Prod',
  'Buku Tamu Dev',
  'Buku Tamu Prod',
  'Rekrutmen Dev',
  'Rekrutmen Prod',
  'Kelas Warga Dev',
  'Kelas Warga Prod',
  'Minio TW',
  'Karawangkab-dev',
  'Karawangkab-prod',
  'PoC Dynatrace',
  'Docmost',
  'VM BLUD OSDM-Dev',
  'OSDM Prod',
  'web pusdatin dev',
  'BLUD_Aceh_DEV',
  'lomba photo',
  'Jaki Staging On-Prem',
  'JakGo Prod',
  'JakGo DB',
  'AI Project gemilang.jsclab.id'
])

// Form validation
const isFormValid = computed(() => {
  return newServerForm.value.nama_sistem.trim() !== '' &&
         newServerForm.value.server_location.trim() !== '' &&
         newServerForm.value.ip_address.trim() !== ''
})

// Edit form validation
const isEditFormValid = computed(() => {
  return editServerForm.value.nama_sistem.trim() !== '' &&
         editServerForm.value.server_location.trim() !== '' &&
         editServerForm.value.ip_address.trim() !== ''
})

// IP address validation
const isValidIpAddress = computed(() => {
  const ipRegex = /^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/
  return ipRegex.test(newServerForm.value.ip_address) || newServerForm.value.ip_address === 'IP All Product'
})

// Edit IP address validation
const isValidEditIpAddress = computed(() => {
  const ipRegex = /^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/
  return ipRegex.test(editServerForm.value.ip_address) || editServerForm.value.ip_address === 'IP All Product'
})

// Add server function
const addServer = async () => {
  if (!isFormValid.value) {
    alert('Please fill all required fields')
    return
  }

  if (!isValidIpAddress.value) {
    alert('Please enter a valid IP address')
    return
  }

  try {
    formLoading.value = true
    
    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    
    const response = await fetch('/api/vpn-servers', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        ...(csrfToken && { 'X-CSRF-TOKEN': csrfToken })
      },
      body: JSON.stringify(newServerForm.value)
    })

    const result = await response.json()

    if (result.success) {
      // Close modal
      showAddServerModal.value = false
      
      // Reset form
      resetForm()
      
      // Refresh data
      await fetchVpnServers()
      
      // Show success message
      showSuccessMessage('VPN Server added successfully!')
    } else {
      showErrorMessage(result.message)
    }
  } catch (error) {
    console.error('Error adding server:', error)
    showErrorMessage('An error occurred while adding server')
  } finally {
    formLoading.value = false
  }
}

// Reset form
const resetForm = () => {
  newServerForm.value = {
    nama_sistem: '',
    server_location: '',
    ip_address: '',
    project: '',
    is_active: true
  }
}

// Show success message
const showSuccessMessage = (message: string) => {
  // Simple success notification
  const notification = document.createElement('div')
  notification.className = 'success-notification'
  notification.innerHTML = `
    <div class="notification-content">
      <VIcon icon="ri-checkbox-circle-line" color="success" />
      <span>${message}</span>
    </div>
  `
  
  // Add styles
  notification.innerHTML += `
    <style>
      .success-notification {
        position: fixed;
        top: 20px;
        right: 20px;
        background: #4CAF50;
        color: white;
        padding: 16px 24px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 9999;
        animation: slideInRight 0.3s ease-out;
      }
      .notification-content {
        display: flex;
        align-items: center;
        gap: 12px;
      }
      @keyframes slideInRight {
        from {
          transform: translateX(100%);
          opacity: 0;
        }
        to {
          transform: translateX(0);
          opacity: 1;
        }
      }
    </style>
  `
  
  document.body.appendChild(notification)
  
  // Auto remove after 3 seconds
  setTimeout(() => {
    notification.style.animation = 'slideOutRight 0.3s ease-in'
    setTimeout(() => notification.remove(), 300)
  }, 3000)
}

// Show error message
const showErrorMessage = (message: string) => {
  const notification = document.createElement('div')
  notification.className = 'error-notification'
  notification.innerHTML = `
    <div class="notification-content">
      <VIcon icon="ri-error-warning-line" color="white" />
      <span>${message}</span>
    </div>
  `
  
  // Add styles
  notification.innerHTML += `
    <style>
      .error-notification {
        position: fixed;
        top: 20px;
        right: 20px;
        background: #EF4444;
        color: white;
        padding: 16px 24px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 9999;
        animation: slideInRight 0.3s ease-out;
      }
      @keyframes slideOutRight {
        to {
          transform: translateX(100%);
          opacity: 0;
        }
      }
    </style>
  `
  
  document.body.appendChild(notification)
  
  // Auto remove after 5 seconds
  setTimeout(() => {
    notification.style.animation = 'slideOutRight 0.3s ease-in'
    setTimeout(() => notification.remove(), 300)
  }, 5000)
}

// Open add server modal
const openAddServerModal = () => {
  showAddServerModal.value = true
  resetForm()
}

// Close add server modal
const closeAddServerModal = () => {
  showAddServerModal.value = false
  resetForm()
}

// View server details
const viewServer = (server: VpnServer) => {
  selectedServer.value = server
  showViewModal.value = true
}

// Close view modal
const closeViewModal = () => {
  showViewModal.value = false
  selectedServer.value = null
}

// Edit server
const editServer = (server: VpnServer) => {
  if (server.id === 0) {
    showErrorMessage('Cannot edit the static "All Product" entry')
    return
  }
  
  editServerForm.value = {
    id: server.id,
    nama_sistem: server.nama_sistem,
    server_location: server.server_location,
    ip_address: server.ip_address,
    project: server.project || '',
    is_active: server.is_active
  }
  showEditModal.value = true
}

// Close edit modal
const closeEditModal = () => {
  showEditModal.value = false
  editServerForm.value = {
    id: 0,
    nama_sistem: '',
    server_location: '',
    ip_address: '',
    project: '',
    is_active: true
  }
}

// Update server
const updateServer = async () => {
  if (!isEditFormValid.value) {
    alert('Please fill all required fields')
    return
  }

  if (!isValidEditIpAddress.value) {
    alert('Please enter a valid IP address')
    return
  }

  // Prevent updating static entry
  if (editServerForm.value.id === 0) {
    showErrorMessage('Cannot update the static "All Product" entry')
    return
  }

  try {
    formLoading.value = true
    
    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    
    const response = await fetch(`/api/vpn-servers/${editServerForm.value.id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        ...(csrfToken && { 'X-CSRF-TOKEN': csrfToken })
      },
      body: JSON.stringify({
        nama_sistem: editServerForm.value.nama_sistem,
        server_location: editServerForm.value.server_location,
        ip_address: editServerForm.value.ip_address,
        project: editServerForm.value.project,
        is_active: editServerForm.value.is_active
      })
    })

    const result = await response.json()

    if (result.success) {
      // Close modal
      closeEditModal()
      
      // Refresh data
      await fetchVpnServers()
      
      // Show success message
      showSuccessMessage('VPN Server updated successfully!')
    } else {
      showErrorMessage(result.message)
    }
  } catch (error) {
    console.error('Error updating server:', error)
    showErrorMessage('An error occurred while updating server')
  } finally {
    formLoading.value = false
  }
}

// Delete server
const deleteServer = (server: VpnServer) => {
  if (server.id === 0) {
    showErrorMessage('Cannot delete the static "All Product" entry')
    return
  }
  
  selectedServer.value = server
  showDeleteModal.value = true
}

// Close delete modal
const closeDeleteModal = () => {
  showDeleteModal.value = false
  selectedServer.value = null
}

// Confirm delete
const confirmDelete = async () => {
  if (!selectedServer.value) return
  
  // Prevent deleting static entry
  if (selectedServer.value.id === 0) {
    showErrorMessage('Cannot delete the static "All Product" entry')
    return
  }

  try {
    formLoading.value = true
    
    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    
    const response = await fetch(`/api/vpn-servers/${selectedServer.value.id}`, {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        ...(csrfToken && { 'X-CSRF-TOKEN': csrfToken })
      }
    })

    const result = await response.json()

    if (result.success) {
      // Close modal
      closeDeleteModal()
      
      // Refresh data
      await fetchVpnServers()
      
      // Show success message
      showSuccessMessage('VPN Server deleted successfully!')
    } else {
      showErrorMessage(result.message)
    }
  } catch (error) {
    console.error('Error deleting server:', error)
    showErrorMessage('An error occurred while deleting server')
  } finally {
    formLoading.value = false
  }
}

// Lifecycle
onMounted(() => {
  fetchVpnServers()
})
</script>

<template>
  <div>
    <VCard class="mb-6">
      <VCardTitle class="d-flex align-center justify-space-between pa-4">
        <div class="d-flex align-center gap-2">
          <VIcon icon="ri-ipv6-line" size="24" />
          <span class="text-h5">Data IP Address</span>
        </div>
        <VBtn
          color="primary"
          prepend-icon="ri-add-line"
          @click="openAddServerModal"
        >
          Add Server
        </VBtn>
      </VCardTitle>
      
      <VCardText>
        <VRow>
          <VCol cols="12" md="3">
            <VCard variant="outlined" class="text-center">
              <VCardText>
                <VIcon icon="ri-server-line" size="32" color="primary" class="mb-2" />
                <div class="text-2xl font-weight-bold text-primary">
                  {{ totalServers }}
                </div>
                <div class="text-sm text-medium-emphasis">Total Servers</div>
              </VCardText>
            </VCard>
          </VCol>
          
          <VCol cols="12" md="3">
            <VCard variant="outlined" class="text-center">
              <VCardText>
                <VIcon icon="ri-checkbox-circle-line" size="32" color="success" class="mb-2" />
                <div class="text-2xl font-weight-bold text-success">
                  {{ activeServers }}
                </div>
                <div class="text-sm text-medium-emphasis">Active Servers</div>
              </VCardText>
            </VCard>
          </VCol>
          
          <VCol cols="12" md="3">
            <VCard variant="outlined" class="text-center">
              <VCardText>
                <VIcon icon="ri-close-circle-line" size="32" color="error" class="mb-2" />
                <div class="text-2xl font-weight-bold text-error">
                  {{ inactiveServers }}
                </div>
                <div class="text-sm text-medium-emphasis">Inactive Servers</div>
              </VCardText>
            </VCard>
          </VCol>
          
          <VCol cols="12" md="3">
            <VCard variant="outlined" class="text-center">
              <VCardText>
                <VIcon icon="ri-global-line" size="32" color="info" class="mb-2" />
                <div class="text-2xl font-weight-bold text-info">
                  Jakarta
                </div>
                <div class="text-sm text-medium-emphasis">Location</div>
              </VCardText>
            </VCard>
          </VCol>
        </VRow>
      </VCardText>
    </VCard>

    <VCard>
      <VCardText>
        <!-- Search and Actions -->
        <VRow class="mb-4">
          <VCol cols="12" md="6">
            <VTextField
              v-model="search"
              label="Search IP Address, Server Name, Project, or Location"
              prepend-inner-icon="ri-search-line"
              variant="outlined"
              density="compact"
              hide-details
              clearable
            />
          </VCol>
        </VRow>

        <!-- Data Table -->
        <VDataTable
          :headers="headers"
          :items="filteredVpnServers"
          :loading="loading"
          loading-text="Loading servers..."
          :items-per-page="itemsPerPage"
          :page="currentPage"
          :items-length="totalServers"
          @update:page="currentPage = $event"
          @update:items-per-page="itemsPerPage = $event"
          class="elevation-0"
        >
          <!-- IP Address Column -->
          <template #item.ip_address="{ item }">
            <div class="d-flex align-center gap-3">
              <VAvatar
                icon="ri-server-line"
                variant="tonal"
                color="primary"
                size="32"
              />
              <div>
                <div class="font-weight-medium">{{ item.ip_address }}</div>
              </div>
            </div>
          </template>

          <!-- Nama Sistem Column -->
          <template #item.nama_sistem="{ item }">
            <div class="d-flex align-center gap-3">
              <VAvatar
                icon="ri-computer-line"
                variant="tonal"
                color="info"
                size="32"
              />
              <div>
                <div class="font-weight-medium">{{ item.nama_sistem }}</div>
              </div>
            </div>
          </template>

          <!-- Status Column -->
          <template #item.is_active="{ item }">
            <VChip
              :color="getStatusColor(item.is_active)"
              variant="elevated"
              size="small"
              class="text-capitalize"
            >
              {{ getStatusText(item.is_active) }}
            </VChip>
          </template>

          <!-- Actions Column -->
          <template #item.actions="{ item }">
            <div class="d-flex gap-1">
              <VBtn
                icon="ri-eye-line"
                variant="text"
                size="small"
                color="primary"
                @click="viewServer(item)"
              />
              <VBtn
                icon="ri-edit-line"
                variant="text"
                size="small"
                color="success"
                @click="editServer(item)"
                :disabled="item.id === 0"
                :title="item.id === 0 ? 'Cannot edit static entry' : 'Edit server'"
              />
              <VBtn
                icon="ri-delete-bin-line"
                variant="text"
                size="small"
                color="error"
                @click="deleteServer(item)"
                :disabled="item.id === 0"
                :title="item.id === 0 ? 'Cannot delete static entry' : 'Delete server'"
              />
            </div>
          </template>
        </VDataTable>
      </VCardText>
    </VCard>

    <!-- Add Server Modal -->
    <VDialog
      v-model="showAddServerModal"
      max-width="600px"
      persistent
      class="add-server-modal"
    >
      <VCard class="modal-card">
        <VCardTitle class="d-flex align-center gap-2 pa-4">
          <VIcon icon="ri-server-line" size="24" />
          <span>Add VPN Server</span>
          <VSpacer />
          <VBtn
            icon="ri-close-line"
            variant="text"
            @click="closeAddServerModal"
          />
        </VCardTitle>
        
        <VCardText>
          <VForm @submit.prevent="addServer">
            <VRow>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="newServerForm.nama_sistem"
                  label="Nama Sistem"
                  placeholder="Enter system name"
                  prepend-inner-icon="ri-computer-line"
                  variant="outlined"
                  required
                  :error-messages="newServerForm.nama_sistem.trim() === '' ? 'Nama sistem is required' : []"
                  hide-details="auto"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="newServerForm.ip_address"
                  label="IP Address"
                  placeholder="192.168.1.1"
                  prepend-inner-icon="ri-ipv6-line"
                  variant="outlined"
                  required
                  :error-messages="!isValidIpAddress && newServerForm.ip_address.trim() !== '' && newServerForm.ip_address !== 'IP All Product' ? 'Invalid IP address format' : []"
                  hide-details="auto"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VSelect
                  v-model="newServerForm.server_location"
                  :items="locations"
                  label="Server Location"
                  prepend-inner-icon="ri-map-pin-line"
                  variant="outlined"
                  required
                  :error-messages="newServerForm.server_location.trim() === '' ? 'Server location is required' : []"
                  hide-details="auto"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VSelect
                  v-model="newServerForm.project"
                  :items="projects"
                  label="Project"
                  prepend-inner-icon="ri-folder-line"
                  variant="outlined"
                  clearable
                  hide-details="auto"
                />
              </VCol>
              <VCol cols="12">
                <VSwitch
                  v-model="newServerForm.is_active"
                  label="Active"
                  color="primary"
                  inset
                  hide-details
                />
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
        
        <VCardActions class="pa-4">
          <VSpacer />
          <VBtn
            variant="outlined"
            color="secondary"
            prepend-icon="ri-close-line"
            @click="closeAddServerModal"
            :disabled="formLoading"
          >
            Cancel
          </VBtn>
          <VBtn
            color="primary"
            prepend-icon="ri-save-line"
            :loading="formLoading"
            :disabled="!isFormValid || !isValidIpAddress"
            @click="addServer"
          >
            Save Server
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- View Server Modal -->
    <VDialog
      v-model="showViewModal"
      max-width="500px"
      persistent
      class="view-server-modal"
    >
      <VCard class="modal-card" v-if="selectedServer">
        <VCardTitle class="d-flex align-center gap-2 pa-4">
          <VIcon icon="ri-eye-line" size="24" />
          <span>Server Details</span>
          <VSpacer />
          <VBtn
            icon="ri-close-line"
            variant="text"
            @click="closeViewModal"
          />
        </VCardTitle>
        
        <VCardText>
          <VRow>
            <VCol cols="12">
              <div class="server-detail-item">
                <VIcon icon="ri-ipv6-line" size="20" color="primary" class="mb-2" />
                <div class="detail-label">IP Address</div>
                <div class="detail-value">{{ selectedServer.ip_address }}</div>
              </div>
            </VCol>
            <VCol cols="12">
              <div class="server-detail-item">
                <VIcon icon="ri-computer-line" size="20" color="info" class="mb-2" />
                <div class="detail-label">System Name</div>
                <div class="detail-value">{{ selectedServer.nama_sistem }}</div>
              </div>
            </VCol>
            <VCol cols="12" v-if="selectedServer.project">
              <div class="server-detail-item">
                <VIcon icon="ri-folder-line" size="20" color="warning" class="mb-2" />
                <div class="detail-label">Project</div>
                <div class="detail-value">{{ selectedServer.project }}</div>
              </div>
            </VCol>
            <VCol cols="12">
              <div class="server-detail-item">
                <VIcon icon="ri-map-pin-line" size="20" color="success" class="mb-2" />
                <div class="detail-label">Server Location</div>
                <div class="detail-value">{{ selectedServer.server_location }}</div>
              </div>
            </VCol>
            <VCol cols="12">
              <div class="server-detail-item">
                <VIcon icon="ri-toggle-line" size="20" :color="selectedServer.is_active ? 'success' : 'error'" class="mb-2" />
                <div class="detail-label">Status</div>
                <VChip
                  :color="getStatusColor(selectedServer.is_active)"
                  variant="elevated"
                  size="small"
                  class="text-capitalize"
                >
                  {{ getStatusText(selectedServer.is_active) }}
                </VChip>
              </div>
            </VCol>
            <VCol cols="12">
              <div class="server-detail-item">
                <VIcon icon="ri-time-line" size="20" color="secondary" class="mb-2" />
                <div class="detail-label">Created At</div>
                <div class="detail-value">{{ new Date(selectedServer.created_at).toLocaleString() }}</div>
              </div>
            </VCol>
            <VCol cols="12">
              <div class="server-detail-item">
                <VIcon icon="ri-refresh-line" size="20" color="secondary" class="mb-2" />
                <div class="detail-label">Last Updated</div>
                <div class="detail-value">{{ new Date(selectedServer.updated_at).toLocaleString() }}</div>
              </div>
            </VCol>
          </VRow>
        </VCardText>
        
        <VCardActions class="pa-4">
          <VSpacer />
          <VBtn
            variant="elevated"
            color="primary"
            prepend-icon="ri-close-line"
            @click="closeViewModal"
          >
            Close
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Edit Server Modal -->
    <VDialog
      v-model="showEditModal"
      max-width="600px"
      persistent
      class="edit-server-modal"
    >
      <VCard class="modal-card">
        <VCardTitle class="d-flex align-center gap-2 pa-4">
          <VIcon icon="ri-edit-line" size="24" />
          <span>Edit VPN Server</span>
          <VSpacer />
          <VBtn
            icon="ri-close-line"
            variant="text"
            @click="closeEditModal"
          />
        </VCardTitle>
        
        <VCardText>
          <VForm @submit.prevent="updateServer">
            <VRow>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="editServerForm.nama_sistem"
                  label="Nama Sistem"
                  placeholder="Enter system name"
                  prepend-inner-icon="ri-computer-line"
                  variant="outlined"
                  required
                  :error-messages="editServerForm.nama_sistem.trim() === '' ? 'Nama sistem is required' : []"
                  hide-details="auto"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="editServerForm.ip_address"
                  label="IP Address"
                  placeholder="192.168.1.1"
                  prepend-inner-icon="ri-ipv6-line"
                  variant="outlined"
                  required
                  :error-messages="!isValidEditIpAddress && editServerForm.ip_address.trim() !== '' && editServerForm.ip_address !== 'IP All Product' ? 'Invalid IP address format' : []"
                  hide-details="auto"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VSelect
                  v-model="editServerForm.server_location"
                  :items="locations"
                  label="Server Location"
                  prepend-inner-icon="ri-map-pin-line"
                  variant="outlined"
                  required
                  :error-messages="editServerForm.server_location.trim() === '' ? 'Server location is required' : []"
                  hide-details="auto"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VSelect
                  v-model="editServerForm.project"
                  :items="projects"
                  label="Project"
                  prepend-inner-icon="ri-folder-line"
                  variant="outlined"
                  clearable
                  hide-details="auto"
                />
              </VCol>
              <VCol cols="12">
                <VSwitch
                  v-model="editServerForm.is_active"
                  label="Active"
                  color="primary"
                  inset
                  hide-details
                />
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
        
        <VCardActions class="pa-4">
          <VSpacer />
          <VBtn
            variant="outlined"
            color="secondary"
            prepend-icon="ri-close-line"
            @click="closeEditModal"
            :disabled="formLoading"
          >
            Cancel
          </VBtn>
          <VBtn
            color="primary"
            prepend-icon="ri-save-line"
            :loading="formLoading"
            :disabled="!isEditFormValid || !isValidEditIpAddress"
            @click="updateServer"
          >
            Update Server
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Delete Confirmation Modal -->
    <VDialog
      v-model="showDeleteModal"
      max-width="400px"
      persistent
      class="delete-modal"
    >
      <VCard class="modal-card">
        <VCardTitle class="d-flex align-center gap-2 pa-4">
          <VIcon icon="ri-delete-bin-line" size="24" color="error" />
          <span>Confirm Delete</span>
          <VSpacer />
          <VBtn
            icon="ri-close-line"
            variant="text"
            @click="closeDeleteModal"
          />
        </VCardTitle>
        
        <VCardText class="text-center pa-6">
          <VIcon icon="ri-error-warning-line" size="64" color="warning" class="mb-4" />
          <div class="text-h6 mb-2">Are you sure?</div>
          <div class="text-body-2 text-medium-emphasis mb-4">
            This action cannot be undone. This will permanently delete the server:
          </div>
          <VCard variant="outlined" class="mb-4">
            <VCardText class="pa-3">
              <div class="d-flex align-center gap-2">
                <VIcon icon="ri-server-line" color="primary" />
                <div>
                  <div class="font-weight-medium">{{ selectedServer?.nama_sistem }}</div>
                  <div class="text-sm text-medium-emphasis">{{ selectedServer?.ip_address }}</div>
                </div>
              </div>
            </VCardText>
          </VCard>
        </VCardText>
        
        <VCardActions class="pa-4">
          <VSpacer />
          <VBtn
            variant="outlined"
            color="secondary"
            prepend-icon="ri-close-line"
            @click="closeDeleteModal"
            :disabled="formLoading"
          >
            Cancel
          </VBtn>
          <VBtn
            color="error"
            prepend-icon="ri-delete-bin-line"
            :loading="formLoading"
            @click="confirmDelete"
          >
            Delete
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>

<style scoped>
.v-data-table {
  border-radius: 8px;
}

/* Transparent Modal Styles */
.add-server-modal :deep(.v-overlay__scrim) {
  background: rgba(0, 0, 0, 0.3) !important;
  backdrop-filter: blur(2px);
}

.add-server-modal :deep(.v-dialog) {
  background: transparent !important;
  box-shadow: none !important;
}

.modal-card {
  background: rgba(255, 255, 255, 0.95) !important;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1) !important;
}

.modal-card :deep(.v-card-title) {
  background: rgba(255, 255, 255, 0.8) !important;
  backdrop-filter: blur(5px);
  border-bottom: 1px solid rgba(255, 255, 255, 0.3);
}

.modal-card :deep(.v-card-text) {
  background: rgba(255, 255, 255, 0.7) !important;
}

.modal-card :deep(.v-card-actions) {
  background: rgba(255, 255, 255, 0.8) !important;
  backdrop-filter: blur(5px);
  border-top: 1px solid rgba(255, 255, 255, 0.3);
}

/* Glassmorphism effect for form fields */
.modal-card :deep(.v-field) {
  background: rgba(255, 255, 255, 0.8) !important;
  backdrop-filter: blur(5px);
  border: 1px solid rgba(255, 255, 255, 0.3) !important;
}

.modal-card :deep(.v-field:hover) {
  background: rgba(255, 255, 255, 0.9) !important;
}

.modal-card :deep(.v-select) .v-field {
  background: rgba(255, 255, 255, 0.8) !important;
  backdrop-filter: blur(5px);
  border: 1px solid rgba(255, 255, 255, 0.3) !important;
}

.modal-card :deep(.v-switch) .v-switch__track {
  background: rgba(33, 150, 243, 0.8) !important;
  border: 2px solid rgba(33, 150, 243, 0.9) !important;
  opacity: 1 !important;
}

.modal-card :deep(.v-switch--inset) .v-switch__track {
  background: rgba(33, 150, 243, 0.9) !important;
  border: 2px solid rgba(33, 150, 243, 1) !important;
}

.modal-card :deep(.v-switch) .v-switch__thumb {
  background: white !important;
  border: 2px solid rgba(33, 150, 243, 0.8) !important;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2) !important;
}

.modal-card :deep(.v-switch--inset) .v-switch__thumb {
  background: white !important;
  border: 2px solid rgba(33, 150, 243, 1) !important;
}

.modal-card :deep(.v-selection-control) {
  background: rgba(255, 255, 255, 0.9) !important;
  border: 1px solid rgba(33, 150, 243, 0.5) !important;
  border-radius: 8px !important;
  padding: 8px 12px !important;
}

.modal-card :deep(.v-selection-control__wrapper) {
  background: rgba(255, 255, 255, 0.95) !important;
  border-radius: 6px !important;
}

.modal-card :deep(.v-label) {
  color: #333 !important;
  font-weight: 600 !important;
  font-size: 14px !important;
  text-shadow: 0 1px 2px rgba(255, 255, 255, 0.8) !important;
}

.modal-card :deep(.v-switch) {
  margin: 8px 0 !important;
}

/* View Modal Styles */
.view-server-modal :deep(.v-overlay__scrim) {
  background: rgba(0, 0, 0, 0.4) !important;
  backdrop-filter: blur(3px);
}

.view-server-modal :deep(.v-dialog) {
  background: transparent !important;
  box-shadow: none !important;
}

.server-detail-item {
  padding: 16px;
  margin-bottom: 12px;
  background: rgba(255, 255, 255, 0.6);
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.3);
  backdrop-filter: blur(5px);
  transition: all 0.3s ease;
}

.server-detail-item:hover {
  background: rgba(255, 255, 255, 0.8);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.detail-label {
  font-size: 12px;
  font-weight: 600;
  color: #666;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 4px;
}

.detail-value {
  font-size: 16px;
  font-weight: 500;
  color: #333;
  line-height: 1.4;
}

/* Edit Modal Styles */
.edit-server-modal :deep(.v-overlay__scrim) {
  background: rgba(0, 0, 0, 0.3) !important;
  backdrop-filter: blur(2px);
}

.edit-server-modal :deep(.v-dialog) {
  background: transparent !important;
  box-shadow: none !important;
}

/* Delete Modal Styles */
.delete-modal :deep(.v-overlay__scrim) {
  background: rgba(0, 0, 0, 0.5) !important;
  backdrop-filter: blur(4px);
}

.delete-modal :deep(.v-dialog) {
  background: transparent !important;
  box-shadow: none !important;
}
</style>
