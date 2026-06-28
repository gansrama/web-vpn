<script lang="ts" setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useTheme } from 'vuetify'
import type { Employee, VpnServer, AksesLogicFormData, TeleworkingFormData } from '@/types'
import { maskEmail, maskPhone, maskNIK } from '@/utils/masking'
import SignaturePad from '@/components/SignaturePad.vue'

const router = useRouter()
const theme = useTheme()

// Form Akses Logic Data
const aksesLogicFormData = ref<AksesLogicFormData>({
  employeeId: '',
  nama_lengkap: '',
  nomor_ktp: '',
  email: '',
  username_vpn: '',
  posisi_jabatan: '',
  nama_organisasi: '',
  nomer_hp_wa: '',
  position: '',
  department: '',
  manager: '',
  accessLevel: 'clientless',
  vpnServer: '',
  duration: '',
  reason: '',
  deviceType: '',
  accessType: '',
  agreeToTerms: false,
  agreeToPolicy: false,
  signature_image: undefined,
  nama_sistem: '',
  jenis_akses: '',
  masa_berlaku: '',
  keperluan_vpn: '',
  pengguna_hak_akses: '',
  sudah_menandatangani_surat_pernyataan: false,
  memahami_kebijakan_keamanan: false,
  posisi_pemohon: ''
})

// Dynamic list for multiple VPN servers
const vpnServersList = ref<Array<{
  id: number
  nama_sistem: string
  ip_address: string
  jenis_akses: string
}>>([])

let nextVpnServerId = 1

// Functions to manage VPN servers list
const addVpnServer = () => {
  if (vpnServersList.value.length >= 5) {
    alert('Maksimal 5 server yang dapat ditambahkan')
    return
  }
  if (aksesLogicFormData.value.vpnServer && aksesLogicFormData.value.accessLevel) {
    const selectedServer = vpnServers.value.find(server => server.nama_sistem === aksesLogicFormData.value.vpnServer)
    const ipAddress = selectedServer?.ip_address || ''
    
    // Check if IP address already exists in the list
    const ipExists = vpnServersList.value.some(server => server.ip_address === ipAddress)
    if (ipExists) {
      alert('IP Address ini sudah ditambahkan. Silakan pilih server dengan IP yang berbeda.')
      return
    }
    
    vpnServersList.value.push({
      id: nextVpnServerId++,
      nama_sistem: aksesLogicFormData.value.vpnServer,
      ip_address: ipAddress,
      jenis_akses: aksesLogicFormData.value.accessLevel
    })
    // Reset VPN server fields for next entry
    aksesLogicFormData.value.vpnServer = ''
    aksesLogicFormData.value.deviceType = ''
    aksesLogicFormData.value.accessLevel = 'clientless'
  }
}

const removeVpnServer = (id: number) => {
  vpnServersList.value = vpnServersList.value.filter(item => item.id !== id)
}

// Helper function to get access level text
const getAccessLevelText = (accessLevel: string) => {
  switch (accessLevel) {
    case 'clientless': return 'SSL VPN'
    case 'client': return 'Endpoint/Client-Based VPN'
    case 'ipsec': return 'IPsec VPN'
    default: return accessLevel
  }
}

// Form Teleworking Data
const teleworkingFormData = ref<TeleworkingFormData>({
  employeeId: '',
  nama_lengkap: '',
  nomor_ktp: '',
  email: '',
  username_vpn: '',
  posisi_jabatan: '',
  nama_organisasi: '',
  nomer_hp_wa: '',
  position: '',
  department: '',
  manager: '',
  duration: '',
  reason: '',
  accessType: '',
  agreeToTerms: false,
  agreeToPolicy: false,
  signature_image: undefined,
  masa_berlaku: '',
  keperluan_vpn: '',
  pengguna_hak_akses: '',
  sudah_menandatangani_surat_pernyataan: false,
  memahami_kebijakan_keamanan: false,
  posisi_pemohon: ''
})

// API Data
const employees = ref<Employee[]>([])
const vpnServers = ref<VpnServer[]>([])
const loading = ref(false)
const currentTab = ref(0)
const isDarkMode = ref(false)
const isMobile = ref(false)
const isScrolled = ref(false)

// Mobile detection
const checkMobile = () => {
  isMobile.value = window.innerWidth <= 768
}

// Scroll detection
const handleScroll = () => {
  isScrolled.value = window.scrollY > 50
}

// Computed property for header class
const headerClass = computed(() => {
  return isScrolled.value ? 'blue-bold' : 'transparent'
})

// Computed properties for masked display
const maskedAksesLogicData = computed(() => ({
  email: maskEmail(aksesLogicFormData.value.email || ''),
  nomer_hp_wa: maskPhone(aksesLogicFormData.value.manager || ''),
  nomor_ktp: maskNIK(aksesLogicFormData.value.nomor_ktp || '')
}))

const maskedTeleworkingData = computed(() => ({
  email: maskEmail(teleworkingFormData.value.email || ''),
  nomer_hp_wa: maskPhone(teleworkingFormData.value.manager || ''),
  nomor_ktp: maskNIK(teleworkingFormData.value.nomor_ktp || '')
}))

// Computed properties for mobile display
const logoText = computed(() => isMobile.value ? 'Webform' : 'Webform VPN')
const cityText = computed(() => isMobile.value ? 'JSC' : 'JAKARTA SMART CITY')
const showBeranda = computed(() => !isMobile.value)

// Hero content
const heroTitle = ref('Portal Layanan VPN & Akses')
const heroSubtitle = ref('Isi formulir sesuai dengan kebutuhan Anda')

// Static data
const vpnRequirements = ref([
  { title: 'Akses Remote Database Server', value: 'Akses Remote Database Server' },
  { title: 'Akses Internal Network', value: 'Akses Internal Network' },
  { title: 'Akses File Server', value: 'Akses File Server' },
  { title: 'Akses Development Server', value: 'Akses Development Server' },
  { title: 'Akses Testing Environment', value: 'Akses Testing Environment' },
  { title: 'Akses Production Server', value: 'Akses Production Server' },
  { title: 'Akses Monitoring Tools', value: 'Akses Monitoring Tools' },
  { title: 'Akses Backup Server', value: 'Akses Backup Server' },
  { title: 'Perpanjangan User Akun VPN', value: 'Perpanjangan User Akun VPN' },
  { title: 'Pengajuan User Akun VPN Baru', value: 'Pengajuan User Akun VPN Baru' },
  { title: 'Reset Password User Akun VPN', value: 'Reset Password User Akun VPN' },
  { title: 'Penambahan Akses IP Address VPN', value: 'Penambahan Akses IP Address VPN' },
  { title: 'Delete Google Auth Code', value: 'Delete Google Auth Code' }
])

const accessLevels = ref([
  { title: 'SSL VPN', value: 'clientless' },
  { title: 'Endpoint/Client-Based VPN', value: 'client' },
  { title: 'IPsec VPN', value: 'ipsec' }
])

const durations = ref([
  { title: 'Januari s.d Maret', value: 'q1' },
  { title: 'April s.d Juni', value: 'q2' },
  { title: 'Juli s.d September', value: 'q3' },
  { title: 'Oktober s.d Desember', value: 'q4' }
])

const accessTypes = ref([
  { title: 'ASN', value: 'asn' },
  { title: 'Non ASN', value: 'non-asn' }
])

// Fetch employees
const fetchEmployees = async () => {
  try {
    loading.value = true
    const response = await fetch('/api/employees?per_page=1000')
    const data = await response.json()
    
    if (data.success) {
      employees.value = data.data
    }
  } catch (error) {
    console.error('Error fetching employees:', error)
  } finally {
    loading.value = false
  }
}

