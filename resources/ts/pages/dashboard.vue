<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const user = ref(JSON.parse(localStorage.getItem('user') || '{}'))
const loading = ref(false)

// Statistics data
const stats = ref({
  teleworking: {
    total: 0,
    pending: 0,
    approved: 0,
    rejected: 0,
    today: 0
  },
  aksesLogic: {
    total: 0,
    pending: 0,
    approved: 0,
    rejected: 0,
    today: 0
  },
  employees: {
    total: 0,
    active: 0
  }
})

// Define interface for recent requests
interface RecentRequest {
  id: number
  request_type: 'teleworking' | 'akses_logic'
  status: 'pending' | 'approved' | 'rejected'
  employee?: {
    nama_lengkap: string
  }
  created_at: string
}

const recentRequests = ref<RecentRequest[]>([])

// Fetch dashboard statistics
const fetchStats = async () => {
  loading.value = true
  try {
    // Fetch all data in parallel for faster loading
    const [teleworkingResponse, aksesResponse, employeeResponse, recentResponse] = await Promise.all([
      fetch('/api/teleworking-requests/stats'),
      fetch('/api/akses-logic-requests/stats'),
      fetch('/api/employees/stats'),
      fetch('/api/recent-requests?limit=5')
    ])

    const [teleworkingData, aksesData, employeeData, recentData] = await Promise.all([
      teleworkingResponse.json(),
      aksesResponse.json(),
      employeeResponse.json(),
      recentResponse.json()
    ])

    console.log('Teleworking stats:', teleworkingData)
    if (teleworkingData.success) {
      stats.value.teleworking = {
        total: teleworkingData.data.total || 0,
        pending: teleworkingData.data.pending || 0,
        approved: teleworkingData.data.approved || 0,
        rejected: teleworkingData.data.rejected || 0,
        today: teleworkingData.data.today || 0
      }
    }

    console.log('Akses Logic stats:', aksesData)
    if (aksesData.success) {
      stats.value.aksesLogic = {
        total: aksesData.data.total || 0,
        pending: aksesData.data.pending || 0,
        approved: aksesData.data.approved || 0,
        rejected: aksesData.data.rejected || 0,
        today: aksesData.data.today || 0
      }
    }

    console.log('Employee stats:', employeeData)
    if (employeeData.success) {
      stats.value.employees = employeeData.data
    }

    console.log('Recent requests:', recentData)
    if (recentData.success) {
      recentRequests.value = recentData.data
    }
  } catch (error) {
    console.error('Error fetching dashboard stats:', error)
  } finally {
    loading.value = false
  }
}

// Calculate total validation forms
const totalValidationForms = computed(() => {
  return (stats.value.teleworking?.total || 0) + (stats.value.aksesLogic?.total || 0)
})

const totalPendingForms = computed(() => {
  return (stats.value.teleworking?.pending || 0) + (stats.value.aksesLogic?.pending || 0)
})

const totalApprovedForms = computed(() => {
  return (stats.value.teleworking?.approved || 0) + (stats.value.aksesLogic?.approved || 0)
})

const todayForms = computed(() => {
  return (stats.value.teleworking?.today || 0) + (stats.value.aksesLogic?.today || 0)
})

// Navigation functions
const navigateTo = (path: string) => {
  router.push(path)
}

const logout = () => {
  localStorage.removeItem('isAuthenticated')
  localStorage.removeItem('user')
  window.location.href = '/login'
}

// Load data on mount
onMounted(() => {
  fetchStats()
})
</script>

