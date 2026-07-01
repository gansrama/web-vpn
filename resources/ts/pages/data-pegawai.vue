<script lang="ts" setup>
import { ref, onMounted, computed, watch } from 'vue'
import { useTheme } from 'vuetify'
import type { Employee } from '@/types'

// Theme
const theme = useTheme()

// Data state
const employees = ref<Employee[]>([])
const loading = ref(true)
const search = ref('')
const selectedEmployee = ref<Employee | null>(null)
const showDialog = ref(false)
const deleteDialog = ref(false)
const employeeToDelete = ref<Employee | null>(null)
const isDeleting = ref(false)
const deleteError = ref('')

// Add Employee Modal State
const isAddEmployeeModalVisible = ref(false)
const newEmployee = ref({
  nama_lengkap: '',
  nomor_ktp: '',
  email: '',
  username_vpn: '',
  posisi_jabatan: '',
  nama_organisasi: '',
  nomer_hp_wa: ''
})
const isSubmitting = ref(false)
const addEmployeeError = ref('')

// Pagination
const currentPage = ref(1)
const itemsPerPage = ref(10)

// Headers for data table
const headers = ref([
  { title: 'Nama Lengkap', key: 'nama_lengkap', sortable: true },
  { title: 'Nomor KTP', key: 'nomor_ktp', sortable: true },
  { title: 'Email', key: 'email', sortable: true },
  { title: 'Username VPN', key: 'username_vpn', sortable: true },
  { title: 'Posisi/Jabatan', key: 'posisi_jabatan', sortable: true },
  { title: 'Organisasi', key: 'nama_organisasi', sortable: true },
  { title: 'No. HP/WA', key: 'nomer_hp_wa', sortable: false },
  { title: 'Actions', key: 'actions', sortable: false, align: 'center' as const }
])

// Computed properties
const filteredEmployees = computed(() => {
  if (!search.value) return employees.value
  
  const searchTerm = search.value.toLowerCase()
  return employees.value.filter(employee => 
    employee.nama_lengkap.toLowerCase().includes(searchTerm) ||
    employee.email.toLowerCase().includes(searchTerm) ||
    employee.username_vpn.toLowerCase().includes(searchTerm) ||
    employee.posisi_jabatan.toLowerCase().includes(searchTerm) ||
    employee.nama_organisasi.toLowerCase().includes(searchTerm) ||
    employee.nomor_ktp.includes(searchTerm)
  )
})

const paginatedEmployees = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  const end = start + itemsPerPage.value
  return filteredEmployees.value.slice(start, end)
})

const totalPages = computed(() => {
  return Math.ceil(filteredEmployees.value.length / itemsPerPage.value)
})

// Methods
const fetchEmployees = async () => {
  try {
    loading.value = true
    // Load smaller initial dataset for faster performance
    const response = await fetch('/api/employees?per_page=50')
    const data = await response.json()
    
    if (data.success) {
      employees.value = data.data
      console.log(`Loaded ${data.data.length} employees out of ${data.pagination?.total || 'unknown'} total`)
    } else {
      console.error('Failed to fetch employees:', data.message)
    }
  } catch (error) {
    console.error('Error fetching employees:', error)
  } finally {
    loading.value = false
  }
}

const viewEmployee = (employee: Employee) => {
  selectedEmployee.value = employee
  showDialog.value = true
}

const confirmDelete = (employee: Employee) => {
  employeeToDelete.value = employee
  deleteError.value = ''
  deleteDialog.value = true
}

const deleteEmployee = async () => {
  if (!employeeToDelete.value) return
  
  try {
    isDeleting.value = true
    deleteError.value = ''
    
    const response = await fetch(`/api/employees/${employeeToDelete.value.id}`, {
      method: 'DELETE',
      headers: {
        'Accept': 'application/json'
      }
    })
    
    const data = await response.json()
    
    if (data.success) {
      // Remove employee from the list
      employees.value = employees.value.filter(emp => emp.id !== employeeToDelete.value!.id)
      // Close dialog and reset state
      deleteDialog.value = false
      employeeToDelete.value = null
      console.log('Employee deleted successfully')
    } else {
      deleteError.value = data.message || 'Gagal menghapus pegawai'
      console.error('Failed to delete employee:', data.message)
    }
  } catch (error) {
    deleteError.value = 'Terjadi kesalahan saat menghapus pegawai'
    console.error('Error deleting employee:', error)
  } finally {
    isDeleting.value = false
  }
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

// Add Employee Functions
const openAddEmployeeModal = () => {
  isAddEmployeeModalVisible.value = true
  resetNewEmployeeForm()
}

const resetNewEmployeeForm = () => {
  newEmployee.value = {
    nama_lengkap: '',
    nomor_ktp: '',
    email: '',
    username_vpn: '',
    posisi_jabatan: '',
    nama_organisasi: '',
    nomer_hp_wa: ''
  }
  addEmployeeError.value = ''
}

const addEmployee = async () => {
  try {
    isSubmitting.value = true
    addEmployeeError.value = ''
    
    const response = await fetch('/api/employees', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(newEmployee.value)
    })
    
    const data = await response.json()
    
    if (data.success) {
      // Add new employee to the list
      employees.value.unshift(data.data)
      // Close modal and reset form
      isAddEmployeeModalVisible.value = false
      resetNewEmployeeForm()
      console.log('Employee added successfully')
    } else {
      addEmployeeError.value = data.message || 'Gagal menambahkan pegawai'
      console.error('Failed to add employee:', data.message)
    }
  } catch (error) {
    addEmployeeError.value = 'Terjadi kesalahan saat menambahkan pegawai'
    console.error('Error adding employee:', error)
  } finally {
    isSubmitting.value = false
  }
}