// Fetch VPN servers
const fetchVpnServers = async () => {
  try {
    const response = await fetch('/api/vpn-servers')
    const data = await response.json()
    
    if (data.success) {
      const uniqueServers = data.data.reduce((acc: VpnServer[], server: VpnServer) => {
        const existingIndex = acc.findIndex(s => s.nama_sistem === server.nama_sistem)
        if (existingIndex === -1) {
          acc.push(server)
        }
        return acc
      }, [])
      
      // Add special options at the beginning of the list
      const specialOptions: VpnServer[] = [
        {
          id: 0,
          nama_sistem: 'All Product',
          server_location: 'Special Case',
          ip_address: 'All IP Product',
          project: 'All Products',
          is_active: true,
          created_at: new Date().toISOString(),
          updated_at: new Date().toISOString()
        },
        {
          id: -1,
          nama_sistem: 'All IP Product',
          server_location: 'Special Case',
          ip_address: 'All IP Product',
          project: 'All IP Products',
          is_active: true,
          created_at: new Date().toISOString(),
          updated_at: new Date().toISOString()
        }
      ]
      
      vpnServers.value = [...specialOptions, ...uniqueServers]
    }
  } catch (error) {
    console.error('Error fetching VPN servers:', error)
  }
}

// Watch for employee selection - Akses Logic
watch(() => aksesLogicFormData.value.employeeId, (newEmployeeId) => {
  if (newEmployeeId) {
    const selectedEmployee = employees.value.find(emp => emp.id === Number(newEmployeeId))
    if (selectedEmployee) {
      aksesLogicFormData.value.nama_lengkap = selectedEmployee.nama_lengkap
      aksesLogicFormData.value.nomor_ktp = selectedEmployee.nomor_ktp
      aksesLogicFormData.value.email = selectedEmployee.email
      aksesLogicFormData.value.position = selectedEmployee.posisi_jabatan
      aksesLogicFormData.value.department = selectedEmployee.nama_organisasi
      aksesLogicFormData.value.manager = selectedEmployee.nomer_hp_wa
    }
  }
})

// Watch for employee selection - Teleworking
watch(() => teleworkingFormData.value.employeeId, (newEmployeeId) => {
  if (newEmployeeId) {
    const selectedEmployee = employees.value.find(emp => emp.id === Number(newEmployeeId))
    if (selectedEmployee) {
      teleworkingFormData.value.nama_lengkap = selectedEmployee.nama_lengkap
      teleworkingFormData.value.nomor_ktp = selectedEmployee.nomor_ktp
      teleworkingFormData.value.email = selectedEmployee.email
      teleworkingFormData.value.position = selectedEmployee.posisi_jabatan
      teleworkingFormData.value.department = selectedEmployee.nama_organisasi
      teleworkingFormData.value.manager = selectedEmployee.nomer_hp_wa
    }
  }
})

// Watch for VPN server selection - Akses Logic
watch(() => aksesLogicFormData.value.vpnServer, (newVpnServer) => {
  if (newVpnServer) {
    const selectedServer = vpnServers.value.find(server => server.nama_sistem === newVpnServer)
    if (selectedServer) {
      aksesLogicFormData.value.deviceType = selectedServer.ip_address
    }
  } else {
    aksesLogicFormData.value.deviceType = ''
  }
}, { immediate: false })


// Form tabs data
const formTabs = ref([
  { id: 0, title: 'Form Akses Logic', icon: 'ri-shield-keyhole-line' },
  { id: 1, title: 'Form Teleworking', icon: 'ri-home-office-line' },
  { id: 2, title: 'Form Google', icon: 'ri-google-line' }
])

// Toggle theme function
const toggleTheme = () => {
  isDarkMode.value = !isDarkMode.value
  document.documentElement.classList.toggle('dark', isDarkMode.value)
}

// Stats
const stats = ref([
  { label: 'Formulir Tersedia', value: '3', icon: 'ri-file-list-3-line' },
  { label: 'Pengguna Aktif', value: '40+', icon: 'ri-user-3-line' },
  { label: 'Akses Aman', value: '24/7', icon: 'ri-shield-check-line' }
])

// Form validation
const isAksesLogicValid = computed(() => {
  return aksesLogicFormData.value.employeeId &&
         aksesLogicFormData.value.email &&
         vpnServersList.value.length > 0 &&
         aksesLogicFormData.value.duration &&
         aksesLogicFormData.value.reason &&
         aksesLogicFormData.value.accessType &&
         // aksesLogicFormData.value.signature_image &&
         aksesLogicFormData.value.agreeToTerms &&
         aksesLogicFormData.value.agreeToPolicy
})

const isTeleworkingValid = computed(() => {
  return teleworkingFormData.value.employeeId &&
         teleworkingFormData.value.email &&
         teleworkingFormData.value.duration &&
         teleworkingFormData.value.reason &&
         teleworkingFormData.value.accessType &&
         // teleworkingFormData.value.signature_image &&
         teleworkingFormData.value.agreeToTerms &&
         teleworkingFormData.value.agreeToPolicy
})


// Submit handlers
const submitAksesLogic = async () => {
  if (!isAksesLogicValid.value) {
    alert('Mohon lengkapi semua field yang wajib diisi')
    return
  }
  
  try {
    // Fetch unmasked employee data for form submission
    let selectedEmployee = null
    if (aksesLogicFormData.value.employeeId) {
      try {
        const response = await fetch(`/api/employees/${aksesLogicFormData.value.employeeId}/unmasked`)
        const result = await response.json()
        if (result.success) {
          selectedEmployee = result.data
        }
      } catch (error) {
        console.error('Error fetching unmasked employee data:', error)
        // Fallback to masked data if unmasked endpoint fails
        selectedEmployee = employees.value.find(emp => emp.id === Number(aksesLogicFormData.value.employeeId))
      }
    }
    
    const formDataToSubmit: any = {
      nama_lengkap: selectedEmployee?.nama_lengkap || aksesLogicFormData.value.nama_lengkap,
      nomor_ktp: selectedEmployee?.nomor_ktp || aksesLogicFormData.value.nomor_ktp,
      email: selectedEmployee?.email || aksesLogicFormData.value.email,
      posisi_jabatan: selectedEmployee?.posisi_jabatan || aksesLogicFormData.value.position,
      nama_organisasi: selectedEmployee?.nama_organisasi || aksesLogicFormData.value.department,
      nomer_hp_wa: selectedEmployee?.nomer_hp_wa || aksesLogicFormData.value.manager,
      masa_berlaku: String(aksesLogicFormData.value.duration || ''),
      keperluan_vpn: String(aksesLogicFormData.value.reason || ''),
      pengguna_hak_akses: String(aksesLogicFormData.value.accessType || ''),
      sudah_menandatangani_surat_pernyataan: !!aksesLogicFormData.value.agreeToTerms,
      memahami_kebijakan_keamanan: !!aksesLogicFormData.value.agreeToPolicy,
      posisi_pemohon: `${selectedEmployee?.nama_organisasi || 'Jakarta'}, ${new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })}`,
      vpn_servers: vpnServersList.value
    }

    console.log('Form data to submit:', {
      ...formDataToSubmit,
      email: maskEmail(formDataToSubmit.email || ''),
      nomor_ktp: maskNIK(formDataToSubmit.nomor_ktp || ''),
      nomer_hp_wa: maskPhone(formDataToSubmit.nomer_hp_wa || '')
    })

    // Add signature image if it exists (base64 string or file)
    if (aksesLogicFormData.value.signature_image) {
      formDataToSubmit.signature_image = aksesLogicFormData.value.signature_image
    }

    // Use FormData for file upload
    const submitData = new FormData()
    
    // Add all form fields
    Object.keys(formDataToSubmit).forEach((key: string) => {
      if (key !== 'signature_image' && key !== 'vpn_servers') {
        const value = formDataToSubmit[key]
        // Handle boolean values properly
        if (typeof value === 'boolean') {
          submitData.append(key, value ? '1' : '0')
        } else {
          submitData.append(key, String(value))
        }
      }
    })
    
    // Add VPN servers as JSON
    submitData.append('vpn_servers', JSON.stringify(formDataToSubmit.vpn_servers))
    
    // Add signature image if it exists (base64 string or file)
    if (aksesLogicFormData.value.signature_image) {
      if (aksesLogicFormData.value.signature_image instanceof File) {
        submitData.append('signature_image', aksesLogicFormData.value.signature_image)
      } else if (typeof aksesLogicFormData.value.signature_image === 'string') {
        const base64String = aksesLogicFormData.value.signature_image as string
        const base64Data = base64String.split(',')[1]
        const byteCharacters = atob(base64Data)
        const byteNumbers = new Array(byteCharacters.length)
        for (let i = 0; i < byteCharacters.length; i++) {
          byteNumbers[i] = byteCharacters.charCodeAt(i)
        }
        const byteArray = new Uint8Array(byteNumbers)
        const blob = new Blob([byteArray], { type: 'image/png' })
        submitData.append('signature_image', blob, 'signature.png')
      }
    }

    const response = await fetch('/api/akses-logic-requests', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: submitData
    })

    const result = await response.json()
    
    if (result.success) {
      alert('Form Akses Logic berhasil disubmit!')
      // Reset form
      resetAksesLogicForm()
    } else {
      console.error('Validation errors:', result.errors)
      const errorMessage = result.errors ? Object.values(result.errors).flat().join(', ') : (result.message || 'Terjadi kesalahan')
      alert('Error: ' + errorMessage)
    }
  } catch (error) {
    console.error('Submit error:', error)
    alert('Terjadi kesalahan saat submit form')
  }
}