<template>
  <div class="pa-6">
    <!-- Header Section -->
    <VCard class="mb-6" elevation="2">
      <VCardTitle class="d-flex align-center justify-space-between pa-6">
        <div class="d-flex align-center">
          <VIcon icon="ri-dashboard-3-line" size="32" class="me-3 text-primary" />
          <div>
            <h1 class="text-h4 font-weight-bold mb-1">Dashboard</h1>
            <p class="text-medium-emphasis">Welcome back, {{ user.name || 'User' }}!</p>
          </div>
        </div>
        <VBtn @click="logout" color="error" variant="outlined" prepend-icon="ri-logout-box-line">
          Logout
        </VBtn>
      </VCardTitle>
    </VCard>

    <!-- Loading State -->
    <VRow v-if="loading" class="mb-6">
      <VCol cols="12">
        <VCard>
          <VCardText class="text-center py-8">
            <VProgressCircular indeterminate color="primary" size="48" class="mb-4" />
            <p>Loading dashboard data...</p>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- Statistics Cards -->
    <VRow v-else class="mb-6">
      <!-- Total Validation Forms -->
      <VCol cols="12" sm="6" lg="3">
        <VCard class="stat-card h-100" elevation="3">
          <VCardText class="pa-6">
            <div class="d-flex align-center justify-space-between mb-4">
              <VAvatar color="primary" variant="tonal" size="48">
                <VIcon icon="ri-file-list-3-line" size="24" />
              </VAvatar>
              <span class="text-caption text-medium-emphasis">Total Forms</span>
            </div>
            <h3 class="text-h3 font-weight-bold text-primary mb-2">{{ totalValidationForms }}</h3>
            <p class="text-caption text-medium-emphasis mb-0">All validation forms</p>
          </VCardText>
        </VCard>
      </VCol>

      <!-- Pending Forms -->
      <VCol cols="12" sm="6" lg="3">
        <VCard class="stat-card h-100" elevation="3">
          <VCardText class="pa-6">
            <div class="d-flex align-center justify-space-between mb-4">
              <VAvatar color="warning" variant="tonal" size="48">
                <VIcon icon="ri-time-line" size="24" />
              </VAvatar>
              <span class="text-caption text-medium-emphasis">Pending</span>
            </div>
            <h3 class="text-h3 font-weight-bold text-warning mb-2">{{ totalPendingForms }}</h3>
            <p class="text-caption text-medium-emphasis mb-0">Awaiting validation</p>
          </VCardText>
        </VCard>
      </VCol>

      <!-- Approved Forms -->
      <VCol cols="12" sm="6" lg="3">
        <VCard class="stat-card h-100" elevation="3">
          <VCardText class="pa-6">
            <div class="d-flex align-center justify-space-between mb-4">
              <VAvatar color="success" variant="tonal" size="48">
                <VIcon icon="ri-checkbox-circle-line" size="24" />
              </VAvatar>
              <span class="text-caption text-medium-emphasis">Approved</span>
            </div>
            <h3 class="text-h3 font-weight-bold text-success mb-2">{{ totalApprovedForms }}</h3>
            <p class="text-caption text-medium-emphasis mb-0">Successfully validated</p>
          </VCardText>
        </VCard>
      </VCol>

      <!-- Today's Forms -->
      <VCol cols="12" sm="6" lg="3">
        <VCard class="stat-card h-100" elevation="3">
          <VCardText class="pa-6">
            <div class="d-flex align-center justify-space-between mb-4">
              <VAvatar color="info" variant="tonal" size="48">
                <VIcon icon="ri-calendar-today-line" size="24" />
              </VAvatar>
              <span class="text-caption text-medium-emphasis">Today</span>
            </div>
            <h3 class="text-h3 font-weight-bold text-info mb-2">{{ todayForms }}</h3>
            <p class="text-caption text-medium-emphasis mb-0">Forms submitted today</p>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- Form Type Breakdown -->
    <VRow class="mb-6">
      <!-- Teleworking Requests -->
      <VCol cols="12" md="6">
        <VCard elevation="2">
          <VCardTitle class="pa-6 pb-4">
            <div class="d-flex align-center">
              <VIcon icon="ri-computer-line" class="me-2 text-primary" />
              <span>Teleworking Requests</span>
            </div>
          </VCardTitle>
          <VDivider />
          <VCardText class="pa-6">
            <VRow>
              <VCol cols="6">
                <div class="text-center">
                  <h4 class="text-h4 font-weight-bold text-primary mb-2">{{ stats.teleworking?.total || 0 }}</h4>
                  <p class="text-caption text-medium-emphasis mb-0">Total Requests</p>
                </div>
              </VCol>
              <VCol cols="6">
                <div class="d-flex flex-column gap-2">
                  <div class="d-flex align-center justify-space-between">
                    <span class="text-caption">Pending</span>
                    <VChip color="warning" size="small">{{ stats.teleworking?.pending || 0 }}</VChip>
                  </div>
                  <div class="d-flex align-center justify-space-between">
                    <span class="text-caption">Approved</span>
                    <VChip color="success" size="small">{{ stats.teleworking?.approved || 0 }}</VChip>
                  </div>
                  <div class="d-flex align-center justify-space-between">
                    <span class="text-caption">Rejected</span>
                    <VChip color="error" size="small">{{ stats.teleworking?.rejected || 0 }}</VChip>
                  </div>
                </div>
              </VCol>
            </VRow>
            <VBtn 
              block 
              color="primary" 
              variant="outlined" 
              class="mt-4"
              @click="navigateTo('/form-teleworking')"
            >
              <VIcon icon="ri-add-line" class="me-2" />
              New Teleworking Request
            </VBtn>
          </VCardText>
        </VCard>
      </VCol>

      <!-- Akses Logic Requests -->
      <VCol cols="12" md="6">
        <VCard elevation="2">
          <VCardTitle class="pa-6 pb-4">
            <div class="d-flex align-center">
              <VIcon icon="ri-shield-keyhole-line" class="me-2 text-secondary" />
              <span>Access Logic Requests</span>
            </div>
          </VCardTitle>
          <VDivider />
          <VCardText class="pa-6">
            <VRow>
              <VCol cols="6">
                <div class="text-center">
                  <h4 class="text-h4 font-weight-bold text-secondary mb-2">{{ stats.aksesLogic?.total || 0 }}</h4>
                  <p class="text-caption text-medium-emphasis mb-0">Total Requests</p>
                </div>
              </VCol>
              <VCol cols="6">
                <div class="d-flex flex-column gap-2">
                  <div class="d-flex align-center justify-space-between">
                    <span class="text-caption">Pending</span>
                    <VChip color="warning" size="small">{{ stats.aksesLogic?.pending || 0 }}</VChip>
                  </div>
                  <div class="d-flex align-center justify-space-between">
                    <span class="text-caption">Approved</span>
                    <VChip color="success" size="small">{{ stats.aksesLogic?.approved || 0 }}</VChip>
                  </div>
                  <div class="d-flex align-center justify-space-between">
                    <span class="text-caption">Rejected</span>
                    <VChip color="error" size="small">{{ stats.aksesLogic?.rejected || 0 }}</VChip>
                  </div>
                </div>
              </VCol>
            </VRow>
            <VBtn 
              block 
              color="secondary" 
              variant="outlined" 
              class="mt-4"
              @click="navigateTo('/form-akses-logic')"
            >
              <VIcon icon="ri-add-line" class="me-2" />
              New Access Logic Request
            </VBtn>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- Quick Actions & Recent Activity -->
    <VRow>
      <!-- Quick Actions -->
      <VCol cols="12" md="4">
        <VCard elevation="2">
          <VCardTitle class="pa-6 pb-4">
            <div class="d-flex align-center">
              <VIcon icon="ri-flashlight-line" class="me-2 text-warning" />
              <span>Quick Actions</span>
            </div>
          </VCardTitle>
          <VDivider />
          <VCardText class="pa-6">
            <div class="d-flex flex-column gap-3">
              <VBtn 
                color="primary" 
                variant="outlined" 
                block 
                prepend-icon="ri-vpn-line"
                @click="navigateTo('/form-google')"
              >
                VPN Request
              </VBtn>
              <VBtn 
                color="info" 
                variant="outlined" 
                block 
                prepend-icon="ri-bar-chart-line"
                @click="navigateTo('/data-pegawai')"
              >
                View Employees
              </VBtn>
              <VBtn 
                color="success" 
                variant="outlined" 
                block 
                prepend-icon="ri-settings-3-line"
                @click="navigateTo('/account-settings')"
              >
                Settings
              </VBtn>
            </div>
          </VCardText>
        </VCard>
      </VCol>

      <!-- User Information -->
      <VCol cols="12" md="4">
        <VCard elevation="2">
          <VCardTitle class="pa-6 pb-4">
            <div class="d-flex align-center">
              <VIcon icon="ri-user-3-line" class="me-2 text-info" />
              <span>User Information</span>
            </div>
          </VCardTitle>
          <VDivider />
          <VCardText class="pa-6">
            <div class="d-flex flex-column gap-3">
              <div class="d-flex align-center">
                <VIcon icon="ri-user-line" size="20" class="me-3 text-medium-emphasis" />
                <div>
                  <p class="text-caption text-medium-emphasis mb-0">Name</p>
                  <p class="text-body-2 mb-0">{{ user.name || 'Unknown' }}</p>
                </div>
              </div>
              <div class="d-flex align-center">
                <VIcon icon="ri-mail-line" size="20" class="me-3 text-medium-emphasis" />
                <div>
                  <p class="text-caption text-medium-emphasis mb-0">Email</p>
                  <p class="text-body-2 mb-0">{{ user.email || 'Unknown' }}</p>
                </div>
              </div>
              <div class="d-flex align-center">
                <VIcon icon="ri-building-line" size="20" class="me-3 text-medium-emphasis" />
                <div>
                  <p class="text-caption text-medium-emphasis mb-0">Total Employees</p>
                  <p class="text-body-2 mb-0">{{ stats.employees.total }}</p>
                </div>
              </div>
            </div>
          </VCardText>
        </VCard>
      </VCol>

      <!-- Recent Requests -->
      <VCol cols="12" md="4">
        <VCard elevation="2">
          <VCardTitle class="pa-6 pb-4">
            <div class="d-flex align-center">
              <VIcon icon="ri-history-line" class="me-2 text-success" />
              <span>Recent Activity</span>
            </div>
          </VCardTitle>
          <VDivider />
          <VCardText class="pa-6">
            <div v-if="recentRequests.length > 0" class="d-flex flex-column gap-2">
              <div 
                v-for="request in recentRequests.slice(0, 3)" 
                :key="request.id"
                class="d-flex align-center justify-space-between pa-2 rounded"
                style="background-color: #dbeafe"
              >
                <div class="d-flex align-center">
                  <VIcon 
                    :icon="request.request_type === 'teleworking' ? 'ri-computer-line' : 'ri-shield-keyhole-line'" 
                    size="16" 
                    class="me-2"
                  />
                  <div>
                    <p class="text-caption mb-0">{{ request.employee?.nama_lengkap || 'Unknown' }}</p>
                    <p class="text-caption text-medium-emphasis mb-0">{{ request.request_type }}</p>
                  </div>
                </div>
                <VChip 
                  :color="request.status === 'approved' ? 'success' : request.status === 'rejected' ? 'error' : 'warning'" 
                  size="x-small"
                >
                  {{ request.status }}
                </VChip>
              </div>
            </div>
            <div v-else class="text-center py-4">
              <VIcon icon="ri-inbox-line" size="32" class="text-medium-emphasis mb-2" />
              <p class="text-caption text-medium-emphasis mb-0">No recent activity</p>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>

<style scoped>
.stat-card {
  transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
}
</style>
