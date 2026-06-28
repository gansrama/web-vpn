<script lang="ts" setup>
import { ref, shallowRef, computed, onMounted, watch, nextTick } from 'vue'
import { useTheme } from 'vuetify'
import { useNotifications } from '@/composables/useNotifications'
import type { Employee, VpnServer, AksesLogicFormData } from '@/types'
import SignaturePad from '@/components/SignaturePad.vue'

const theme = useTheme()
const { addFormSubmissionNotification } = useNotifications()

// Form data dengan ref untuk reactivity yang lebih baik
const formData = ref<AksesLogicFormData>({
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
  accessLevel: 'clientless', // Default value
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
  if (formData.value.vpnServer && formData.value.accessLevel) {
    const selectedServer = vpnServers.value.find(server => server.nama_sistem === formData.value.vpnServer)
    const ipAddress = selectedServer?.ip_address || ''
    
    // Check if IP address already exists in the list
    const ipExists = vpnServersList.value.some(server => server.ip_address === ipAddress)
    if (ipExists) {
      alert('IP Address ini sudah ditambahkan. Silakan pilih server dengan IP yang berbeda.')
      return
    }
    
    vpnServersList.value.push({
      id: nextVpnServerId++,
      nama_sistem: formData.value.vpnServer,
      ip_address: ipAddress,
      jenis_akses: formData.value.accessLevel
    })
    // Reset VPN server fields for next entry
    formData.value.vpnServer = ''
    formData.value.deviceType = ''
    formData.value.accessLevel = 'clientless'
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

const resetForm = () => {
  console.log('Resetting form...')
  formData.value.employeeId = ''
  formData.value.email = ''
  formData.value.position = ''
  formData.value.department = ''
  formData.value.manager = ''
  formData.value.accessLevel = 'clientless' // Reset to default
  formData.value.vpnServer = ''
  formData.value.duration = ''
  formData.value.reason = ''
  formData.value.deviceType = ''
  formData.value.accessType = ''
  formData.value.agreeToTerms = false
  formData.value.agreeToPolicy = false
  formData.value.signature_image = undefined
  vpnServersList.value = []
  nextVpnServerId = 1
  console.log('Form reset complete')
}

// API Data
const employees = ref<Employee[]>([])
const vpnServers = ref<VpnServer[]>([])
const loading = ref(false)
const isEditMode = ref(false)
const editId = ref<number | null>(null)

// Static data dengan shallowRef untuk performance
const vpnRequirements = shallowRef([
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

// Access Levels dengan shallowRef
const accessLevels = shallowRef([
  { title: 'SSL VPN', value: 'clientless' },
  { title: 'Endpoint/Client-Based VPN', value: 'client' },
  { title: 'IPsec VPN ', value: 'ipsec' }
])

// Device Types dengan shallowRef
const deviceTypes = shallowRef([
  { title: 'Laptop', value: 'laptop' },
  { title: 'Desktop PC', value: 'desktop' },
  { title: 'Mobile Phone', value: 'mobile' },
  { title: 'Tablet', value: 'tablet' }
])

// Durations dengan shallowRef
const durations = shallowRef([
  { title: 'Januari s.d Maret', value: 'q1' },
  { title: 'April s.d Juni', value: 'q2' },
  { title: 'Juli s.d September', value: 'q3' },
  { title: 'Oktober s.d Desember', value: 'q4' }
])

// Fetch employees dari API
const fetchEmployees = async () => {
  try {
    loading.value = true
    console.log('Fetching employees...')
    const response = await fetch('/api/employees?per_page=1000')
    const data = await response.json()
    
    console.log('Employees response:', data)
    
    if (data.success) {
      employees.value = data.data
      console.log('Employees loaded:', employees.value.length)
      console.log('Employee data structure:', employees.value[0])
      console.log('Available employees for dropdown:', employees.value.map(emp => ({
        id: emp.id,
        nama_lengkap: emp.nama_lengkap,
        email: emp.email,
        posisi_jabatan: emp.posisi_jabatan,
        nama_organisasi: emp.nama_organisasi
      })))
    } else {
      console.error('Failed to fetch employees:', data.message)
    }
  } catch (error) {
    console.error('Error fetching employees:', error)
  } finally {
    loading.value = false
  }
}

// Fetch VPN servers dari API
const fetchVpnServers = async () => {
  try {
    console.log('Fetching VPN servers...')
    const response = await fetch('/api/vpn-servers')
    const data = await response.json()
    
    console.log('VPN servers response:', data)
    
    if (data.success) {
      // Remove duplicates based on nama_sistem and ensure unique entries
      const uniqueServers = data.data.reduce((acc: VpnServer[], server: VpnServer) => {
        const existingIndex = acc.findIndex(s => s.nama_sistem === server.nama_sistem)
        if (existingIndex === -1) {
          acc.push(server)
        } else {
          console.warn('Duplicate VPN server found:', server.nama_sistem)
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
      console.log('VPN servers loaded:', vpnServers.value.length)
    } else {
      console.error('Failed to fetch VPN servers:', data.message)
    }
  } catch (error) {
    console.error('Error fetching VPN servers:', error)
  }
}

// Watch untuk employee selection
watch(() => formData.value.employeeId, (newEmployeeId) => {
  console.log('Employee ID changed:', newEmployeeId, 'Type:', typeof newEmployeeId)
  if (newEmployeeId) {
    const selectedEmployee = employees.value.find(emp => emp.id === Number(newEmployeeId))
    console.log('Selected employee:', selectedEmployee)
    
    if (selectedEmployee) {
      // Auto-fill employee data
      formData.value.nama_lengkap = selectedEmployee.nama_lengkap
      formData.value.nomor_ktp = selectedEmployee.nomor_ktp
      formData.value.email = selectedEmployee.email
      formData.value.position = selectedEmployee.posisi_jabatan
      formData.value.department = selectedEmployee.nama_organisasi
      formData.value.manager = selectedEmployee.nomer_hp_wa
      
      console.log('Form data updated:', {
        nama_lengkap: formData.value.nama_lengkap,
        nomor_ktp: formData.value.nomor_ktp,
        email: formData.value.email,
        position: formData.value.position,
        department: formData.value.department,
        manager: formData.value.manager
      })
    }
  }
})

// Watch untuk VPN server selection
watch(() => formData.value.vpnServer, (newVpnServer) => {
  console.log('VPN Server changed:', newVpnServer, 'Type:', typeof newVpnServer)
  if (newVpnServer) {
    const selectedServer = vpnServers.value.find(server => server.nama_sistem === newVpnServer)
    console.log('Selected VPN server:', selectedServer)
    
    if (selectedServer) {
      // Auto-fill IP Address when VPN server is selected
      formData.value.deviceType = selectedServer.ip_address
      
      console.log('VPN server data updated:', {
        vpnServer: formData.value.vpnServer,
        deviceType: formData.value.deviceType
      })
    }
  } else {
    formData.value.deviceType = ''
    formData.value = { ...formData.value }
  }
}, { immediate: false })


// Computed properties untuk optimasi
const isFormValid = computed(() => {
  const validationState = {
    employeeId: !!formData.value.employeeId,
    email: !!formData.value.email,
    position: !!formData.value.position,
    department: !!formData.value.department,
    manager: !!formData.value.manager,
    duration: !!formData.value.duration,
    reason: !!formData.value.reason,
    accessType: !!formData.value.accessType,
    signature_image: !!formData.value.signature_image,
    agreeToTerms: !!formData.value.agreeToTerms,
    agreeToPolicy: !!formData.value.agreeToPolicy,
    hasVpnServers: vpnServersList.value.length > 0
  }
  
  const valid = validationState.employeeId && 
         validationState.email && 
         validationState.position && 
         validationState.department && 
         validationState.manager && 
         validationState.duration && 
         validationState.reason && 
         validationState.accessType &&
         validationState.signature_image &&
         validationState.agreeToTerms && 
         validationState.agreeToPolicy &&
         validationState.hasVpnServers
  
  console.log('Form validation state:', validationState)
  console.log('Form validation result:', valid)
  console.log('Form data for validation:', {
    employeeId: formData.value.employeeId,
    email: formData.value.email,
    position: formData.value.position,
    department: formData.value.department,
    manager: formData.value.manager,
    duration: formData.value.duration,
    reason: formData.value.reason,
    accessType: formData.value.accessType,
    agreeToTerms: formData.value.agreeToTerms,
    agreeToPolicy: formData.value.agreeToPolicy,
    vpnServersList: vpnServersList.value,
    isValid: valid
  })
  
  return valid
})

const submitForm = async () => {
  console.log('=== Submit Form Started ===')
  console.log('Edit Mode:', isEditMode.value)
  console.log('Edit ID:', editId.value)
  
  if (!isFormValid.value) {
    console.warn('Form is not valid')
    console.log('Form data:', formData.value)
    console.log('Validation state:', isFormValid.value)
    return
  }
  
  console.log('Form is valid, submitting...')
  console.log('Form data:', formData.value)
  
  try {
    // Show loading state
    const submitButton = document.querySelector('button[type="submit"]') as HTMLButtonElement
    if (submitButton) {
      console.log('Disabling submit button...')
      submitButton.disabled = true
      submitButton.innerHTML = '<span class="v-btn__loading"><span class="v-icon" aria-hidden="true"><svg viewBox="0 0 24 24" height="18" width="18"><path d="M12,4V2A10,10 0 0,0 2,12A10,10 0 0,0 12,22C14.7,22 16.1,20.9 17.6,19.4L16.2,20.9C14.3,22.8 11.7,22.8 9.8,20.9L8.4,19.4C9.9,20.9 11.3,22 12,22A10,10 0 0,0 2,12A10,10 0 0,0 12,2M12,6A6,6 0 0,1 18,12A6,6 0 0,1 6,12A6,6 0 0,1 6,12M12,8A4,4 0 0,0 8,12A4,4 0 0,0 12,16A4,4 0 0,0 16,12A4,4 0 0,0 12,8Z"></path></svg></span></span> Submitting...'
    }
    
    // Prepare form data for API
    const selectedEmployee = employees.value.find((emp: Employee) => emp.id === Number(formData.value.employeeId))
    
    // Debug: Check if employee is found and form data is populated
    console.log('=== DEBUG: Employee Selection ===')
    console.log('formData.employeeId:', formData.value.employeeId)
    console.log('selectedEmployee:', selectedEmployee)
    console.log('employees available:', employees.value.length)
    console.log('=== END DEBUG ===')
    
    const formDataToSubmit: any = {
      nama_lengkap: selectedEmployee?.nama_lengkap || formData.value.nama_lengkap,
      nomor_ktp: selectedEmployee?.nomor_ktp || formData.value.nomor_ktp,
      email: selectedEmployee?.email || formData.value.email,
      posisi_jabatan: selectedEmployee?.posisi_jabatan || formData.value.position,
      nama_organisasi: selectedEmployee?.nama_organisasi || formData.value.department,
      nomer_hp_wa: selectedEmployee?.nomer_hp_wa || formData.value.manager,
      masa_berlaku: String(formData.value.duration || ''),
      keperluan_vpn: String(formData.value.reason || ''),
      pengguna_hak_akses: String(formData.value.accessType || ''),
      sudah_menandatangani_surat_pernyataan: !!formData.value.agreeToTerms,
      memahami_kebijakan_keamanan: !!formData.value.agreeToPolicy,
      posisi_pemohon: selectedEmployee?.posisi_pemohon || `${selectedEmployee?.nama_organisasi || 'Jakarta'}, ${new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })}`,
      vpn_servers: vpnServersList.value
    }
    
    // Debug: Log the VPN server values
    console.log('=== DEBUG: VPN Server Values ===')
    console.log('formData.vpnServer:', formData.value.vpnServer)
    console.log('formDataToSubmit.nama_sistem:', formDataToSubmit.nama_sistem)
    console.log('Available VPN servers:', vpnServers.value.map(s => s.nama_sistem))
    console.log('=== END DEBUG ===')
    
    // Add signature image if it exists (base64 string or file)
    if (formData.value.signature_image) {
      console.log('Adding signature image to form data...')
      formDataToSubmit.signature_image = formData.value.signature_image
    }
    
    console.log('Form data to submit:', formDataToSubmit)
    
    // Check for empty fields before sending
    const requiredFields = [
      'nama_lengkap',
      'nomor_ktp',
      'email',
      'posisi_jabatan',
      'nama_organisasi',
      'nomer_hp_wa',
      'nama_sistem',
      'jenis_akses',
      'masa_berlaku',
      'keperluan_vpn',
      'pengguna_hak_akses',
      'sudah_menandatangani_surat_pernyataan',
      'memahami_kebijakan_keamanan'
    ]
    
    const emptyFields = requiredFields.filter(field => {
      const value = (formDataToSubmit as any)[field]
      if (typeof value === 'boolean') return false
      if (typeof value === 'number') return value <= 0
      return !value || String(value).trim() === ''
    })
    
    // Check if duration is valid and required
    const validDurations = ['q1', 'q2', 'q3', 'q4']
    const durationValid = formData.value.duration && validDurations.includes(formData.value.duration)
    
    if (!durationValid) {
      console.error('Invalid or missing duration:', formData.value.duration)
      alert('Silakan pilih durasi yang valid: Januari-Maret, April-Juni, Juli-September, atau Oktober-Desember')
      return
    }
    
    // Check if access type is valid and required
    const validAccessTypes = ['asn', 'non-asn']
    const accessTypeValid = formData.value.accessType && validAccessTypes.includes(formData.value.accessType)
    
    if (!accessTypeValid) {
      console.error('Invalid or missing access type:', formData.value.accessType)
      alert('Silakan pilih tipe akses: ASN atau Non ASN')
      return
    }
    
    // Check if access level is valid and required
    const validAccessLevels = ['clientless', 'client', 'ipsec']
    const accessLevelValid = formData.value.accessLevel && validAccessLevels.includes(formData.value.accessLevel)
    
    if (!accessLevelValid) {
      console.error('Invalid or missing access level:', formData.value.accessLevel)
      alert('Silakan pilih level akses: SSL VPN, Endpoint/Client-Based VPN, atau IPsec VPN')
      return
    }
    
    // Check if at least one VPN server is added
    if (vpnServersList.value.length === 0) {
      console.error('No VPN servers added:', vpnServersList.value)
      alert('Silakan tambahkan minimal satu server VPN')
      return
    }
    
    if (emptyFields.length > 0) {
      console.error('Empty required fields:', emptyFields)
      console.error('Form data to submit:', formDataToSubmit)
      alert('Please fill all required fields. Missing: ' + emptyFields.join(', '))
      return
    }
    
    // Check checkbox values
    if (!formData.value.agreeToTerms) {
      console.error('Terms not agreed:', formData.value.agreeToTerms)
      alert('Please agree to the terms and conditions')
      return
    }
    
    if (!formData.value.agreeToPolicy) {
      console.error('Policy not agreed:', formData.value.agreeToPolicy)
      alert('Please agree to the policy')
      return
    }
    
    // Check CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    console.log('CSRF Token:', csrfToken ? 'Found' : 'Not found')
    
    // Prepare request headers with proper typing
    const headers: Record<string, string> = {
      'Accept': 'application/json'
    }
    
    if (csrfToken) {
      headers['X-CSRF-TOKEN'] = csrfToken
    }
    
    console.log('Request headers:', headers)
    
    // Determine API endpoint and method based on edit mode
    const apiUrl = isEditMode.value ? `/api/akses-logic-requests/${editId.value}` : '/api/akses-logic-requests'
    const apiMethod = isEditMode.value ? 'PUT' : 'POST'
    
    console.log('Request URL:', apiUrl)
    console.log('Request Method:', apiMethod)
    
    // Send to API
    console.log('Sending request...')
    console.log('Request body:', JSON.stringify(formDataToSubmit, null, 2))
    
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
    if (formData.value.signature_image) {
      console.log('Adding signature image to FormData...')
      if (formData.value.signature_image instanceof File) {
        submitData.append('signature_image', formData.value.signature_image)
      } else if (typeof formData.value.signature_image === 'string') {
        // Convert base64 to blob for FormData
        const base64String = formData.value.signature_image as string
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
    
    // Log FormData contents (for debugging)
    console.log('FormData contents:')
    for (let [key, value] of submitData.entries()) {
      console.log(`${key}:`, value instanceof File ? `File(${value.name}, ${value.size} bytes)` : value)
    }
    
    // Additional debugging - log the exact values being sent
    console.log('=== DEBUG: Form Data Values ===')
    console.log('nama_lengkap:', formDataToSubmit.nama_lengkap)
    console.log('nomor_ktp:', formDataToSubmit.nomor_ktp)
    console.log('email:', formDataToSubmit.email)
    console.log('posisi_jabatan:', formDataToSubmit.posisi_jabatan)
    console.log('nama_organisasi:', formDataToSubmit.nama_organisasi)
    console.log('nomer_hp_wa:', formDataToSubmit.nomer_hp_wa)
    console.log('nama_sistem:', formDataToSubmit.nama_sistem)
    console.log('jenis_akses:', formDataToSubmit.jenis_akses)
    console.log('masa_berlaku:', formDataToSubmit.masa_berlaku)
    console.log('keperluan_vpn:', formDataToSubmit.keperluan_vpn)
    console.log('pengguna_hak_akses:', formDataToSubmit.pengguna_hak_akses)
    console.log('sudah_menandatangani_surat_pernyataan:', formDataToSubmit.sudah_menandatangani_surat_pernyataan)
    console.log('memahami_kebijakan_keamanan:', formDataToSubmit.memahami_kebijakan_keamanan)
    console.log('=== END DEBUG ===')
    
    let response
    try {
      response = await fetch(apiUrl, {
        method: apiMethod,
        headers: {
          // Don't set Content-Type for FormData - browser will set it with boundary
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: submitData
      })
    } catch (fetchError) {
      console.error('Fetch error:', fetchError)
      const errorMessage = fetchError instanceof Error ? fetchError.message : 'Unknown network error'
      throw new Error('Network error: ' + errorMessage)
    }
    
    console.log('Response received:')
    console.log('Status:', response.status)
    console.log('Status text:', response.statusText)
    console.log('Headers:', response.headers)
    
    // Parse response
    let result
    try {
      const responseText = await response.text()
      console.log('Raw response text:', responseText)
      
      if (!responseText) {
        throw new Error('Empty response from server')
      }
      
      result = JSON.parse(responseText)
      console.log('Parsed response:', result)
    } catch (parseError) {
      console.error('Error parsing response:', parseError)
      console.log('Response status:', response.status)
      console.log('Response headers:', Object.fromEntries(response.headers.entries()))
      const errorMessage = parseError instanceof Error ? parseError.message : 'Unknown parse error'
      throw new Error('Invalid response from server: ' + errorMessage)
    }
    
    if (result.success) {
      // Show success popup
      console.log('Submit successful!')
      const requestId = result.data?.id || result.id || 'Unknown'
      const actionText = isEditMode.value ? 'diupdate' : 'disubmit'
      const email = isEditMode.value ? (selectedEmployee?.email || formData.value.email) : formData.value.email
      
      // Add notification
      const requestIdStr = String(requestId)
      const dbId = result.data?.id || result.id || requestId
      console.log('Adding notification:', { formType: 'akses-logic', email, requestIdStr, dbId, actionText })
      addFormSubmissionNotification('akses-logic', email, requestIdStr, actionText)
      
      showSuccessPopup(requestId, email, 'pending', actionText)
      
      // Reset form
      console.log('Resetting form...')
      resetForm()
      
    } else {
      // Show error message
      console.error('Submit failed:', result)
      
      // Handle specific error cases with user-friendly messages
      let userMessage = result.message || 'Terjadi kesalahan saat submit form'
      
      // Show validation errors if they exist
      if (result.errors) {
        console.error('Validation errors:', result.errors)
        const errorMessages = Object.entries(result.errors).map(([field, messages]) => {
          return `${field}: ${Array.isArray(messages) ? messages.join(', ') : messages}`
        })
        userMessage = `Validation Error: ${errorMessages.join('; ')}`
      }
      
      showErrorPopup(userMessage)
    }
    
  } catch (error: unknown) {
    console.error('Submit error:', error)
    
    // Type-safe error handling
    const errorMessage = error instanceof Error ? error.message : 'Unknown error occurred'
    const errorName = error instanceof Error ? error.name : 'Unknown'
    const errorStack = error instanceof Error ? error.stack : 'No stack trace available'
    
    console.error('Error details:', {
      name: errorName,
      message: errorMessage,
      stack: errorStack
    })
    showErrorPopup(errorMessage)
  } finally {
    // Restore button state
    console.log('Restoring button state...')
    const submitButton = document.querySelector('button[type="submit"]') as HTMLButtonElement
    if (submitButton) {
      submitButton.disabled = false
      submitButton.innerHTML = 'Submit Form'
    }
    console.log('=== Submit Form Ended ===')
  }
}

// Success popup function
const showSuccessPopup = (requestId: string, employeeName: string, status: string, actionText?: string) => {
  // Remove existing popup if any
  const existingPopup = document.getElementById('successPopup')
  if (existingPopup) {
    existingPopup.remove()
  }

  // Create popup container
  const popup = document.createElement('div')
  popup.id = 'successPopup'
  popup.innerHTML = `
    <div class="popup-overlay">
      <div class="popup-container">
        <div class="popup-icon">
          <svg width="64" height="64" viewBox="0 0 24 24" fill="none">
            <circle cx="12" cy="12" r="10" fill="#4CAF50"/>
            <path d="M9 12l2 2 4-4" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
        <h2 class="popup-title">Sukses!</h2>
        <p class="popup-message">Data ${actionText || 'ditambahkan'}</p>
        <div class="popup-details">
          <div class="detail-item">
            <span class="detail-label">Request ID:</span>
            <span class="detail-value">#${requestId}</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Employee:</span>
            <span class="detail-value">${employeeName}</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Status:</span>
            <span class="detail-value status-badge">${status}</span>
          </div>
        </div>
        <button class="popup-button" onclick="closeSuccessPopup()">OK</button>
      </div>
    </div>
  `

  // Add styles
  popup.innerHTML += `
    <style>
      .popup-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        animation: fadeIn 0.3s ease-in-out;
      }

      .popup-container {
        background: white;
        border-radius: 16px;
        padding: 32px;
        max-width: 400px;
        width: 90%;
        text-align: center;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        animation: slideUp 0.3s ease-in-out;
      }

      .popup-icon {
        margin-bottom: 16px;
      }

      .popup-title {
        font-size: 24px;
        font-weight: 600;
        color: #1f2937;
        margin: 0 0 8px 0;
      }

      .popup-message {
        font-size: 16px;
        color: #6b7280;
        margin: 0 0 24px 0;
      }

      .popup-details {
        background: #f9fafb;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 24px;
        text-align: left;
      }

      .detail-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
      }

      .detail-item:last-child {
        margin-bottom: 0;
      }

      .detail-label {
        font-size: 14px;
        color: #6b7280;
        font-weight: 500;
      }

      .detail-value {
        font-size: 14px;
        color: #1f2937;
        font-weight: 600;
      }

      .status-badge {
        background: #fef3c7;
        color: #92400e;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 12px;
      }

      .popup-button {
        background: #4CAF50;
        color: white;
        border: none;
        border-radius: 8px;
        padding: 12px 24px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.2s;
        width: 100%;
      }

      .popup-button:hover {
        background: #45a049;
      }

      @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
      }

      @keyframes slideUp {
        from { 
          opacity: 0;
          transform: translateY(20px);
        }
        to { 
          opacity: 1;
          transform: translateY(0);
        }
      }
    </style>
  `

  // Add close function to global scope
  ;(window as any).closeSuccessPopup = () => {
    const popup = document.getElementById('successPopup')
    if (popup) {
      popup.style.animation = 'fadeOut 0.3s ease-in-out'
      setTimeout(() => popup.remove(), 300)
    }
  }

  // Add fade out animation
  const style = document.createElement('style')
  style.textContent = `
    @keyframes fadeOut {
      from { opacity: 1; }
      to { opacity: 0; }
    }
  `
  document.head.appendChild(style)

  // Append to body
  document.body.appendChild(popup)

  // Auto close after 5 seconds
  setTimeout(() => {
    ;(window as any).closeSuccessPopup()
  }, 5000)
}

// Error popup function
const showErrorPopup = (message: string) => {
  // Remove existing popup if any
  const existingPopup = document.getElementById('errorPopup')
  if (existingPopup) {
    existingPopup.remove()
  }

  // Create popup container
  const popup = document.createElement('div')
  popup.id = 'errorPopup'
  popup.innerHTML = `
    <div class="popup-overlay">
      <div class="popup-container error">
        <div class="popup-icon">
          <svg width="64" height="64" viewBox="0 0 24 24" fill="none">
            <circle cx="12" cy="12" r="10" fill="#EF4444"/>
            <path d="M15 9l-6 6M9 9l6 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
        <h2 class="popup-title">Error!</h2>
        <p class="popup-message">${message}</p>
        <button class="popup-button error" onclick="closeErrorPopup()">OK</button>
      </div>
    </div>
  `

  // Add error styles
  popup.innerHTML += `
    <style>
      .popup-container.error {
        border-top: 4px solid #EF4444;
      }
      
      .popup-button.error {
        background: #EF4444;
      }
      
      .popup-button.error:hover {
        background: #dc2626;
      }
    </style>
  `

  // Add close function to global scope
  ;(window as any).closeErrorPopup = () => {
    const popup = document.getElementById('errorPopup')
    if (popup) {
      popup.style.animation = 'fadeOut 0.3s ease-in-out'
      setTimeout(() => popup.remove(), 300)
    }
  }

  // Append to body
  document.body.appendChild(popup)
}

// Load existing request data for editing
const loadRequestData = async (id: number) => {
  try {
    loading.value = true
    console.log('Loading akses logic request data for ID:', id)
    
    const response = await fetch(`/api/akses-logic-requests/${id}`)
    const result = await response.json()
    
    if (result.success) {
      const request = result.data
      console.log('Request data loaded:', request)
      
      // Populate form with existing data
      formData.value.employeeId = request.employee_id.toString()
      formData.value.email = request.employee?.email || ''
      formData.value.position = request.employee?.posisi_jabatan || ''
      formData.value.department = request.employee?.nama_organisasi || ''
      formData.value.manager = request.employee?.nomer_hp_wa || ''
      formData.value.accessLevel = request.jenis_akses || 'clientless'
      formData.value.vpnServer = request.nama_sistem || ''
      formData.value.deviceType = request.ip_address || ''
      formData.value.duration = request.masa_berlaku || ''
      formData.value.reason = request.keperluan_vpn || ''
      formData.value.accessType = request.pengguna_hak_akses || ''
      formData.value.agreeToTerms = request.sudah_menandatangani_surat_pernyataan
      formData.value.agreeToPolicy = request.memahami_kebijakan_keamanan
      
      console.log('Form populated with existing data:', formData.value)
    } else {
      console.error('Failed to load request data:', result.message)
      alert('Gagal memuat data request: ' + (result.message || 'Terjadi kesalahan'))
    }
  } catch (error) {
    console.error('Error loading request data:', error)
    alert('Terjadi kesalahan saat memuat data request')
  } finally {
    loading.value = false
  }
}

// Lifecycle
onMounted(() => {
  console.log('Component mounted, initializing...')
  
  // Check if we're in edit mode
  const urlParams = new URLSearchParams(window.location.search)
  const editParam = urlParams.get('edit')
  
  if (editParam) {
    isEditMode.value = true
    editId.value = parseInt(editParam)
    console.log('Edit mode detected for ID:', editId.value)
    
    // Load existing data after fetching employees and VPN servers
    Promise.all([fetchEmployees(), fetchVpnServers()]).then(() => {
      if (editId.value) {
        loadRequestData(editId.value)
      }
    })
  } else {
    console.log('New form mode')
    fetchEmployees()
    fetchVpnServers()
  }
  
  console.log('Initial form data:', formData.value)
})
</script>

<template>
  <div>
    <VCard>
      <VCardTitle class="d-flex align-center gap-2 pa-4">
        <VIcon icon="ri-vpn-line" size="24" />
        <span v-if="!isEditMode">Form Akses Logic</span>
        <span v-else>Edit Akses Logic (ID: {{ editId }})</span>
      </VCardTitle>
      
      <VCardText v-if="isEditMode" class="mb-4">
        <VAlert
          type="info"
          variant="tonal"
          density="compact"
          class="mb-4"
        >
          <div class="d-flex align-center gap-2">
            <VIcon icon="ri-information-line" />
            <span><strong>Mode Edit:</strong> Data employee dan ID request tidak dapat diubah. Hanya data lain yang bisa diperbarui.</span>
          </div>
        </VAlert>
      </VCardText>
      
      <VCardText>
        <VForm @submit.prevent="submitForm">
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
                        v-model="formData.employeeId"
                        :items="employees"
                        item-title="nama_lengkap"
                        item-value="id"
                        label="Nama Lengkap"
                        prepend-inner-icon="ri-user-line"
                        variant="outlined"
                        required
                        :loading="loading"
                        :disabled="isEditMode"
                        :menu-props="{ eager: true }"
                        hide-details="auto"
                        clearable
                        searchable
                        @update:model-value="(value) => console.log('VSelect updated:', value, 'Type:', typeof value)"
                      />
                    </VCol>
                    <VCol cols="12" md="6">
                      <VTextField
                        v-model="formData.email"
                        label="Email Address"
                        placeholder="Email will be auto-filled"
                        variant="outlined"
                        required
                        :readonly="isEditMode"
                        hide-details="auto"
                      />
                    </VCol>
                    <VCol cols="12" md="6">
                      <VTextField
                        v-model="formData.position"
                        label="Posisi/Jabatan"
                        variant="outlined"
                        required
                        :readonly="isEditMode"
                        hide-details="auto"
                      />
                    </VCol>
                    <VCol cols="12" md="6">
                      <VTextField
                        v-model="formData.department"
                        label="Nama Organisasi"
                        variant="outlined"
                        required
                        :readonly="isEditMode"
                        hide-details="auto"
                      />
                    </VCol>
                    <VCol cols="12" md="6">
                      <VTextField
                        v-model="formData.manager"
                        label="Nomer HP/WA"
                        variant="outlined"
                        required
                        :readonly="isEditMode"
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
                            v-model="formData.vpnServer"
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
                            v-model="formData.deviceType"
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
                            v-model="formData.accessLevel"
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
                            :disabled="!formData.vpnServer || !formData.accessLevel"
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
                        v-model="formData.duration"
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
                    v-model="formData.reason"
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
                    v-model="formData.accessType"
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
                v-model="formData.signature_image"
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
                    v-model="formData.agreeToTerms"
                    label="Pengguna hak akses sudah menandatangani Surat Pernyataan Menjaga Kerahasiaan"
                    hide-details="auto"
                    required
                  />
                  <VCheckbox
                    v-model="formData.agreeToPolicy"
                    label="Pengguna hak akses telah memahami dan bersedia mematuhi kebijakan keamanan informasi pada Jakarta Smart City"
                    hide-details="auto"
                    required
                  />
                </VCardText>
              </VCard>
            </VCol>

            <!-- Form Actions -->
            <VCol cols="12">
              <div class="d-flex gap-3">
                <VBtn
                  type="submit"
                  color="primary"
                  :disabled="!isFormValid"
                  :loading="false"
                >
                  Submit Form
                </VBtn>
                <VBtn
                  type="button"
                  color="secondary"
                  variant="outlined"
                  @click="resetForm"
                >
                  Reset
                </VBtn>
              </div>
            </VCol>
          </VRow>
        </VForm>
      </VCardText>
    </VCard>
  </div>
</template>

<style scoped>
.v-card {
  border-radius: 12px;
}
</style>