const submitTeleworking = async () => {
  if (!isTeleworkingValid.value) {
    alert('Mohon lengkapi semua field yang wajib diisi')
    return
  }
  
  try {
    // Fetch unmasked employee data for form submission
    let selectedEmployee = null
    if (teleworkingFormData.value.employeeId) {
      try {
        const response = await fetch(`/api/employees/${teleworkingFormData.value.employeeId}/unmasked`)
        const result = await response.json()
        if (result.success) {
          selectedEmployee = result.data
        }
      } catch (error) {
        console.error('Error fetching unmasked employee data:', error)
        // Fallback to masked data if unmasked endpoint fails
        selectedEmployee = employees.value.find(emp => emp.id === Number(teleworkingFormData.value.employeeId))
      }
    }
    
    const formDataToSubmit: any = {
      nama_lengkap: selectedEmployee?.nama_lengkap || teleworkingFormData.value.nama_lengkap,
      nomor_ktp: selectedEmployee?.nomor_ktp || teleworkingFormData.value.nomor_ktp,
      email: selectedEmployee?.email || teleworkingFormData.value.email,
      posisi_jabatan: selectedEmployee?.posisi_jabatan || teleworkingFormData.value.position,
      nama_organisasi: selectedEmployee?.nama_organisasi || teleworkingFormData.value.department,
      nomer_hp_wa: selectedEmployee?.nomer_hp_wa || teleworkingFormData.value.manager,
      masa_berlaku: String(teleworkingFormData.value.duration || ''),
      keperluan_vpn: String(teleworkingFormData.value.reason || ''),
      pengguna_hak_akses: String(teleworkingFormData.value.accessType || ''),
      sudah_menandatangani_surat_pernyataan: !!teleworkingFormData.value.agreeToTerms,
      memahami_kebijakan_keamanan: !!teleworkingFormData.value.agreeToPolicy,
      posisi_pemohon: `${selectedEmployee?.nama_organisasi || 'Jakarta'}, ${new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })}`
    }

    console.log('Form data to submit:', {
      ...formDataToSubmit,
      email: maskEmail(formDataToSubmit.email || ''),
      nomor_ktp: maskNIK(formDataToSubmit.nomor_ktp || ''),
      nomer_hp_wa: maskPhone(formDataToSubmit.nomer_hp_wa || '')
    })

    // Add signature image if it exists (base64 string or file)
    if (teleworkingFormData.value.signature_image) {
      formDataToSubmit.signature_image = teleworkingFormData.value.signature_image
    }

    // Use FormData for file upload
    const submitData = new FormData()
    
    // Add all form fields
    Object.keys(formDataToSubmit).forEach((key: string) => {
      if (key !== 'signature_image') {
        const value = formDataToSubmit[key]
        // Handle boolean values properly
        if (typeof value === 'boolean') {
          submitData.append(key, value ? '1' : '0')
        } else {
          submitData.append(key, String(value))
        }
      }
    })
    
    // Add signature image if it exists (base64 string or file)
    if (teleworkingFormData.value.signature_image) {
      if (teleworkingFormData.value.signature_image instanceof File) {
        submitData.append('signature_image', teleworkingFormData.value.signature_image)
      } else if (typeof teleworkingFormData.value.signature_image === 'string') {
        const base64String = teleworkingFormData.value.signature_image as string
        const base64Data = base64String.split(',')[1]
        const byteCharacters = atob(base64Data)
        const byteNumbers = new Array(byteCharacters.length)
        for (let i = 0; i < byteCharacters.length; i++) {
          byteNumbers[i] = byteCharacters.charCodeAt(i)
        }
        const byteArray = new Uint8Array(byteNumbers)
        const blob = new Blob([byteArray], { type: 'image/png' })
        submitData.append('signature_image', blob, 'signature.png')
      }
    }

    const response = await fetch('/api/teleworking-requests', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: submitData
    })

    const result = await response.json()
    
    if (result.success) {
      alert('Form Teleworking berhasil disubmit!')
      // Reset form
      resetTeleworkingForm()
    } else {
      alert('Error: ' + (result.message || 'Terjadi kesalahan'))
    }
  } catch (error) {
    console.error('Submit error:', error)
    alert('Terjadi kesalahan saat submit form')
  }
}

// Reset forms
const resetAksesLogicForm = () => {
  aksesLogicFormData.value.employeeId = ''
  aksesLogicFormData.value.nama_lengkap = ''
  aksesLogicFormData.value.nomor_ktp = ''
  aksesLogicFormData.value.email = ''
  aksesLogicFormData.value.position = ''
  aksesLogicFormData.value.department = ''
  aksesLogicFormData.value.manager = ''
  aksesLogicFormData.value.accessLevel = 'clientless'
  aksesLogicFormData.value.vpnServer = ''
  aksesLogicFormData.value.duration = ''
  aksesLogicFormData.value.reason = ''
  aksesLogicFormData.value.deviceType = ''
  aksesLogicFormData.value.accessType = ''
  aksesLogicFormData.value.agreeToTerms = false
  aksesLogicFormData.value.agreeToPolicy = false
  aksesLogicFormData.value.signature_image = undefined
  vpnServersList.value = []
  nextVpnServerId = 1
}

const resetTeleworkingForm = () => {
  teleworkingFormData.value.employeeId = ''
  teleworkingFormData.value.nama_lengkap = ''
  teleworkingFormData.value.nomor_ktp = ''
  teleworkingFormData.value.email = ''
  teleworkingFormData.value.position = ''
  teleworkingFormData.value.department = ''
  teleworkingFormData.value.manager = ''
  teleworkingFormData.value.duration = ''
  teleworkingFormData.value.reason = ''
  teleworkingFormData.value.accessType = ''
  teleworkingFormData.value.agreeToTerms = false
  teleworkingFormData.value.agreeToPolicy = false
  teleworkingFormData.value.signature_image = undefined
}

// Navigate to Google Form
const openGoogleForm = () => {
  window.open('https://docs.google.com/forms/d/e/1FAIpQLScvkV7-MhI1WZVLRqYSZV7gQ2_9RBOFFXu-mrDdzKpdBxsueQ/viewform', '_blank')
}

// Lifecycle
onMounted(() => {
  fetchEmployees()
  fetchVpnServers()
  checkMobile()
  window.addEventListener('resize', checkMobile)
  window.addEventListener('scroll', handleScroll)
})

onUnmounted(() => {
  window.removeEventListener('resize', checkMobile)
  window.removeEventListener('scroll', handleScroll)
})
</script>