// Watch for page changes to refetch data
watch(currentPage, () => {
  fetchEmployees()
})

// Watch for items per page changes
watch(itemsPerPage, () => {
  currentPage.value = 1
  fetchEmployees()
})

// Lifecycle
onMounted(() => {
  fetchEmployees()
})
</script>

<template>
  <VCard class="pa-6">
    <VCardTitle class="text-h5 mb-4 d-flex align-center justify-space-between">
      <div class="d-flex align-center">
        <VIcon icon="ri-group-line" class="mr-2" />
        Data Pegawai
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
      
      <div class="d-flex gap-2">
        <VBtn
          color="primary"
          prepend-icon="ri-add-line"
          @click="openAddEmployeeModal"
        >
          Tambah Pegawai
        </VBtn>
        
        <VBtn
          color="secondary"
          prepend-icon="ri-refresh-line"
          @click="fetchEmployees"
          :loading="loading"
        >
          Refresh
        </VBtn>
      </div>
    </VCardText>
    
    <!-- Data Table -->
    <VCardText>
      <div class="d-flex align-center justify-space-between mb-4">
        <VSelect
          v-model="itemsPerPage"
          :items="[5, 10, 25, 50, 100]"
          label="Items per page"
          variant="outlined"
          hide-details
          style="max-width: 150px"
        />
        <div class="text-body-2">
          Menampilkan {{ paginatedEmployees.length }} dari {{ filteredEmployees.length }} data
        </div>
      </div>
      
      <VDataTable
        :headers="headers"
        :items="paginatedEmployees"
        :loading="loading"
        loading-text="Memuat data..."
        no-data-text="Tidak ada data pegawai"
        class="elevation-1"
        :items-per-page="itemsPerPage"
        hide-default-footer
      >
        <!-- Template for actions -->
        <template #item.actions="{ item }">
          <div class="d-flex gap-2">
            <VBtn
              icon="ri-eye-line"
              size="small"
              variant="text"
              color="primary"
              @click="viewEmployee(item)"
            />
            <VBtn
              icon="ri-delete-bin-line"
              size="small"
              variant="text"
              color="error"
              @click="confirmDelete(item)"
            />
          </div>
        </template>
      </VDataTable>
      
      <!-- Pagination -->
      <div class="d-flex justify-center mt-4">
        <VPagination
          v-model="currentPage"
          :length="totalPages"
          :total-visible="5"
          circle
        />
      </div>
    </VCardText>
    
    <!-- View Dialog -->
    <VDialog v-model="showDialog" max-width="600">
      <VCard>
        <VCardTitle class="text-h6 pa-4">
          Detail Pegawai
        </VCardTitle>
        
        <VCardText v-if="selectedEmployee">
          <VRow>
            <VCol cols="12" md="6">
              <VTextField
                :model-value="selectedEmployee.nama_lengkap"
                label="Nama Lengkap"
                readonly
                variant="outlined"
              />
            </VCol>
            <VCol cols="12" md="6">
              <VTextField
                :model-value="selectedEmployee.nomor_ktp"
                label="Nomor KTP"
                readonly
                variant="outlined"
              />
            </VCol>
            <VCol cols="12" md="6">
              <VTextField
                :model-value="selectedEmployee.email"
                label="Email"
                readonly
                variant="outlined"
              />
            </VCol>
            <VCol cols="12" md="6">
              <VTextField
                :model-value="selectedEmployee.username_vpn"
                label="Username VPN"
                readonly
                variant="outlined"
              />
            </VCol>
            <VCol cols="12" md="6">
              <VTextField
                :model-value="selectedEmployee.posisi_jabatan"
                label="Posisi/Jabatan"
                readonly
                variant="outlined"
              />
            </VCol>
            <VCol cols="12" md="6">
              <VTextField
                :model-value="selectedEmployee.nama_organisasi"
                label="Organisasi"
                readonly
                variant="outlined"
              />
            </VCol>
            <VCol cols="12" md="6">
              <VTextField
                :model-value="selectedEmployee.nomer_hp_wa"
                label="No. HP/WA"
                readonly
                variant="outlined"
              />
            </VCol>
            <VCol cols="12">
              <VTextField
                :model-value="formatDate(selectedEmployee.created_at)"
                label="Tanggal Dibuat"
                readonly
                variant="outlined"
              />
            </VCol>
          </VRow>
        </VCardText>
        
        <VCardActions class="pa-4">
          <VSpacer />
          <VBtn @click="showDialog = false">
            Tutup
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
    
    <!-- Delete Confirmation Dialog -->
    <VDialog v-model="deleteDialog" max-width="400">
      <VCard>
        <VCardTitle class="text-h6 pa-4">
          <VIcon icon="ri-delete-bin-line" class="mr-2" color="error" />
          Konfirmasi Hapus
        </VCardTitle>
        
        <!-- Error Message -->
        <VAlert
          v-if="deleteError"
          type="error"
          variant="tonal"
          class="ma-4"
        >
          {{ deleteError }}
        </VAlert>
        
        <VCardText v-if="employeeToDelete">
          Apakah Anda yakin ingin menghapus pegawai "<strong>{{ employeeToDelete.nama_lengkap }}</strong>"?
        </VCardText>
        
        <VCardActions class="pa-4">
          <VSpacer />
          <VBtn
            variant="text"
            @click="deleteDialog = false"
            :disabled="isDeleting"
          >
            Batal
          </VBtn>
          <VBtn
            color="error"
            @click="deleteEmployee"
            :loading="isDeleting"
            :disabled="isDeleting"
          >
            {{ isDeleting ? 'Menghapus...' : 'Hapus' }}
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
    
    <!-- Add Employee Modal -->
    <VDialog v-model="isAddEmployeeModalVisible" max-width="800">
      <VCard>
        <VCardTitle class="text-h6 pa-4">
          <VIcon icon="ri-user-add-line" class="mr-2" />
          Tambah Pegawai Baru
        </VCardTitle>
        
        <VCardText class="pa-4">
          <!-- Error Message -->
          <VAlert
            v-if="addEmployeeError"
            type="error"
            variant="tonal"
            class="mb-4"
          >
            {{ addEmployeeError }}
          </VAlert>
          
          <VForm @submit.prevent="addEmployee">
            <VRow>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="newEmployee.nama_lengkap"
                  label="Nama Lengkap"
                  placeholder="Masukkan nama lengkap"
                  :rules="[v => !!v || 'Nama lengengkap harus diisi']"
                  required
                  variant="outlined"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="newEmployee.nomor_ktp"
                  label="Nomor KTP"
                  placeholder="Masukkan nomor KTP"
                  :rules="[v => !!v || 'Nomor KTP harus diisi']"
                  required
                  variant="outlined"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="newEmployee.email"
                  label="Email"
                  type="email"
                  placeholder="Masukkan email"
                  :rules="[v => !!v || 'Email harus diisi', v => /.+@.+\..+/.test(v) || 'Email tidak valid']"
                  required
                  variant="outlined"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="newEmployee.username_vpn"
                  label="Username VPN"
                  placeholder="Masukkan username VPN"
                  :rules="[v => !!v || 'Username VPN harus diisi']"
                  required
                  variant="outlined"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="newEmployee.posisi_jabatan"
                  label="Posisi/Jabatan"
                  placeholder="Masukkan posisi/jabatan"
                  :rules="[v => !!v || 'Posisi/Jabatan harus diisi']"
                  required
                  variant="outlined"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="newEmployee.nama_organisasi"
                  label="Organisasi"
                  placeholder="Masukkan nama organisasi"
                  :rules="[v => !!v || 'Organisasi harus diisi']"
                  required
                  variant="outlined"
                />
              </VCol>
              <VCol cols="12">
                <VTextField
                  v-model="newEmployee.nomer_hp_wa"
                  label="No. HP/WA"
                  placeholder="Masukkan nomor HP/WA"
                  :rules="[v => !!v || 'No. HP/WA harus diisi']"
                  required
                  variant="outlined"
                />
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
        
        <VCardActions class="pa-4">
          <VSpacer />
          <VBtn
            variant="text"
            @click="isAddEmployeeModalVisible = false"
            :disabled="isSubmitting"
          >
            Batal
          </VBtn>
          <VBtn
            color="primary"
            @click="addEmployee"
            :loading="isSubmitting"
            :disabled="isSubmitting"
          >
            {{ isSubmitting ? 'Menyimpan...' : 'Simpan' }}
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </VCard>
</template>

<style scoped>
.v-data-table {
  border-radius: 8px;
}
</style>
