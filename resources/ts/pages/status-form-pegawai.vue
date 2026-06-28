<script lang="ts" setup>
import { ref, onMounted, computed } from 'vue'
import { useTheme } from 'vuetify'

// Theme
const theme = useTheme()

// Data state
const employees = ref<any[]>([])
const loading = ref(true)
const search = ref('')

// Periods
const periods = [
  { key: 'q1', label: 'Januari s.d Maret' },
  { key: 'q2', label: 'April s.d Juni' },
  { key: 'q3', label: 'Juli s.d September' },
  { key: 'q4', label: 'Oktober s.d Desember' },
]

// Form types
const formTypes = [
  { key: 'akses_logic', label: 'FORM LOGIC' },
  { key: 'teleworking', label: 'FORM TELEWORKING' },
]

// Headers for data table
const headers = computed(() => {
  const baseHeaders: any[] = [
    { title: 'Nama Lengkap', key: 'nama_lengkap', sortable: true, fixed: true },
    { title: 'Username VPN', key: 'username_vpn', sortable: true, fixed: true },
  ]
  
  // Add period columns for each form type
  periods.forEach(period => {
    formTypes.forEach(formType => {
      baseHeaders.push({
        title: `${formType.label}\n${period.label}`,
        key: `${formType.key}_${period.key}`,
        sortable: false
      })
    })
  })
  
  return baseHeaders
})

// Computed properties
const filteredEmployees = computed(() => {
  if (!search.value) return employees.value
  
  const searchTerm = search.value.toLowerCase()
  return employees.value.filter(employee => 
    employee.nama_lengkap.toLowerCase().includes(searchTerm) ||
    employee.username_vpn.toLowerCase().includes(searchTerm)
  )
})

// Methods
const fetchEmployeeFormStatus = async () => {
  try {
    loading.value = true
    const response = await fetch('/api/employee-form-status')
    const data = await response.json()
    
    if (data.success) {
      employees.value = data.data
      console.log(`Loaded ${data.data.length} employees with form status`)
      console.log('Sample employee data:', data.data[0])
    } else {
      console.error('Failed to fetch employee form status:', data.message)
    }
  } catch (error) {
    console.error('Error fetching employee form status:', error)
  } finally {
    loading.value = false
  }
}

const hasFormSubmission = (employee: any, formType: string, period: string) => {
  if (!employee.form_submissions) return false
  
  const submission = employee.form_submissions.find(
    (sub: any) => sub.form_type === formType && sub.period === period
  )
  
  return submission && (submission.status === 'approved' || submission.status === 'submitted')
}

const getFormStatusColor = (employee: any, formType: string, period: string) => {
  if (hasFormSubmission(employee, formType, period)) {
    return 'success'
  }
  return 'default'
}

// Lifecycle
onMounted(() => {
  fetchEmployeeFormStatus()
})
</script>