<template>
  <div class="landing-container" :class="{ 'dark-mode': isDarkMode }">
    <!-- Header Navigation -->
    <header class="landing-header" :class="headerClass">
      <div class="header-container">
        <div class="header-content">
          <div class="logo-section">
            <h1 class="logo-text">{{ logoText }}</h1>
          </div>
          <nav class="nav-menu">
            <RouterLink v-if="showBeranda" to="/landing" class="nav-link">Beranda</RouterLink>
            <RouterLink to="/login" class="nav-link nav-login">Login</RouterLink>
          </nav>
        </div>
      </div>
    </header>
    
    <!-- Theme Toggle -->
    <div class="theme-toggle-container">
      <VBtn
        @click="toggleTheme"
        :icon="isDarkMode ? 'ri-sun-line' : 'ri-moon-line'"
        variant="elevated"
        size="small"
        class="theme-toggle-btn"
        color="white"
      />
    </div>
    
    <!-- Hero Section -->
    <section class="hero-section">
      <div class="hero-content">
        <!-- Logo -->
        <div class="hero-logo-text">
          <img src="/images/jaya-raya-logo.png" alt="TIM DEV Logo" class="logo-white" />
          <h1>{{ cityText }}</h1>
        </div>
        
        <div class="hero-text">
          <h1 class="hero-title">
            {{ heroTitle }}
          </h1>
          <p class="hero-subtitle">
            {{ heroSubtitle }}
          </p>
        </div>
        
        <!-- Stats -->
        <div class="stats-container">
          <div 
            v-for="stat in stats" 
            :key="stat.label"
            class="stat-card"
          >
            <div class="stat-icon">
              <VIcon :icon="stat.icon" size="24" />
            </div>
            <div class="stat-content">
              <div class="stat-value">{{ stat.value }}</div>
              <div class="stat-label">{{ stat.label }}</div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="hero-decoration">
        <div class="floating-card card-1">
          <VIcon icon="ri-vpn-line" size="32" />
        </div>
        <div class="floating-card card-2">
          <VIcon icon="ri-shield-check-line" size="24" />
        </div>
        <div class="floating-card card-3">
          <VIcon icon="ri-lock-line" size="28" />
        </div>
      </div>
    </section>

    <!-- Forms Section -->
    <section class="forms-section">
      <div class="section-header">
        <h2 class="section-title">Isi Formulir Sesuai Kebutuhan</h2>
        <p class="section-subtitle">
          Lengkapi formulir di bawah ini sesuai dengan kebutuhan akses VPN Anda
        </p>
      </div>

      <div class="forms-tabs-container">
        <VTabs
          v-model="currentTab"
          align-tabs="center"
          class="form-tabs"
          color="#2563eb"
          show-arrows
        >
          <VTab
            v-for="tab in formTabs"
            :key="tab.id"
            :value="tab.id"
            class="form-tab"
          >
            <VIcon :icon="tab.icon" class="mr-2" />
            {{ tab.title }}
          </VTab>
        </VTabs>

        <VWindow
          v-model="currentTab"
          class="form-window"
          :touch="{ 'left': () => currentTab = (currentTab + 1) % 3, 'right': () => currentTab = (currentTab - 1 + 3) % 3 }"
        >
          <!-- Form Akses Logic -->
          <VWindowItem :value="0">
            <VCard class="form-card-vertical" variant="outlined">
              <VCardTitle class="form-card-header">
                <div class="form-header-content">
                  <div class="form-icon-container" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);">
                    <VIcon icon="ri-shield-keyhole-line" size="32" color="white" />
                  </div>
                  <div>
                    <h3 class="form-title">Form Akses Logic</h3>
                    <p class="form-description">Pengajuan akses VPN untuk kebutuhan akses logic, database server, dan internal network</p>
                  </div>
                </div>
              </VCardTitle>
              
              <VCardText class="form-card-content">
                <VForm @submit.prevent="submitAksesLogic">
                  <VRow>
                    <!-- Employee Information -->
                    <VCol cols="12">
                      <VCard variant="outlined" class="mb-4">
                        <VCardTitle class="text-h6 pa-3">
                          <VIcon icon="ri-user-line" class="mr-2" />
                          Employee Information
                        </VCardTitle>
                        <VCardText>
                          <VRow>
                            <VCol cols="12" md="6">
                              <VSelect
                                v-model="aksesLogicFormData.employeeId"
                                :items="employees"
                                item-title="nama_lengkap"
                                item-value="id"
                                label="Nama Lengkap"
                                prepend-inner-icon="ri-user-line"
                                variant="outlined"
                                required
                                :loading="loading"
                                :menu-props="{ eager: true }"
                                hide-details="auto"
                                clearable
                                searchable
                              />
                            </VCol>
                            <VCol cols="12" md="6">
                              <VTextField
                                :model-value="maskedAksesLogicData.email"
                                label="Email Address"
                                placeholder="Email will be auto-filled"
                                variant="outlined"
                                required
                                readonly
                                hide-details="auto"
                              />
                            </VCol>
                            <VCol cols="12" md="6">
                              <VTextField
                                v-model="aksesLogicFormData.position"
                                label="Posisi/Jabatan"
                                variant="outlined"
                                required
                                readonly
                                hide-details="auto"
                              />
                            </VCol>
                            <VCol cols="12" md="6">
                              <VTextField
                                v-model="aksesLogicFormData.department"
                                label="Nama Organisasi"
                                variant="outlined"
                                required
                                readonly
                                hide-details="auto"
                              />
                            </VCol>
                            <VCol cols="12" md="6">
                              <VTextField
                                :model-value="maskedAksesLogicData.nomer_hp_wa"
                                label="Nomer HP/WA"
                                variant="outlined"
                                required
                                readonly
                                hide-details="auto"
                              />
                            </VCol>
                          </VRow>
                        </VCardText>
                      </VCard>
                    </VCol>

                    <!-- VPN Configuration -->
                    <VCol cols="12">
                      <VCard variant="outlined" class="mb-4">
                        <VCardTitle class="text-h6 pa-3">
                          <VIcon icon="ri-vpn-line" class="mr-2" />
                          VPN Configuration
                          <VChip size="small" color="info" class="ml-2">
                            Bisa tambah lebih dari 1 server
                          </VChip>
                        </VCardTitle>
                        <VCardText>
                          <!-- Add Server Section -->
                          <VCard variant="outlined" class="mb-4" style="background-color: #f5f5f5;">
                            <VCardTitle class="text-subtitle-1 pa-3">
                              <VIcon icon="ri-add-circle-line" class="mr-2" />
                              Tambah Server Baru
                            </VCardTitle>
                            <VCardText>
                              <VRow>
                                <VCol cols="12" md="4">
                                  <VSelect
                                    v-model="aksesLogicFormData.vpnServer"
                                    :items="vpnServers"
                                    item-title="nama_sistem"
                                    item-value="nama_sistem"
                                    label="Nama Sistem *"
                                    prepend-inner-icon="ri-server-line"
                                    variant="outlined"
                                    :menu-props="{ eager: true }"
                                    hide-details="auto"
                                    :item-props="(item) => ({ key: item.id })"
                                    clearable
                                  />
                                </VCol>
                                <VCol cols="12" md="4">
                                  <VTextField
                                    v-model="aksesLogicFormData.deviceType"
                                    label="IP Address"
                                    placeholder="Auto-filled"
                                    prepend-inner-icon="ri-ipv6-line"
                                    variant="outlined"
                                    hide-details="auto"
                                    readonly
                                  />
                                </VCol>
                                <VCol cols="12" md="4">
                                  <VSelect
                                    v-model="aksesLogicFormData.accessLevel"
                                    :items="accessLevels"
                                    item-title="title"
                                    item-value="value"
                                    label="Jenis Akses *"
                                    prepend-inner-icon="ri-shield-keyhole-line"
                                    variant="outlined"
                                    :menu-props="{ eager: true }"
                                    hide-details="auto"
                                  />
                                </VCol>
                                <VCol cols="12">
                                  <VBtn
                                    type="button"
                                    color="success"
                                    variant="elevated"
                                    @click="addVpnServer"
                                    :disabled="!aksesLogicFormData.vpnServer || !aksesLogicFormData.accessLevel"
                                    block
                                  >
                                    <VIcon icon="ri-add-line" class="mr-2" />
                                    Tambah ke Daftar Server
                                  </VBtn>
                                </VCol>
                              </VRow>
                            </VCardText>
                          </VCard>

                          <!-- List of added VPN servers -->
                          <VCard variant="outlined" class="mb-2" v-if="vpnServersList.length > 0">
                            <VCardTitle class="text-subtitle-1 pa-3 d-flex justify-space-between align-center">
                              <div class="d-flex align-center">
                                <VIcon icon="ri-list-check" class="mr-2" />
                                Daftar Server yang Ditambahkan
                                <VChip size="small" color="primary" class="ml-2">
                                  {{ vpnServersList.length }} / 5 Server
                                </VChip>
                              </div>
                            </VCardTitle>
                            <VCardText>
                              <VTable>
                                <thead>
                                  <tr>
                                    <th>No</th>
                                    <th>Nama Sistem</th>
                                    <th>IP Address</th>
                                    <th>Jenis Akses</th>
                                    <th>Aksi</th>
                                  </tr>
                                </thead>
                                <tbody>
                                  <tr v-for="(server, index) in vpnServersList" :key="server.id">
                                    <td>{{ index + 1 }}</td>
                                    <td>{{ server.nama_sistem }}</td>
                                    <td>{{ server.ip_address }}</td>
                                    <td>{{ getAccessLevelText(server.jenis_akses) }}</td>
                                    <td>
                                      <VBtn
                                        icon="ri-delete-bin-line"
                                        size="small"
                                        color="error"
                                        variant="text"
                                        @click="removeVpnServer(server.id)"
                                      />
                                    </td>
                                  </tr>
                                </tbody>
                              </VTable>
                            </VCardText>
                          </VCard>

                          <VAlert
                            v-if="vpnServersList.length === 0"
                            type="info"
                            variant="tonal"
                            density="compact"
                            class="mt-2"
                          >
                            <div class="d-flex align-center gap-2">
                              <VIcon icon="ri-information-line" />
                              <span>Belum ada server yang ditambahkan. Silakan pilih Nama Sistem dan Jenis Akses, lalu klik "Tambah ke Daftar Server".</span>
                            </div>
                          </VAlert>
                        </VCardText>
                      </VCard>
                    </VCol>

                    <!-- Masa Berlaku -->
                    <VCol cols="12">
                      <VCard variant="outlined" class="mb-4">
                        <VCardTitle class="text-h6 pa-3">
                          <VIcon icon="ri-calendar-line" class="mr-2" />
                          Masa Berlaku
                        </VCardTitle>
                        <VCardText>
                          <VRow>
                            <VCol cols="12" md="6">
                              <VSelect
                                v-model="aksesLogicFormData.duration"
                                :items="durations"
                                item-title="title"
                                item-value="value"
                                label="Masa Berlaku"
                                prepend-inner-icon="ri-calendar-line"
                                variant="outlined"
                                required
                                :menu-props="{ eager: true }"
                                hide-details="auto"
                              />
                            </VCol>
                          </VRow>
                        </VCardText>
                      </VCard>
                    </VCol>

                    <!-- List Keperluan VPN -->
                    <VCol cols="12">
                      <VCard variant="outlined" class="mb-4">
                        <VCardTitle class="text-h6 pa-3">
                          <VIcon icon="ri-list-check" class="mr-2" />
                          List Keperluan VPN
                        </VCardTitle>
                        <VCardText>
                          <VSelect
                            v-model="aksesLogicFormData.reason"
                            :items="vpnRequirements"
                            item-title="title"
                            item-value="value"
                            label="Pilih Keperluan VPN"
                            prepend-inner-icon="ri-list-check"
                            variant="outlined"
                            required
                            :menu-props="{ eager: true }"
                            hide-details="auto"
                          />
                        </VCardText>
                      </VCard>
                    </VCol>

                    <!-- Pengguna Hak Akses -->
                    <VCol cols="12">
                      <VCard variant="outlined" class="mb-4">
                        <VCardTitle class="text-h6 pa-3">
                          <VIcon icon="ri-user-settings-line" class="mr-2" />
                          Pengguna Hak Akses
                        </VCardTitle>
                        <VCardText>
                          <VRadioGroup
                            v-model="aksesLogicFormData.accessType"
                            inline
                            hide-details="auto"
                          >
                            <VRadio
                              label="ASN"
                              value="asn"
                              color="primary"
                            />
                            <VRadio
                              label="Non ASN"
                              value="non-asn"
                              color="primary"
                            />
                          </VRadioGroup>
                        </VCardText>
                      </VCard>
                    </VCol>

                    <!-- Signature -->
                    <VCol cols="12">
                      <SignaturePad
                        v-model="aksesLogicFormData.signature_image"
                        label="Tanda Tangan"
                        required
                      />
                    </VCol>

                    <!-- Terms and Policy Agreement -->
                    <VCol cols="12">
                      <VCard variant="outlined" class="mb-4">
                        <VCardTitle class="text-h6 pa-3">
                          <VIcon icon="ri-file-text-line" class="mr-2" />
                          Terms and Policy Agreement
                        </VCardTitle>
                        <VCardText>
                          <VCheckbox
                            v-model="aksesLogicFormData.agreeToTerms"
                            label="Pengguna hak akses sudah menandatangani Surat Pernyataan Menjaga Kerahasiaan"
                            hide-details="auto"
                            required
                          />
                          <VCheckbox
                            v-model="aksesLogicFormData.agreeToPolicy"
                            label="Pengguna hak akses telah memahami dan bersedia mematuhi kebijakan keamanan informasi pada Jakarta Smart City"
                            hide-details="auto"
                            required
                          />
                        </VCardText>
                      </VCard>
                    </VCol>

                    <VCol cols="12">
                      <VBtn
                        type="submit"
                        color="#2563eb"
                        size="large"
                        variant="elevated"
                        class="submit-btn"
                        :disabled="!isAksesLogicValid"
                      >
                        <VIcon icon="ri-send-plane-line" class="mr-2" />
                        Submit Form Akses Logic
                      </VBtn>
                    </VCol>
                  </VRow>
                </VForm>
              </VCardText>
            </VCard>
          </VWindowItem>

          <!-- Form Teleworking -->
          <VWindowItem :value="1">
            <VCard class="form-card-vertical" variant="outlined">
              <VCardTitle class="form-card-header">
                <div class="form-header-content">
                  <div class="form-icon-container" style="background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);">
                    <VIcon icon="ri-home-office-line" size="32" color="white" />
                  </div>
                  <div>
                    <h3 class="form-title">Form Teleworking</h3>
                    <p class="form-description">Pengajuan akses VPN untuk kebutuhan kerja remote dan teleworking</p>
                  </div>
                </div>
              </VCardTitle>
              
              <VCardText class="form-card-content">
                <VForm @submit.prevent="submitTeleworking">
                  <VRow>
                    <!-- Employee Information -->
                    <VCol cols="12">
                      <VCard variant="outlined" class="mb-4">
                        <VCardTitle class="text-h6 pa-3">
                          <VIcon icon="ri-user-line" class="mr-2" />
                          Employee Information
                        </VCardTitle>
                        <VCardText>
                          <VRow>
                            <VCol cols="12" md="6">
                              <VSelect
                                v-model="teleworkingFormData.employeeId"
                                :items="employees"
                                item-title="nama_lengkap"
                                item-value="id"
                                label="Nama Lengkap"
                                prepend-inner-icon="ri-user-line"
                                variant="outlined"
                                required
                                :loading="loading"
                                :menu-props="{ eager: true }"
                                hide-details="auto"
                                clearable
                                searchable
                              />
                            </VCol>
                            <VCol cols="12" md="6">
                              <VTextField
                                :model-value="maskedTeleworkingData.email"
                                label="Email Address"
                                placeholder="Email will be auto-filled"
                                prepend-inner-icon="ri-mail-line"
                                type="email"
                                variant="outlined"
                                readonly
                                hide-details="auto"
                              />
                            </VCol>
                            <VCol cols="12" md="6">
                              <VTextField
                                v-model="teleworkingFormData.position"
                                label="Posisi/Jabatan"
                                placeholder="Position will be auto-filled"
                                prepend-inner-icon="ri-briefcase-line"
                                variant="outlined"
                                readonly
                                hide-details="auto"
                              />
                            </VCol>
                            <VCol cols="12" md="6">
                              <VTextField
                                v-model="teleworkingFormData.department"
                                label="Nama Organisasi"
                                placeholder="Organization will be auto-filled"
                                prepend-inner-icon="ri-building-line"
                                variant="outlined"
                                readonly
                                hide-details="auto"
                              />
                            </VCol>
                            <VCol cols="12" md="6">
                              <VTextField
                                :model-value="maskedTeleworkingData.nomer_hp_wa"
                                label="Manager/Phone"
                                placeholder="Manager will be auto-filled"
                                prepend-inner-icon="ri-phone-line"
                                variant="outlined"
                                readonly
                                hide-details="auto"
                              />
                            </VCol>
                          </VRow>
                        </VCardText>
                      </VCard>
                    </VCol>

                    <!-- Masa Berlaku -->
                    <VCol cols="12">
                      <VCard variant="outlined" class="mb-4">
                        <VCardTitle class="text-h6 pa-3">
                          <VIcon icon="ri-vpn-line" class="mr-2" />
                          Masa Berlaku
                        </VCardTitle>
                        <VCardText>
                          <VSelect
                            v-model="teleworkingFormData.duration"
                            :items="durations"
                            item-title="title"
                            item-value="value"
                            label="Pilih Durasi"
                            prepend-inner-icon="ri-calendar-line"
                            variant="outlined"
                            required
                            :menu-props="{ eager: true }"
                            hide-details="auto"
                          />
                        </VCardText>
                      </VCard>
                    </VCol>

                    <!-- List Keperluan VPN -->
                    <VCol cols="12">
                      <VCard variant="outlined" class="mb-4">
                        <VCardTitle class="text-h6 pa-3">
                          <VIcon icon="ri-list-check" class="mr-2" />
                          List Keperluan VPN
                        </VCardTitle>
                        <VCardText>
                          <VSelect
                            v-model="teleworkingFormData.reason"
                            :items="vpnRequirements"
                            item-title="title"
                            item-value="value"
                            label="Pilih Keperluan VPN"
                            prepend-inner-icon="ri-list-check"
                            variant="outlined"
                            required
                            :menu-props="{ eager: true }"
                            hide-details="auto"
                          />
                        </VCardText>
                      </VCard>
                    </VCol>

                    <!-- Pengguna Hak Akses -->
                    <VCol cols="12">
                      <VCard variant="outlined" class="mb-4">
                        <VCardTitle class="text-h6 pa-3">
                          <VIcon icon="ri-user-settings-line" class="mr-2" />
                          Pengguna Hak Akses
                        </VCardTitle>
                        <VCardText>
                          <VRadioGroup
                            v-model="teleworkingFormData.accessType"
                            inline
                            hide-details="auto"
                          >
                            <VRadio
                              label="ASN"
                              value="asn"
                              color="primary"
                            />
                            <VRadio
                              label="Non ASN"
                              value="non-asn"
                              color="primary"
                            />
                          </VRadioGroup>
                        </VCardText>
                      </VCard>
                    </VCol>

                    <!-- Signature -->
                    <VCol cols="12">
                      <SignaturePad
                        v-model="teleworkingFormData.signature_image"
                        label="Tanda Tangan"
                        required
                      />
                    </VCol>

                    <!-- Terms and Policy Agreement -->
                    <VCol cols="12">
                      <VCard variant="outlined" class="mb-4">
                        <VCardTitle class="text-h6 pa-3">
                          <VIcon icon="ri-file-text-line" class="mr-2" />
                          Terms and Policy Agreement
                        </VCardTitle>
                        <VCardText>
                          <VCheckbox
                            v-model="teleworkingFormData.agreeToTerms"
                            label="Pengguna hak akses sudah menandatangani Surat Pernyataan Menjaga Kerahasiaan"
                            hide-details="auto"
                            required
                          />
                          <VCheckbox
                            v-model="teleworkingFormData.agreeToPolicy"
                            label="Pengguna hak akses telah memahami dan bersedia mematuhi kebijakan keamanan informasi pada Jakarta Smart City"
                            hide-details="auto"
                            required
                          />
                        </VCardText>
                      </VCard>
                    </VCol>

                    <VCol cols="12">
                      <VBtn
                        type="submit"
                        color="#10b981"
                        size="large"
                        variant="elevated"
                        class="submit-btn"
                        :disabled="!isTeleworkingValid"
                      >
                        <VIcon icon="ri-send-plane-line" class="mr-2" />
                        Submit Form Teleworking
                      </VBtn>
                    </VCol>
                  </VRow>
                </VForm>
              </VCardText>
            </VCard>
          </VWindowItem>

          <!-- Form Google -->
          <VWindowItem :value="2">
            <VCard class="form-card-vertical" variant="outlined">
              <VCardTitle class="form-card-header">
                <div class="form-header-content">
                  <div class="form-icon-container" style="background: linear-gradient(135deg, #2563eb 0%, #60a5fa 100%);">
                    <VIcon icon="ri-google-line" size="32" color="white" />
                  </div>
                  <div>
                    <h3 class="form-title">Form Google</h3>
                    <p class="form-description">Formulir Google Forms untuk pengajuan layanan dan permintaan lainnya</p>
                  </div>
                </div>
              </VCardTitle>
              
              <VCardText class="form-card-content">
                <div class="google-form-content">
                  <div class="google-form-info">
                    <VIcon icon="ri-information-line" size="48" color="#f59e0b" class="mb-4" />
                    <h4 class="info-title">Google Forms Integration</h4>
                    <p class="info-description">
                      Klik tombol di bawah ini untuk mengakses formulir Google Forms resmi kami. 
                      Formulir akan terbuka di tab baru dengan semua layanan yang tersedia.
                    </p>
                    
                    <div class="features-list">
                      <div class="feature-item">
                        <VIcon icon="ri-checkbox-circle-line" size="20" color="#10b981" />
                        <span>Google Forms Integration</span>
                      </div>
                      <div class="feature-item">
                        <VIcon icon="ri-checkbox-circle-line" size="20" color="#10b981" />
                        <span>Easy Submission</span>
                      </div>
                      <div class="feature-item">
                        <VIcon icon="ri-checkbox-circle-line" size="20" color="#10b981" />
                        <span>Cloud Based</span>
                      </div>
                      <div class="feature-item">
                        <VIcon icon="ri-checkbox-circle-line" size="20" color="#10b981" />
                        <span>Mobile Friendly</span>
                      </div>
                    </div>
                  </div>
                  
                  <div class="google-form-action">
                    <VBtn
                      @click="openGoogleForm"
                      color="#f59e0b"
                      size="large"
                      variant="elevated"
                      class="google-form-btn"
                      prepend-icon="ri-external-link-line"
                    >
                      Buka Google Forms
                    </VBtn>
                    <p class="form-note">
                      <VIcon icon="ri-information-line" size="16" />
                      Link akan membuka Google Forms di tab baru
                    </p>
                  </div>
                </div>
              </VCardText>
            </VCard>
          </VWindowItem>
        </VWindow>

        <!-- Navigation Buttons -->
        <div class="tab-navigation">
          <VBtn
            @click="currentTab = (currentTab - 1 + 3) % 3"
            :disabled="currentTab === 0"
            variant="outlined"
            color="#2563eb"
            prepend-icon="ri-arrow-left-line"
            class="nav-btn"
          >
            Previous
          </VBtn>
          
          <div class="tab-indicators">
            <div
              v-for="tab in formTabs"
              :key="tab.id"
              class="tab-indicator"
              :class="{ active: currentTab === tab.id }"
              @click="currentTab = tab.id"
            />
          </div>
          
          <VBtn
            @click="currentTab = (currentTab + 1) % 3"
            :disabled="currentTab === 2"
            variant="outlined"
            color="#2563eb"
            append-icon="ri-arrow-right-line"
            class="nav-btn"
          >
            Next
          </VBtn>
        </div>
      </div>
    </section>

    <!-- Info Section -->
    <section class="info-section">
      <div class="info-container">
        <div class="info-content">
          <h2 class="info-title">Mengapa Memilih Layanan Kami?</h2>
          <div class="info-features">
            <div class="info-feature">
              <div class="info-icon">
                <VIcon icon="ri-lock-2-line" size="24" />
              </div>
              <div class="info-text">
                <h4>Keamanan Terjamin</h4>
                <p>Enkripsi data dan akses yang aman untuk melindungi informasi Anda</p>
              </div>
            </div>
            <div class="info-feature">
              <div class="info-icon">
                <VIcon icon="ri-speed-line" size="24" />
              </div>
              <div class="info-text">
                <h4>Cepat & Efisien</h4>
                <p>Proses pengajuan yang cepat dan sistem yang responsif</p>
              </div>
            </div>
            <div class="info-feature">
              <div class="info-icon">
                <VIcon icon="ri-customer-service-2-line" size="24" />
              </div>
              <div class="info-text">
                <h4>Dukungan 24/7</h4>
                <p>Tim support siap membantu Anda kapan saja dibutuhkan</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="landing-footer">
      <div class="footer-content">
        <div class="footer-text">
          <p>&copy; TIM DEV</p>
        </div>
        <div class="footer-links">
          <VBtn variant="text" size="small">Bantuan</VBtn>
          <VBtn variant="text" size="small">Kebijakan</VBtn>
          <VBtn variant="text" size="small">Kontak</VBtn>
        </div>
      </div>
    </footer>
  </div>
