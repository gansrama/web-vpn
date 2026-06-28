<script lang="ts" setup>
import { ref, computed } from 'vue'
import { useTheme } from 'vuetify'

const theme = useTheme()
const search = ref('')

// Sample data for VPN
const vpnData = ref([
  {
    id: 1,
    name: 'VPN Premium US',
    server: 'US-West-01',
    user: 'John Doe',
    duration: '30 Days',
    progress: 75,
    status: 'active',
    icon: 'ri-server-line',
    activeUsers: 45,
    totalUsers: 60
  },
  {
    id: 2,
    name: 'VPN Premium EU',
    server: 'EU-Central-02',
    user: 'Jane Smith',
    duration: '30 Days',
    progress: 100,
    status: 'completed',
    icon: 'ri-global-line',
    activeUsers: 32,
    totalUsers: 32
  },
  {
    id: 3,
    name: 'VPN Premium Asia',
    server: 'Asia-Singapore-01',
    user: 'Mike Johnson',
    duration: '30 Days',
    progress: 50,
    status: 'active',
    icon: 'ri-earth-line',
    activeUsers: 28,
    totalUsers: 50
  },
  {
    id: 4,
    name: 'VPN Basic US',
    server: 'US-East-01',
    user: 'Sarah Wilson',
    duration: '7 Days',
    progress: 25,
    status: 'pending',
    icon: 'ri-wifi-line',
    activeUsers: 15,
    totalUsers: 20
  },
  {
    id: 5,
    name: 'VPN Premium Canada',
    server: 'Canada-Toronto-01',
    user: 'Tom Brown',
    duration: '30 Days',
    progress: 90,
    status: 'active',
    icon: 'ri-map-pin-line',
    activeUsers: 38,
    totalUsers: 40
  }
])

// Table headers
const headers = [
  { title: 'VPN NAME', key: 'name', sortable: true },
  { title: 'SERVER', key: 'server', sortable: true },
  { title: 'USER', key: 'user', sortable: true },
  { title: 'DURATION', key: 'duration', sortable: true },
  { title: 'PROGRESS', key: 'progress', sortable: false },
  { title: 'STATUS', key: 'status', sortable: true },
  { title: 'ACTIONS', key: 'actions', sortable: false }
]

// Computed property for filtered data
const filteredVpnData = computed(() => {
  if (!search.value) return vpnData.value
  
  return vpnData.value.filter(item =>
    item.name.toLowerCase().includes(search.value.toLowerCase()) ||
    item.server.toLowerCase().includes(search.value.toLowerCase()) ||
    item.user.toLowerCase().includes(search.value.toLowerCase())
  )
})

// Status color helper
const getStatusColor = (status: string) => {
  switch (status) {
    case 'active': return 'success'
    case 'completed': return 'primary'
    case 'pending': return 'warning'
    default: return 'grey'
  }
}

// Progress color helper
const getProgressColor = (progress: number) => {
  if (progress >= 75) return 'success'
  if (progress >= 50) return 'primary'
  if (progress >= 25) return 'warning'
  return 'error'
}
</script>

<template>
  <div>
    <VCard class="mb-6">
      <VCardTitle class="d-flex align-center justify-space-between pa-4">
        <div class="d-flex align-center gap-2">
          <VIcon icon="ri-vpn-line" size="24" />
          <span class="text-h5">Data Akses Logic</span>
        </div>
        <VBtn
          color="primary"
          prepend-icon="ri-add-line"
          @click="$router.push('/data-vpn/add')"
        >
          Add VPN
        </VBtn>
      </VCardTitle>
      
      <VCardText>
        <VRow>
          <VCol cols="12" md="3">
            <VCard variant="outlined" class="text-center">
              <VCardText>
                <VIcon icon="ri-database-2-line" size="32" color="primary" class="mb-2" />
                <div class="text-2xl font-weight-bold text-primary">
                  {{ vpnData.length }}
                </div>
                <div class="text-sm text-medium-emphasis">Total VPN</div>
              </VCardText>
            </VCard>
          </VCol>
          
          <VCol cols="12" md="3">
            <VCard variant="outlined" class="text-center">
              <VCardText>
                <VIcon icon="ri-user-line" size="32" color="success" class="mb-2" />
                <div class="text-2xl font-weight-bold text-success">
                  {{ vpnData.reduce((sum, item) => sum + item.activeUsers, 0) }}
                </div>
                <div class="text-sm text-medium-emphasis">Active Users</div>
              </VCardText>
            </VCard>
          </VCol>
          
          <VCol cols="12" md="3">
            <VCard variant="outlined" class="text-center">
              <VCardText>
                <VIcon icon="ri-check-line" size="32" color="primary" class="mb-2" />
                <div class="text-2xl font-weight-bold text-primary">
                  {{ vpnData.filter(item => item.status === 'completed').length }}
                </div>
                <div class="text-sm text-medium-emphasis">Completed</div>
              </VCardText>
            </VCard>
          </VCol>
          
          <VCol cols="12" md="3">
            <VCard variant="outlined" class="text-center">
              <VCardText>
                <VIcon icon="ri-time-line" size="32" color="warning" class="mb-2" />
                <div class="text-2xl font-weight-bold text-warning">
                  {{ vpnData.filter(item => item.status === 'pending').length }}
                </div>
                <div class="text-sm text-medium-emphasis">Pending</div>
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
              label="Search VPN"
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
          :items="filteredVpnData"
          :search="search"
          items-per-page="5"
          class="elevation-0"
        >
          <!-- VPN Name Column -->
          <template #item.name="{ item }">
            <div class="d-flex align-center gap-3">
              <VAvatar
                :icon="item.icon"
                variant="tonal"
                color="primary"
                size="32"
              />
              <div>
                <div class="font-weight-medium">{{ item.name }}</div>
                <div class="text-sm text-medium-emphasis">{{ item.server }}</div>
              </div>
            </div>
          </template>

          <!-- Progress Column -->
          <template #item.progress="{ item }">
            <div class="d-flex align-center gap-2">
              <VProgressLinear
                :model-value="item.progress"
                :color="getProgressColor(item.progress)"
                height="6"
                rounded
                style="width: 80px;"
              />
              <span class="text-sm">{{ item.progress }}%</span>
            </div>
          </template>

          <!-- Status Column -->
          <template #item.status="{ item }">
            <VChip
              :color="getStatusColor(item.status)"
              variant="elevated"
              size="small"
              class="text-capitalize"
            >
              {{ item.status }}
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
                @click="$router.push(`/data-vpn/${item.id}`)"
              />
              <VBtn
                icon="ri-edit-line"
                variant="text"
                size="small"
                color="success"
                @click="$router.push(`/data-vpn/${item.id}/edit`)"
              />
              <VBtn
                icon="ri-delete-bin-line"
                variant="text"
                size="small"
                color="error"
                @click="console.log('Delete', item.id)"
              />
            </div>
          </template>
        </VDataTable>
      </VCardText>
    </VCard>
  </div>
</template>

<style scoped>
.v-data-table {
  border-radius: 8px;
}
</style>