<template>
  <VCard class="pa-6">
    <VCardTitle class="text-h5 mb-4 d-flex align-center justify-space-between">
      <div class="d-flex align-center">
        <VIcon icon="ri-checkbox-multiple-line" class="mr-2" />
        Status Form Pegawai
      </div>
      <VChip color="primary" variant="tonal">
        Total: {{ employees.length }} pegawai
      </VChip>
    </VCardTitle>
    
    <!-- Search and Actions -->
    <VCardText class="d-flex align-center justify-space-between mb-4">
      <VTextField
        v-model="search"
        label="Cari Pegawai..."
        prepend-inner-icon="ri-search-line"
        variant="outlined"
        hide-details
        clearable
        style="max-width: 300px"
      />
      
      <VBtn
        color="secondary"
        prepend-icon="ri-refresh-line"
        @click="fetchEmployeeFormStatus"
        :loading="loading"
      >
        Refresh
      </VBtn>
    </VCardText>
    
    <!-- Data Table -->
    <VCardText>
      <div class="text-body-2 mb-4">
        Menampilkan {{ filteredEmployees.length }} dari {{ employees.length }} data
      </div>
      
      <div class="table-container" style="overflow-x: auto;">
        <VDataTable
          :headers="headers"
          :items="filteredEmployees"
          :loading="loading"
          loading-text="Memuat data..."
          no-data-text="Tidak ada data pegawai"
          class="elevation-1 status-form-table"
          :items-per-page="-1"
          hide-default-footer
        >
          <!-- Template for Akses Logic columns -->
          <template #item.akses_logic_q1="{ item }">
            <VCheckbox
              :model-value="hasFormSubmission(item, 'akses_logic', 'q1')"
              :color="getFormStatusColor(item, 'akses_logic', 'q1')"
              hide-details
              density="compact"
              readonly
            />
          </template>
          <template #item.akses_logic_q2="{ item }">
            <VCheckbox
              :model-value="hasFormSubmission(item, 'akses_logic', 'q2')"
              :color="getFormStatusColor(item, 'akses_logic', 'q2')"
              hide-details
              density="compact"
              readonly
            />
          </template>
          <template #item.akses_logic_q3="{ item }">
            <VCheckbox
              :model-value="hasFormSubmission(item, 'akses_logic', 'q3')"
              :color="getFormStatusColor(item, 'akses_logic', 'q3')"
              hide-details
              density="compact"
              readonly
            />
          </template>
          <template #item.akses_logic_q4="{ item }">
            <VCheckbox
              :model-value="hasFormSubmission(item, 'akses_logic', 'q4')"
              :color="getFormStatusColor(item, 'akses_logic', 'q4')"
              hide-details
              density="compact"
              readonly
            />
          </template>
          
          <!-- Template for Teleworking columns -->
          <template #item.teleworking_q1="{ item }">
            <VCheckbox
              :model-value="hasFormSubmission(item, 'teleworking', 'q1')"
              :color="getFormStatusColor(item, 'teleworking', 'q1')"
              hide-details
              density="compact"
              readonly
            />
          </template>
          <template #item.teleworking_q2="{ item }">
            <VCheckbox
              :model-value="hasFormSubmission(item, 'teleworking', 'q2')"
              :color="getFormStatusColor(item, 'teleworking', 'q2')"
              hide-details
              density="compact"
              readonly
            />
          </template>
          <template #item.teleworking_q3="{ item }">
            <VCheckbox
              :model-value="hasFormSubmission(item, 'teleworking', 'q3')"
              :color="getFormStatusColor(item, 'teleworking', 'q3')"
              hide-details
              density="compact"
              readonly
            />
          </template>
          <template #item.teleworking_q4="{ item }">
            <VCheckbox
              :model-value="hasFormSubmission(item, 'teleworking', 'q4')"
              :color="getFormStatusColor(item, 'teleworking', 'q4')"
              hide-details
              density="compact"
              readonly
            />
          </template>
        </VDataTable>
      </div>
    </VCardText>
    
    <!-- Legend -->
    <VCardText class="mt-4">
      <VCard variant="outlined" class="pa-4">
        <VCardTitle class="text-subtitle-1 mb-2">Keterangan</VCardTitle>
        <div class="d-flex align-center gap-4 flex-wrap">
          <div class="d-flex align-center">
            <VCheckbox
              :model-value="true"
              color="success"
              hide-details
              density="compact"
              readonly
              class="mr-2"
            />
            <span class="text-body-2">Sudah mengisi form (Approved)</span>
          </div>
          <div class="d-flex align-center">
            <VCheckbox
              :model-value="false"
              hide-details
              density="compact"
              readonly
              class="mr-2"
            />
            <span class="text-body-2">Belum mengisi form</span>
          </div>
        </div>
      </VCard>
    </VCardText>
  </VCard>
</template>

<style scoped>
.table-container {
  min-width: 100%;
}

.status-form-table :deep(.v-data-table__th) {
  white-space: nowrap;
  font-size: 0.75rem;
  padding: 8px;
  text-align: center;
}

.status-form-table :deep(.v-data-table__td) {
  padding: 8px;
  text-align: center;
}

.status-form-table :deep(.v-data-table__th:first-child),
.status-form-table :deep(.v-data-table__td:first-child) {
  position: sticky;
  left: 0;
  background: white;
  z-index: 2;
  text-align: left;
  width: 200px;
}

.status-form-table :deep(.v-data-table__th:nth-child(2)),
.status-form-table :deep(.v-data-table__td:nth-child(2)) {
  position: sticky;
  left: 200px;
  background: white;
  z-index: 2;
  text-align: left;
  width: 150px;
}
</style>