</template>

<style scoped>
.landing-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
  position: relative;
  overflow-x: hidden;
}

.landing-header {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1000;
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid rgba(255, 255, 255, 0.2);
  transition: background 0.3s ease, border-bottom 0.3s ease;
}

/* Header dengan background putih transparan */
.landing-header.transparent {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid rgba(255, 255, 255, 0.2);
}

/* Header dengan background biru bold */
.landing-header.blue-bold {
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid rgba(255, 255, 255, 0.3);
}

.header-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 0;
}

.logo-section {
  display: flex;
  align-items: center;
}

.logo-text {
  font-size: 1.5rem;
  font-weight: bold;
  color: white;
  margin: 0;
  text-decoration: none;
}

.nav-menu {
  display: flex;
  align-items: center;
  gap: 2rem;
}

.notification-wrapper {
  display: flex;
  align-items: center;
}

.nav-link {
  color: white;
  text-decoration: none;
  font-weight: 500;
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  transition: all 0.3s ease;
}

.nav-link:hover {
  background: rgba(255, 255, 255, 0.2);
  transform: translateY(-2px);
}

.nav-login {
  background: rgba(255, 255, 255, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.3);
}

.nav-login:hover {
  background: rgba(255, 255, 255, 0.3);
}

.dark-mode .landing-header {
  background: rgba(0, 0, 0, 0.3);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.theme-toggle-container {
  position: fixed;
  top: 80px;
  right: 20px;
  z-index: 1001;
}

.theme-toggle-btn {
  width: 48px !important;
  height: 48px !important;
  border-radius: 50% !important;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
  transition: all 0.3s ease;
}

.theme-toggle-btn:hover {
  transform: scale(1.1);
  box-shadow: 0 6px 30px rgba(0, 0, 0, 0.3);
}

/* Hero Section */
.hero-section {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 6rem 2rem 2rem;
  text-align: center;
  position: relative;
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
}

.hero-content {
  text-align: center;
  max-width: 800px;
  z-index: 2;
  position: relative;
}

.hero-logo {
  margin-bottom: 3rem;
  display: flex;
  justify-content: center;
  align-items: center;
}

.hero-logo-text {
  margin-bottom: 3rem;
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 0.5rem;
}

.logo-white {
  width: 60px;
  height: 60px;
  object-fit: contain;
  filter: drop-shadow(0 4px 20px rgba(0, 0, 0, 0.3));
  transition: transform 0.3s ease, filter 0.3s ease;
}

.logo-white:hover {
  transform: scale(1.05);
  filter: drop-shadow(0 6px 30px rgba(0, 0, 0, 0.4));
}

.hero-logo-text h1 {
  font-size: 3rem;
  font-weight: 800;
  color: white;
  text-align: center;
  margin: 0;
  text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
  letter-spacing: 2px;
  transition: transform 0.3s ease, text-shadow 0.3s ease;
}

.hero-logo-text h1:hover {
  transform: scale(1.05);
  text-shadow: 0 6px 30px rgba(0, 0, 0, 0.4);
}

.logo-image {
  max-width: 200px;
  height: auto;
  filter: drop-shadow(0 4px 20px rgba(0, 0, 0, 0.1));
  transition: transform 0.3s ease, filter 0.3s ease;
}

.logo-image:hover {
  transform: scale(1.05);
  filter: drop-shadow(0 8px 30px rgba(0, 0, 0, 0.15));
}

.hero-text {
  margin-bottom: 4rem;
}

.hero-title {
  font-size: 3.5rem;
  font-weight: 700;
  color: white;
  margin-bottom: 1rem;
  line-height: 1.2;
  text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
  transition: color 0.3s ease;
}

.hero-subtitle {
  font-size: 1.25rem;
  color: rgba(255, 255, 255, 0.9);
  margin-bottom: 3rem;
  line-height: 1.6;
  text-shadow: 0 1px 5px rgba(0, 0, 0, 0.2);
  transition: color 0.3s ease;
}

/* Ensure landing wording is white when background is blue */
.hero-section .hero-title,
.hero-section .hero-subtitle,
.hero-section .hero-logo-text h1 {
  color: white;
}

.dark-mode .hero-title,
.dark-mode .hero-subtitle {
  color: white;
  text-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
}

/* Stats */
.stats-container {
  display: flex;
  gap: 2rem;
  justify-content: center;
  flex-wrap: wrap;
  animation: fadeInUp 0.8s ease-out 0.4s both;
}

.stat-card {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 16px;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  min-width: 200px;
  transition: transform 0.3s ease, background 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-4px);
  background: rgba(255, 255, 255, 0.15);
}

.stat-icon {
  background: rgba(255, 255, 255, 0.2);
  border-radius: 12px;
  padding: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: white;
}

.stat-label {
  font-size: 0.875rem;
  color: rgba(255, 255, 255, 0.8);
}

/* Hero Decoration */
.hero-decoration {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 100%;
  height: 100%;
  pointer-events: none;
}

.floating-card {
  position: absolute;
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 16px;
  padding: 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.card-1 {
  top: 20%;
  right: 10%;
  animation: float 6s ease-in-out infinite;
}

.card-2 {
  top: 60%;
  left: 10%;
  animation: float 6s ease-in-out infinite 2s;
}

.card-3 {
  bottom: 20%;
  right: 15%;
  animation: float 6s ease-in-out infinite 4s;
}

/* Forms Section */
.forms-section {
  padding: 4rem 2rem;
  background: #f8fafc;
  position: relative;
  transition: background 0.3s ease;
}

.dark-mode .forms-section {
  background: #0f172a;
}

.section-header {
  text-align: center;
  margin-bottom: 3rem;
}

.section-title {
  font-size: 2.5rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 1rem;
  transition: color 0.3s ease;
}

.section-subtitle {
  font-size: 1.125rem;
  color: #64748b;
  max-width: 600px;
  margin: 0 auto;
  transition: color 0.3s ease;
}

.dark-mode .section-title {
  color: white;
}

.dark-mode .section-subtitle {
  color: rgba(255, 255, 255, 0.8);
}

.forms-tabs-container {
  max-width: 1200px;
  margin: 0 auto;
  position: relative;
}

.form-tabs {
  margin-bottom: 2rem;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  overflow: hidden;
}

.form-tab {
  text-transform: none;
  font-weight: 600;
  letter-spacing: 0.5px;
  white-space: nowrap;
  flex: 1;
  text-align: center;
  min-width: 0;
  border-radius: 8px;
  transition: all 0.3s ease;
}

.form-window {
  border-radius: 16px;
  overflow: hidden;
  background: transparent;
  min-height: 800px;
}

.tab-navigation {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 2rem;
  padding: 0 1rem;
}

.nav-btn {
  border-radius: 12px;
  height: 48px;
  font-weight: 600;
  text-transform: none;
  letter-spacing: 0.5px;
  transition: all 0.3s ease;
}

.nav-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);
}

.tab-indicators {
  display: flex;
  gap: 0.5rem;
}

.tab-indicator {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: #e2e8f0;
  cursor: pointer;
  transition: all 0.3s ease;
}

.tab-indicator.active {
  background: #2563eb;
  transform: scale(1.2);
}

.tab-indicator:hover {
  background: #94a3b8;
}

.tab-indicator.active:hover {
  background: #2563eb;
}

.form-card-vertical {
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  overflow: hidden;
  transition: transform 0.3s ease, box-shadow 0.3s ease, background 0.3s ease;
}

.dark-mode .form-card-vertical {
  background: #1e293b;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

.dark-mode .form-card-vertical * {
  color: white !important;
}

.dark-mode .form-card-vertical .v-card-title,
.dark-mode .form-card-vertical .v-card-subtitle,
.dark-mode .form-card-vertical .v-label,
.dark-mode .form-card-vertical .v-field__input,
.dark-mode .form-card-vertical .v-select__selection-text,
.dark-mode .form-card-vertical .v-radio__label,
.dark-mode .form-card-vertical .v-checkbox__label {
  color: white !important;
}

.dark-mode .form-card-vertical .v-field--variant-outlined .v-field__outline {
  color: rgba(255, 255, 255, 0.3) !important;
}

.dark-mode .form-card-vertical .v-card {
  background: #1e293b !important;
}

.dark-mode .form-card-vertical .v-card--variant-outlined {
  background: #1e293b !important;
}

.dark-mode .form-card-vertical .v-card-text {
  background: #1e293b !important;
}

/* Dark mode for nested cards in forms */
.dark-mode .v-card {
  background: #1e293b !important;
  color: white !important;
}

.dark-mode .v-card .v-card-title {
  color: white !important;
}

.dark-mode .v-card .v-card-subtitle {
  color: rgba(255, 255, 255, 0.8) !important;
}

.dark-mode .v-card .v-label {
  color: white !important;
}

.dark-mode .v-field--variant-outlined {
  background: #1e293b !important;
}

.dark-mode .v-field--variant-outlined .v-field__input {
  color: white !important;
}

.dark-mode .v-select .v-field__input {
  color: white !important;
}

.dark-mode .v-radio .v-radio__label {
  color: white !important;
}

.dark-mode .v-checkbox .v-checkbox__label {
  color: white !important;
}

.form-card-vertical:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
}

.form-card-header {
  padding: 1.5rem 2rem;
  background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
  border-bottom: 1px solid #e2e8f0;
  transition: background 0.3s ease;
}

.dark-mode .form-card-header {
  background: linear-gradient(135deg, #334155 0%, #475569 100%);
  border-bottom: 1px solid #475569;
}

.form-header-content {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.form-icon-container {
  width: 64px;
  height: 64px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
  flex-shrink: 0;
}

.form-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 0.5rem;
  line-height: 1.2;
  transition: color 0.3s ease;
}

.form-description {
  color: #64748b;
  line-height: 1.5;
  margin: 0;
  transition: color 0.3s ease;
}

.dark-mode .form-title {
  color: white;
}

.dark-mode .form-description {
  color: rgba(255, 255, 255, 0.8);
}

.form-card-content {
  padding: 2rem;
}

.section-subtitle {
  font-size: 1.125rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 1.5rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.section-subtitle::before {
  content: '';
  width: 4px;
  height: 20px;
  background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
  border-radius: 2px;
}

.submit-btn {
  width: 100%;
  height: 56px;
  font-weight: 600;
  text-transform: none;
  letter-spacing: 0.5px;
  border-radius: 12px;
  margin-top: 1rem;
}

/* Google Form Specific Styles */
.google-form-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding: 2rem;
}

.google-form-info {
  margin-bottom: 2rem;
}

.info-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 1rem;
}

.info-description {
  color: #64748b;
  line-height: 1.6;
  max-width: 500px;
  margin: 0 auto 2rem;
}

.features-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  max-width: 300px;
  margin: 0 auto;
}

.feature-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  color: #475569;
  font-size: 0.9rem;
  font-weight: 500;
}

.google-form-action {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
}

.google-form-btn {
  width: auto;
  min-width: 200px;
  height: 56px;
  font-weight: 600;
  text-transform: none;
  letter-spacing: 0.5px;
  border-radius: 12px;
  padding: 0 2rem;
}

.form-note {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #64748b;
  font-size: 0.875rem;
  margin: 0;
}

/* Info Section */
.info-section {
  padding: 4rem 2rem;
  background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
}

.info-container {
  max-width: 1200px;
  margin: 0 auto;
}

.info-title {
  font-size: 2rem;
  font-weight: 700;
  color: white;
  text-align: center;
  margin-bottom: 3rem;
}

.info-features {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 2rem;
}

.info-feature {
  display: flex;
  gap: 1rem;
  align-items: flex-start;
}

.info-icon {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  flex-shrink: 0;
}

.info-text h4 {
  color: white;
  font-size: 1.125rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.info-text p {
  color: rgba(255, 255, 255, 0.8);
  line-height: 1.6;
}

/* Footer */
.landing-footer {
  background: #0f172a;
  padding: 2rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.footer-content {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
}

.footer-text {
  color: rgba(255, 255, 255, 0.8);
}

.footer-links {
  display: flex;
  gap: 1rem;
}

/* Animations */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes float {
  0%, 100% {
    transform: translateY(0px);
  }
  50% {
    transform: translateY(-20px);
  }
}

/* Responsive Design */
@media (max-width: 768px) {
  .hero-logo {
    margin-bottom: 2rem;
  }
  
  .nav-menu {
    gap: 1rem;
  }
  
  .hero-logo-text {
    margin-bottom: 2rem;
    gap: 0.4rem;
  }
  
  .logo-white {
    width: 50px;
    height: 50px;
  }
  
  .hero-logo-text h1 {
    font-size: 2.5rem;
  }
  
  .logo-image {
    max-width: 150px;
  }
  
  .hero-title {
    font-size: 2.5rem;
  }
  
  .hero-subtitle {
    font-size: 1rem;
  }
  
  .stats-container {
    gap: 1rem;
  }
  
  .stat-card {
    min-width: 150px;
    padding: 1rem;
  }
  
  .forms-tabs-container {
    padding: 0 1rem;
  }
  
  .form-tabs {
    margin-bottom: 1.5rem;
    padding: 0.25rem;
  }
  
  .form-tab {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    white-space: nowrap;
    flex: 1;
    text-align: center;
    min-width: 0;
    border-radius: 6px;
    transition: all 0.3s ease;
  }
  
  .form-card-header {
    padding: 1.25rem 1.5rem;
  }
  
  .form-header-content {
    flex-direction: column;
    text-align: center;
    gap: 1rem;
  }
  
  .form-card-content {
    padding: 1.5rem;
  }
  
  .tab-navigation {
    padding: 0 0.5rem;
    gap: 1rem;
  }
  
  .nav-btn {
    height: 40px;
    font-size: 0.875rem;
    padding: 0 1rem;
  }
  
  .form-window {
    min-height: 700px;
  }
  
  .section-title {
    font-size: 2rem;
  }
  
  .info-features {
    grid-template-columns: 1fr;
  }
  
  .footer-content {
    flex-direction: column;
    text-align: center;
  }
}

@media (max-width: 480px) {
  .hero-section {
    padding: 1rem;
  }
  
  .hero-logo {
    margin-bottom: 1.5rem;
  }
  
  .hero-logo-text {
    margin-bottom: 1.5rem;
    gap: 0.25rem;
  }
  
  .logo-white {
    width: 40px;
    height: 40px;
  }
  
  .hero-logo-text h1 {
    font-size: 2rem;
    letter-spacing: 1px;
  }
  
  .logo-image {
    max-width: 120px;
  }
  
  .forms-section {
    padding: 2rem 1rem;
  }
  
  .forms-tabs-container {
    padding: 0 0.5rem;
  }
  
  .form-tabs {
    margin-bottom: 1rem;
    padding: 0.25rem;
  }
  
  .form-tab {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
  }
  
  .form-tab .v-icon {
    font-size: 16px !important;
  }
  
  .form-card-header {
    padding: 1rem;
  }
  
  .form-card-content {
    padding: 1rem;
  }
  
  .form-icon-container {
    width: 56px;
    height: 56px;
  }
  
  .form-title {
    font-size: 1.25rem;
  }
  
  .form-description {
    font-size: 0.875rem;
  }
  
  .google-form-content {
    padding: 1.5rem;
  }
  
  .info-section {
    padding: 2rem 1rem;
  }
  
  .submit-btn {
    height: 48px;
  }
  
  .google-form-btn {
    height: 48px;
    min-width: 180px;
  }
  
  .tab-navigation {
    flex-direction: column;
    gap: 1rem;
    padding: 1rem 0.5rem;
  }
  
  .tab-indicators {
    order: -1;
  }
  
  .nav-btn {
    width: 100%;
    height: 40px;
  }
  
  .form-window {
    min-height: 600px;
  }
}
</style>
