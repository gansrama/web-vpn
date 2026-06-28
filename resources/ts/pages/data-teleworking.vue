<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useTheme } from 'vuetify'

const theme = useTheme()

// Define type for teleworking request object
interface TeleworkingRequest {
  id: number
  employee_id: number
  employee?: {
    id: number
    nama_lengkap: string
    email: string
    posisi_jabatan: string
    nama_organisasi: string
    nomer_hp_wa: string
    nomor_ktp: string
  }
  masa_berlaku: string
  keperluan_vpn: string
  pengguna_hak_akses: string
  sudah_menandatangani_surat_pernyataan: boolean
  memahami_kebijakan_keamanan: boolean
  status: string
  catatan: string
  request_type: string
  signature_path?: string
  posisi_pemohon?: string // Tambah posisi pemohon
  created_at: string
  updated_at: string
}

// Data state
const requests = ref<TeleworkingRequest[]>([])
const loading = ref(false)
const error = ref('')
const search = ref('')
const selectedStatus = ref('all')
const selectedEmployee = ref('all')

// Statistics
const stats = ref({
  total: 0,
  pending: 0,
  today: 0,
  byStatus: {}
})

// Fetch data from API
const fetchRequests = async () => {
  loading.value = true
  error.value = ''

  try {
    console.log('Fetching Teleworking requests and stats in parallel...')
    const [response, statsResponse] = await Promise.all([
      fetch('/api/teleworking-requests'),
      fetch('/api/teleworking-requests/stats')
    ])

    const [result, statsResult] = await Promise.all([
      response.json(),
      statsResponse.json()
    ])

    console.log('API Response:', result)

    if (result.success) {
      // Ensure we have an array, handle paginated or direct data
      requests.value = Array.isArray(result.data) ? result.data : (result.data?.data || [])
      console.log('Requests loaded:', requests.value.length)
    } else {
      error.value = result.message || 'Failed to fetch requests'
    }

    console.log('Stats Response:', statsResult)
    if (statsResult.success) {
      stats.value = statsResult.data
    }
  } catch (err) {
    console.error('Error fetching requests:', err)
    error.value = 'Error fetching requests. Please try again.'
  } finally {
    loading.value = false
  }
}

// Fetch statistics (kept for manual refresh if needed)
const fetchStats = async () => {
  try {
    console.log('Fetching statistics...')
    const response = await fetch('/api/teleworking-requests/stats')
    const result = await response.json()

    console.log('Stats Response:', result)

    if (result.success) {
      stats.value = result.data
    }
  } catch (err) {
    console.error('Error fetching stats:', err)
  }
}

// Filter requests
const filteredRequests = computed(() => {
  // Ensure requests.value is an array before filtering
  if (!Array.isArray(requests.value)) {
    return []
  }
  
  let filtered = requests.value
  
  // Filter by status
  if (selectedStatus.value !== 'all') {
    filtered = filtered.filter((req: TeleworkingRequest) => req.status === selectedStatus.value)
  }
  
  // Filter by employee
  if (selectedEmployee.value !== 'all') {
    filtered = filtered.filter((req: TeleworkingRequest) => req.employee_id === Number(selectedEmployee.value))
  }
  
  // Filter by search
  if (search.value) {
    const searchTerm = search.value.toLowerCase()
    filtered = filtered.filter((req: TeleworkingRequest) => 
      req.catatan?.toLowerCase().includes(searchTerm) ||
      req.keperluan_vpn?.toLowerCase().includes(searchTerm) ||
      req.pengguna_hak_akses?.toLowerCase().includes(searchTerm) ||
      req.employee?.nama_lengkap?.toLowerCase().includes(searchTerm) ||
      req.employee?.email?.toLowerCase().includes(searchTerm)
    )
  }
  
  return filtered
})

// Get unique employees for filter
const uniqueEmployees = computed(() => {
  const employees = new Map()
  
  // Ensure requests.value is an array before using forEach
  if (Array.isArray(requests.value)) {
    requests.value.forEach((req: TeleworkingRequest) => {
      if (!employees.has(req.employee_id) && req.employee) {
        employees.set(req.employee_id, req.employee.nama_lengkap)
      }
    })
  }
  
  return Array.from(employees.entries()).map(([id, name]) => ({ id, name }))
})

// Format date
const formatDate = (dateString: string) => {
  if (!dateString) return '-'
  const date = new Date(dateString)
  return date.toLocaleDateString('id-ID', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Format date for signature (31 Maret 2026)
const formatDateForSignature = (dateString: string) => {
  if (!dateString) return '-'
  const date = new Date(dateString)
  return date.toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
}

// Get status color
const getStatusColor = (status: string) => {
  switch (status) {
    case 'pending': return 'warning'
    case 'approved': return 'success'
    case 'rejected': return 'error'
    default: return 'info'
  }
}

// Get access type badge
const getAccessTypeBadge = (accessType: string) => {
  switch (accessType) {
    case 'asn': return { color: 'primary', text: 'ASN' }
    case 'non-asn': return { color: 'secondary', text: 'Non ASN' }
    default: return { color: 'grey', text: accessType }
  }
}

// Get access level text
const getAccessLevelText = (accessLevel: string) => {
  switch (accessLevel) {
    case 'clientless': return 'SSL VPN'
    case 'client': return 'Endpoint/Client-Based VPN'
    case 'ipsec': return 'IPsec VPN'
    default: return accessLevel
  }
}

// Get duration text
const getDurationText = (duration: string) => {
  switch (duration) {
    case '30': return '30 Hari'
    case '60': return '60 Hari'
    case '90': return '90 Hari'
    case 'q1': return 'Januari s.d Maret'
    case 'q2': return 'April s.d Juni'
    case 'q3': return 'Juli s.d September'
    case 'q4': return 'Oktober s.d Desember'
    default: return duration
  }
}

// Signature dialog state
const signatureDialog = ref(false)
const signatureLoading = ref(false)
const signatureError = ref('')
const currentSignature = ref('')
const signatureDialogTitle = ref('')

// Get signature for teleworking request
const getSignature = async (requestId: number, employeeName: string) => {
  signatureDialog.value = true
  signatureLoading.value = true
  signatureError.value = ''
  currentSignature.value = ''
  signatureDialogTitle.value = `Signature - ${employeeName}`
  
  try {
    const response = await fetch(`/api/teleworking-requests/${requestId}/signature`)
    const result = await response.json()
    
    if (result.success) {
      // Try direct URL first (faster), fallback to base64
      if (result.data.signature_path) {
        const directUrl = `/storage/signatures/${result.data.signature_path.split('/').pop()}`
        currentSignature.value = directUrl
      } else {
        // Fallback to base64
        currentSignature.value = `data:${result.data.mime_type};base64,${result.data.signature_base64}`
      }
    } else {
      signatureError.value = result.message || 'Signature not found'
    }
  } catch (err) {
    console.error('Error fetching signature:', err)
    signatureError.value = 'Error fetching signature. Please try again.'
  } finally {
    signatureLoading.value = false
  }
}

// Close signature dialog
const closeSignatureDialog = () => {
  signatureDialog.value = false
  currentSignature.value = ''
  signatureError.value = ''
}

// Get reason text
const getReasonText = (reason: string) => {
  // Since values now match titles exactly, return the reason as-is
  // This handles the new format where value === title
  return reason
}

// Close popup function (global scope)
const closeDetailsPopup = () => {
  const popup = document.getElementById('detailsPopup')
  if (popup) {
    popup.style.animation = 'fadeOut 0.3s ease-in-out'
    setTimeout(() => popup.remove(), 300)
  }
}

// View details function
const viewDetails = (item: TeleworkingRequest) => {
  // Create details popup
  const existingPopup = document.getElementById('detailsPopup')
  if (existingPopup) {
    existingPopup.remove()
  }

  const popup = document.createElement('div')
  popup.id = 'detailsPopup'
  
  const termsAgreed = item.sudah_menandatangani_surat_pernyataan ? 'Ya' : 'Tidak'
  const policyAgreed = item.memahami_kebijakan_keamanan ? 'Ya' : 'Tidak'
  
  popup.innerHTML = `
    <div class="popup-overlay" onclick="if(event.target === this) closeDetailsPopup()">
      <div class="popup-container details" onclick="event.stopPropagation()">
        <div class="popup-header">
          <h2 class="popup-title">Request Details</h2>
          <button class="close-btn" onclick="closeDetailsPopup()">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
              <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
          </button>
        </div>
        
        <div class="popup-content">
          <div class="detail-section">
            <h3>Request Information</h3>
            <div class="detail-grid">
              <div class="detail-item">
                <span class="label">Request ID:</span>
                <span class="value">#${item.id}</span>
              </div>
              <div class="detail-item">
                <span class="label">Status:</span>
                <span class="value status-badge-${getStatusColor(item.status)}">${item.status}</span>
              </div>
              <div class="detail-item">
                <span class="label">Created:</span>
                <span class="value">${formatDate(item.created_at)}</span>
              </div>
              <div class="detail-item">
                <span class="label">Updated:</span>
                <span class="value">${formatDate(item.updated_at)}</span>
              </div>
            </div>
          </div>
          
          <div class="detail-section">
            <h3>Employee Information</h3>
            <div class="detail-grid">
              <div class="detail-item">
                <span class="label">Employee ID:</span>
                <span class="value">${item.employee_id}</span>
              </div>
              <div class="detail-item">
                <span class="label">Name:</span>
                <span class="value">${item.employee?.nama_lengkap || 'Unknown'}</span>
              </div>
              <div class="detail-item">
                <span class="label">Email:</span>
                <span class="value">${item.employee?.email || 'Unknown'}</span>
              </div>
              <div class="detail-item">
                <span class="label">Position:</span>
                <span class="value">${item.employee?.posisi_jabatan || 'Unknown'}</span>
              </div>
              <div class="detail-item">
                <span class="label">Organization:</span>
                <span class="value">${item.employee?.nama_organisasi || 'Unknown'}</span>
              </div>
              <div class="detail-item">
                <span class="label">Phone/WA:</span>
                <span class="value">${item.employee?.nomer_hp_wa || 'Unknown'}</span>
              </div>
            </div>
          </div>
          
          <div class="detail-section">
            <h3>Masa Berlaku</h3>
            <div class="detail-grid">
              <div class="detail-item">
                <span class="label">Duration:</span>
                <span class="value">${getDurationText(item.masa_berlaku)}</span>
              </div>
              <div class="detail-item">
                <span class="label">Reason:</span>
                <span class="value">${getReasonText(item.keperluan_vpn)}</span>
              </div>
              <div class="detail-item">
                <span class="label">Access Type:</span>
                <span class="value">${getAccessTypeBadge(item.pengguna_hak_akses).text}</span>
              </div>
            </div>
          </div>
          
          <div class="detail-section">
            <h3>Agreements</h3>
            <div class="detail-grid">
              <div class="detail-item">
                <span class="label">Terms Agreement:</span>
                <span class="value">${termsAgreed}</span>
              </div>
              <div class="detail-item">
                <span class="label">Policy Agreement:</span>
                <span class="value">${policyAgreed}</span>
              </div>
            </div>
          </div>
        </div>
        
        <div class="popup-footer">
          <button class="popup-button print" onclick="printRequestDetails(${item.id})">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" style="margin-right: 8px;">
              <path d="M6 9V2h12v7M6 9h12M6 9v11h12V9M6 9l3-3M18 9l-3-3M8 13h8M8 17h5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Print Data
          </button>
          <select class="status-select" id="statusSelect-${item.id}" onchange="updateRequestStatus(${item.id}, this.value)">
            <option value="">Validasi</option>
            <option value="approved">Disetujui</option>
            <option value="rejected">Ditolak</option>
          </select>
        </div>
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
        cursor: pointer;
      }

      .popup-container.details {
        background: white;
        border-radius: 16px;
        max-width: 600px;
        width: 90%;
        max-height: 80vh;
        overflow-y: auto;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        animation: slideUp 0.3s ease-in-out;
      }

      .popup-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 24px 24px 0 24px;
        border-bottom: 1px solid #e5e7eb;
      }

      .popup-title {
        font-size: 20px;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
      }

      .close-btn {
        background: none;
        border: none;
        padding: 8px;
        border-radius: 8px;
        cursor: pointer;
        color: #6b7280;
        transition: all 0.2s;
      }

      .close-btn:hover {
        background: #f3f4f6;
        color: #374151;
      }

      .popup-content {
        padding: 24px;
      }

      .detail-section {
        margin-bottom: 24px;
      }

      .detail-section:last-child {
        margin-bottom: 0;
      }

      .detail-section h3 {
        font-size: 16px;
        font-weight: 600;
        color: #1f2937;
        margin: 0 0 16px 0;
        padding-bottom: 8px;
        border-bottom: 1px solid #e5e7eb;
      }

      .detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 16px;
      }

      .detail-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
      }

      .label {
        font-size: 14px;
        color: #6b7280;
        font-weight: 500;
      }

      .value {
        font-size: 14px;
        color: #1f2937;
        font-weight: 600;
      }

      .status-badge-warning {
        background: #fef3c7;
        color: #92400e;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 12px;
      }

      .status-badge-success {
        background: #d1fae5;
        color: #065f46;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 12px;
      }

      .status-badge-error {
        background: #fee2e2;
        color: #991b1b;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 12px;
      }

      .popup-footer {
        padding: 0 24px 24px 24px;
        border-top: 1px solid #e5e7eb;
        display: flex;
        gap: 12px;
      }

      .popup-button {
        background: #3b82f6;
        color: white;
        border: none;
        border-radius: 8px;
        padding: 12px 24px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.2s;
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
      }

      .popup-button:hover {
        background: #2563eb;
      }

      .popup-button.print {
        background: #10b981;
      }

      .popup-button.print:hover {
        background: #059669;
      }

      .status-select {
        background: #3b82f6;
        color: white;
        border: 2px solid #3b82f6;
        border-radius: 8px;
        padding: 12px 16px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        flex: 1;
        min-width: 200px;
        text-align: center;
      }

      .status-select:hover {
        border-color: #2563eb;
        background: #2563eb;
        color: white;
      }

      .status-select:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
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

  // Add functions to global scope
  ;(window as any).closeDetailsPopup = closeDetailsPopup
  ;(window as any).printRequestDetails = async (requestId: number) => {
    console.log('Print function called with requestId:', requestId)
    await printRequestDetails(requestId)
  }

  // Add update status function to global scope
  ;(window as any).updateRequestStatus = async (requestId: number, newStatus: string) => {
    if (!newStatus) {
      alert('Silakan pilih status terlebih dahulu')
      return
    }

    try {
      const response = await fetch(`/api/teleworking-requests/${requestId}/status`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ status: newStatus })
      })

      const result = await response.json()

      if (result.success) {
        const statusText = newStatus === 'approved' ? 'Disetujui' : 'Ditolak'
        alert(`Status request berhasil diubah menjadi ${statusText}`)
        closeDetailsPopup()
        refreshData()
      } else {
        alert('Gagal mengubah status: ' + (result.message || 'Terjadi kesalahan'))
      }
    } catch (error) {
      console.error('Error updating status:', error)
      alert('Terjadi kesalahan saat mengubah status')
    }
  }

  // Debug: Log that functions are registered
  console.log('Popup functions registered:', {
    closeDetailsPopup: typeof (window as any).closeDetailsPopup,
    printRequestDetails: typeof (window as any).printRequestDetails,
    updateRequestStatus: typeof (window as any).updateRequestStatus
  })

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
}

// Generate HTML content function
const generatePrintContent = async (request: TeleworkingRequest, employeeName: string, employeeEmail: string, employeeData: any) => {
  // Pre-compute all values to avoid template interpolation issues
  const accessTypeText = getAccessTypeBadge(request.pengguna_hak_akses).text
  const formattedDate = formatDateForSignature(request.updated_at)
  const pernyataanText = request.sudah_menandatangani_surat_pernyataan ? 'YA' : 'TIDAK'
  const kebijakanText = request.memahami_kebijakan_keamanan ? 'YA' : 'TIDAK'
  const reasonText = getReasonText(request.keperluan_vpn)
  const durationText = getDurationText(request.masa_berlaku)
  
  // Fetch signature from API if available
  let signatureSrc = ''
  if (request.signature_path) {
    try {
      const response = await fetch(`/api/teleworking-requests/${request.id}/signature`)
      const result = await response.json()
      
      if (result.success) {
        // Try direct URL first, fallback to base64
        if (result.data.signature_path) {
          const directUrl = `/storage/signatures/${result.data.signature_path.split('/').pop()}`
          signatureSrc = directUrl
        } else {
          signatureSrc = `data:${result.data.mime_type};base64,${result.data.signature_base64}`
        }
      }
    } catch (error) {
      console.error('Error fetching signature for print:', error)
      signatureSrc = ''
    }
  }
  
  const html = `<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Permohonan Teleworking</title>
    <style>
        @page { size: A4; margin: 0; }
        body { font-family: Arial, sans-serif; background: #e0e0e0; display: flex; flex-direction: column; align-items: center; padding: 20px; margin: 0; }
        .a4-page { width: 210mm; min-height: 297mm; background: white; box-shadow: 0 0 10px rgba(0,0,0,0.1); margin-bottom: 20px; padding: 20mm; box-sizing: border-box; font-size: 11pt; line-height: 1.5; position: relative; }
        @media print { body { background: none; padding: 0; } .a4-page { box-shadow: none; margin: 0; page-break-after: always; } .a4-page:last-child { page-break-after: auto; } }
        table { width: 100%; border-collapse: collapse; }
        .header-table, .header-table td { border: 1px solid black; }
        .header-table td { padding: 5px; }
        .header-logo { width: 60px; height: 70px; display: flex; align-items: center; justify-content: center; margin: 0 auto; text-align: center; font-weight: bold; font-size: 9pt; }
        h3.title { text-align: center; text-decoration: underline; margin-top: 20px; margin-bottom: 20px; font-size: 14pt; }
        .form-section { margin-bottom: 15px; }
        .no-border-table td { border: none; padding: 2px 5px; vertical-align: top; }
        .data-table, .data-table th, .data-table td { border: 1px solid black; }
        .data-table th, .data-table td { padding: 8px; text-align: center; }
        ul.no-bullet { list-style-type: none; padding-left: 0; margin-top: 0; }
        .checkbox { display: inline-block; width: 12px; height: 12px; border: 1px solid black; margin-right: 5px; position: relative; top: 2px; }
    </style>
</head>
<body>
    <div class="a4-page">
        <table class="header-table" style="text-align: center; margin-bottom: 10px;">
            <tr>
                <td rowspan="4" width="15%"><div class="header-logo"><img src="${window.location.origin}/images/jaya-raya-logo.png" alt="JAYA RAYA" style="width: 80px; height: 90px; object-fit: contain; border: none;" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';"><div style="display: none; text-align: center; font-weight: bold; font-size: 12pt; color: #333;">LOGO<br>JAYA RAYA</div></div></td>
                <td rowspan="4" width="50%">PEMERINTAH PROVINSI DAERAH KHUSUS IBUKOTA JAKARTA<br><b>UNIT PENGELOLA JAKARTA SMART CITY</b><br><span style="font-size: 9pt">Jalan Medan Merdeka Selatan 8-9 Blok B Lt. 3<br>Telepon (021) 3822255 Faximile (021) 3822255<br>Jakarta 10110</span></td>
                <td width="15%" style="text-align: left; font-size: 9pt;">No. Dok</td><td width="20%" style="text-align: left; font-size: 9pt;">: JSC.SMKI.FM.15.01</td>
            </tr>
            <tr><td style="text-align: left; font-size: 9pt;">Rev & Tgl</td><td style="text-align: left; font-size: 9pt;">: 00 (07 Agustus 2023)</td></tr>
            <tr><td style="text-align: left; font-size: 9pt;">Klasifikasi</td><td style="text-align: left; font-size: 9pt;">: INTERNAL</td></tr>
            <tr><td style="text-align: left; font-size: 9pt;">Hal</td><td style="text-align: left; font-size: 9pt;">: 1 dari 2</td></tr>
        </table>
        <h3 class="title">FORM PERMOHONAN TELEWORKING</h3>
        <div class="form-section">
            <p style="margin-top: 0;">Saya yang bertanda tangan di bawah ini:</p>
            <table class="no-border-table" style="margin-bottom: 10px;"><tr><td width="200">Nama</td><td width="10">:</td><td>${employeeName}</td></tr></table>
            <ul class="no-bullet">
                <li style="margin-bottom: 10px;">&bull; Data Pegawai:
                    <table class="no-border-table" style="margin-left: 15px;">
                        <tr><td width="180"><span class=""></span> Nomor KTP</td><td width="10">:</td><td>${employeeData.nomorKTP}</td></tr>
                        <tr><td><span class=""></span> Nomor HP/WA</td><td>:</td><td>${employeeData.nomorHP}</td></tr>
                        <tr><td><span class=""></span> Email</td><td>:</td><td>${employeeEmail}</td></tr>
                        <tr><td><span class=""></span> Unit Kerja/Divisi</td><td>:</td><td>${employeeData.unit}</td></tr>
                        <tr><td><span class=""></span> Posisi/Jabatan</td><td>:</td><td>${employeeData.jabatan}</td></tr>
                    </table>
                </li>
            </ul>
        </div>
        <div class="form-section">
            <p>Mengajukan permohonan Teleworking berikut:</p>
            <table class="no-border-table" style="margin-bottom: 15px;"><tr><td width="200">&bull; Pengguna Hak Akses</td><td width="10">:</td><td>${accessTypeText}</td></tr></table>
            
        </div>
        <div class="form-section">
            <table class="no-border-table" style="margin-top: 15px; margin-bottom: 15px;">
                <tr>
                    <td width="150">Keperluan</td>
                    <td width="10">:</td>
                    <td>${getReasonText(request.keperluan_vpn)}</td>
                </tr>
            </table>

            <p style="margin-bottom: 5px;">Masa Berlaku (pilih salah satu):</p>
            <table class="no-border-table" style="margin-left: 10px;">
                <tr>
                    <td width="20"><span class=""></span></td>
                    <td width="250">ASN<br>(Maksimal 6 Bulan), yaitu</td>
                    <td width="10">:</td>
                    <td>dari ............................ s.d. ............................</td>
                </tr>
                <tr>
                    <td style="padding-top: 10px;"><span class=""></span></td>
                    <td style="padding-top: 10px;">Non ASN/Pihak Ketiga/Pihak Eksternal<br>(Maksimal 3 Bulan), yaitu</td>
                    <td style="padding-top: 10px;">:</td>
                    <td style="padding-top: 10px;">${getDurationText(request.masa_berlaku)}</td>
                </tr>
            </table>
        </div>
    </div>
    <div class="a4-page">
        <table class="header-table" style="text-align: center; margin-bottom: 10px;">
            <tr>
                <td rowspan="4" width="15%"><div class="header-logo"><img src="${window.location.origin}/images/jaya-raya-logo.png" alt="JAYA RAYA" style="width: 80px; height: 90px; object-fit: contain; border: none;" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';"><div style="display: none; text-align: center; font-weight: bold; font-size: 12pt; color: #333;">LOGO<br>JAYA RAYA</div></div></td>
                <td rowspan="4" width="50%">PEMERINTAH PROVINSI DAERAH KHUSUS IBUKOTA JAKARTA<br><b>UNIT PENGELOLA JAKARTA SMART CITY</b><br><span style="font-size: 9pt">Jalan Medan Merdeka Selatan 8-9 Blok B Lt. 3<br>Telepon (021) 3822255 Faximile (021) 3822255<br>Jakarta 10110</span></td>
                <td width="15%" style="text-align: left; font-size: 9pt;">No. Dok</td><td width="20%" style="text-align: left; font-size: 9pt;">: JSC.SMKI.FM.15.01</td>
            </tr>
            <tr><td style="text-align: left; font-size: 9pt;">Rev & Tgl</td><td style="text-align: left; font-size: 9pt;">: 00 (07 Agustus 2023)</td></tr>
            <tr><td style="text-align: left; font-size: 9pt;">Klasifikasi</td><td style="text-align: left; font-size: 9pt;">: INTERNAL</td></tr>
            <tr><td style="text-align: left; font-size: 9pt;">Hal</td><td style="text-align: left; font-size: 9pt;">: 2 dari 2</td></tr>
        </table>
        <h3 class="title">FORM PERMOHONAN TELEWORKING</h3>
        <div class="form-section" style="margin-top: 30px;">
            <table class="no-border-table">
                <tr><td width="90%">Pengguna hak akses sudah menandatangani Surat Pernyataan Menjaga Kerahasiaan</td><td width="10%" style="font-weight: bold;">: ${pernyataanText}</td></tr>
                <tr><td style="padding-top: 15px;">Pengguna hak akses telah memahami dan bersedia mematuhi kebijakan keamanan<br>informasi pada Jakarta Smart City</td><td style="padding-top: 15px; font-weight: bold;">: ${kebijakanText}</td></tr>
            </table>
        </div>
        <div class="form-section">
            <table class="no-border-table" style="width: 100%; margin-top: 50px; text-align: center;">
                <tr><td width="50%">Dibuat Oleh,</td><td width="50%">Disetujui Oleh,<br>Jakarta, ${formattedDate}</td></tr>
                <tr><td style="padding-top: 10px;">Pemohon</td><td style="padding-top: 10px;">Chief of Information Security<br>Officer (CISO)</td></tr>
                <tr><td height="30"></td><td></td></tr>
                <tr>
                    <td style="text-align: center; vertical-align: top;">
                        <div style="margin-bottom: 5px; min-height: 100px; padding: 5px; display: flex; align-items: center; justify-content: center;">
                            ${signatureSrc ? 
                                `<img 
                                    src="${signatureSrc}" 
                                    alt="Signature" 
                                    style="max-height: 100px; max-width: 200px; display: block; margin: 0 auto;" 
                                />` : 
                                ``
                            }
                        </div>
                        <b>( ${employeeName} )</b>
                    </td>
                    <td style="text-align: center; vertical-align: top;">
                        <div style="margin-bottom: 5px; min-height: 80px; padding: 5px;"></div>
                        <b>( ........................................................ )</b>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>`
  return html
}

// Print request details function
const printRequestDetails = async (requestId: number) => {
  console.log('printRequestDetails function called with requestId:', requestId)
  
  try {
    // Find the request data
    const request = requests.value.find((r: TeleworkingRequest) => r.id === requestId)
    if (!request) {
      console.error('Request not found for ID:', requestId)
      alert('Request tidak ditemukan')
      return
    }

    console.log('Found request:', request)

    // Get employee data directly from the request
    const employeeName = request.employee?.nama_lengkap || 'Unknown'
    const employeeEmail = request.employee?.email || 'Unknown'
    
    console.log('Employee info extracted:', { employeeName, employeeEmail, employeeId: request.employee_id })
    
    // Create employee data object for print - fetch unmasked data if needed
    let employeeData = {
      nomorKTP: request.employee?.nomor_ktp || '-',
      nomorHP: request.employee?.nomer_hp_wa || '-',
      unit: request.employee?.nama_organisasi || '-',
      jabatan: request.employee?.posisi_jabatan || '-'
    }

    // Check if employee data is masked and fetch unmasked if needed
    if (request.employee?.nomor_ktp?.includes('***') || 
        request.employee?.nomer_hp_wa?.includes('***') || 
        request.employee?.email?.includes('***')) {
      try {
        console.log('Fetching unmasked employee data for print, ID:', request.employee_id)
        const employeeResponse = await fetch(`/api/employees/${request.employee_id}/unmasked`)
        if (employeeResponse.ok) {
          const response = await employeeResponse.json()
          if (response.success && response.data) {
            employeeData = {
              nomorKTP: response.data.nomor_ktp || '-',
              nomorHP: response.data.nomer_hp_wa || '-',
              unit: response.data.nama_organisasi || '-',
              jabatan: response.data.posisi_jabatan || '-'
            }
          }
        }
      } catch (error) {
        console.error('Error fetching unmasked employee data for print:', error)
      }
    }

    // Generate HTML content
    const printContent = await generatePrintContent(request, employeeName, employeeEmail, employeeData)
    console.log('Print content generated')

    // Create print window
    const printWindow = window.open('', '_blank', 'width=800,height=600')
    if (!printWindow) {
      console.error('Failed to open print window')
      alert('Please allow popups to print / Mohon izinkan popup untuk print')
      return
    }

    console.log('Print window opened successfully')

    // Write content to print window
    printWindow.document.write(printContent)
    printWindow.document.close()

    console.log('Content written to print window')

    // Wait for content to load, then print
    printWindow.onload = () => {
      console.log('Print window loaded, triggering print')
      setTimeout(() => {
        printWindow.print()
        printWindow.close()
      }, 500)
    }

  } catch (error) {
    console.error('Error in printRequestDetails:', error)
    const errorMessage = error instanceof Error ? error.message : 'Unknown error occurred'
    alert('Terjadi kesalahan saat mencetak: ' + errorMessage)
  }
}

// Delete request function
const deleteRequest = async (requestId: number) => {
  if (!confirm('Apakah Anda yakin ingin menghapus request ini?')) {
    return
  }

  try {
    const response = await fetch(`/api/teleworking-requests/${requestId}`, {
      method: 'DELETE'
    })

    const result = await response.json()

    if (result.success) {
      alert('Request berhasil dihapus')
      refreshData()
    } else {
      alert('Gagal menghapus request: ' + (result.message || 'Terjadi kesalahan'))
    }
  } catch (error) {
    console.error('Error deleting request:', error)
    alert('Terjadi kesalahan saat menghapus request')
  }
}


// Refresh data
const refreshData = () => {
  fetchRequests()
  fetchStats()
}

// On mounted
onMounted(() => {
  console.log('Data Teleworking page mounted')
  fetchRequests()
  fetchStats()
})
</script>

<template>
  <VContainer fluid class="pa-4">
    <!-- Header -->
    <VRow>
      <VCol cols="12">
        <VCard variant="outlined">
          <VCardTitle class="d-flex align-center pa-4">
            <VIcon icon="ri-database-2-line" class="mr-3" />
            <span class="text-h5">Data Teleworking</span>
            <VSpacer />
            <VBtn
              color="primary"
              prepend-icon="ri-refresh-line"
              @click="refreshData"
              :loading="loading"
            >
              Refresh
            </VBtn>
          </VCardTitle>
        </VCard>
      </VCol>
    </VRow>

    <!-- Statistics Cards -->
    <VRow class="mb-4">
      <VCol cols="12" md="3">
        <VCard variant="outlined" class="text-center">
          <VCardText>
            <div class="text-h4 text-primary mb-2">{{ stats.total }}</div>
            <div class="text-body-2 text-medium-emphasis">Total Requests</div>
          </VCardText>
        </VCard>
      </VCol>
      <VCol cols="12" md="3">
        <VCard variant="outlined" class="text-center">
          <VCardText>
            <div class="text-h4 text-warning mb-2">{{ stats.pending }}</div>
            <div class="text-body-2 text-medium-emphasis">Pending</div>
          </VCardText>
        </VCard>
      </VCol>
      <VCol cols="12" md="3">
        <VCard variant="outlined" class="text-center">
          <VCardText>
            <div class="text-h4 text-success mb-2">{{ stats.today }}</div>
            <div class="text-body-2 text-medium-emphasis">Today</div>
          </VCardText>
        </VCard>
      </VCol>
      <VCol cols="12" md="3">
        <VCard variant="outlined" class="text-center">
          <VCardText>
            <div class="text-h4 text-info mb-2">{{ filteredRequests.length }}</div>
            <div class="text-body-2 text-medium-emphasis">Filtered</div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- Filters -->
    <VRow class="mb-4">
      <VCol cols="12" md="4">
        <VTextField
          v-model="search"
          label="Search"
          prepend-inner-icon="ri-search-line"
          variant="outlined"
          hide-details
          clearable
        />
      </VCol>
      <VCol cols="12" md="3">
        <VSelect
          v-model="selectedStatus"
          :items="[
            { title: 'All Status', value: 'all' },
            { title: 'Pending', value: 'pending' },
            { title: 'Approved', value: 'approved' },
            { title: 'Rejected', value: 'rejected' }
          ]"
          label="Status"
          variant="outlined"
          hide-details
        />
      </VCol>
      <VCol cols="12" md="3">
        <VSelect
          v-model="selectedEmployee"
          :items="[
            { title: 'All Employees', value: 'all' },
            ...uniqueEmployees.map(emp => ({ title: emp.name, value: emp.id }))
          ]"
          label="Employee"
          variant="outlined"
          hide-details
        />
      </VCol>
      <VCol cols="12" md="3">
        <VBtn
          color="primary"
          variant="outlined"
          prepend-icon="ri-add-line"
          to="/form-teleworking"
          block
        >
          New Request
        </VBtn>
      </VCol>
    </VRow>

    <!-- Data Table -->
    <VRow>
      <VCol cols="12">
        <VCard variant="outlined">
          <VCardTitle class="pa-4">
            <span class="text-h6">Request List</span>
            <VSpacer />
            <span class="text-body-2 text-medium-emphasis">
              {{ filteredRequests.length }} of {{ requests.length }} records
            </span>
          </VCardTitle>
          
          <VDivider />
          
          <VCardText class="pa-0">
            <!-- Loading State -->
            <div v-if="loading" class="text-center pa-8">
              <VProgressCircular indeterminate color="primary" size="48" />
              <div class="mt-4 text-body-1">Loading data...</div>
            </div>

            <!-- Error State -->
            <div v-else-if="error" class="text-center pa-8">
              <VIcon icon="ri-error-warning-line" color="error" size="48" />
              <div class="mt-4 text-body-1 text-error">{{ error }}</div>
              <VBtn color="primary" class="mt-4" @click="refreshData">Retry</VBtn>
            </div>

            <!-- Data Table -->
            <div v-else-if="filteredRequests.length > 0">
              <VDataTable
                :headers="[
                  { title: 'ID', key: 'id', sortable: true },
                  { title: 'Employee', key: 'employee_name', sortable: true },
                  { title: 'Duration', key: 'masa_berlaku', sortable: true },
                  { title: 'Reason', key: 'keperluan_vpn', sortable: true },
                  { title: 'Access Type', key: 'pengguna_hak_akses', sortable: true },
                  { title: 'Status', key: 'status', sortable: true },
                  { title: 'Created', key: 'created_at', sortable: true },
                  { title: 'Actions', key: 'actions', sortable: false }
                ]"
                :items="filteredRequests.map((item: TeleworkingRequest) => ({
                  ...item,
                  employee_name: item.employee?.nama_lengkap || 'Unknown'
                }))"
                :loading="loading"
                class="elevation-0"
              >
                <!-- Status Column -->
                <template #item.status="{ item }">
                  <VChip
                    :color="getStatusColor(item.status)"
                    size="small"
                    variant="flat"
                  >
                    {{ item.status }}
                  </VChip>
                </template>

                <!-- Access Type Column -->
                <template #item.pengguna_hak_akses="{ item }">
                  <VChip
                    :color="getAccessTypeBadge(item.pengguna_hak_akses).color"
                    size="small"
                    variant="flat"
                  >
                    {{ getAccessTypeBadge(item.pengguna_hak_akses).text }}
                  </VChip>
                </template>

                <!-- Duration Column -->
                <template #item.masa_berlaku="{ item }">
                  <span class="text-body-2">{{ getDurationText(item.masa_berlaku) }}</span>
                </template>

                <!-- Reason Column -->
                <template #item.keperluan_vpn="{ item }">
                  <span class="text-body-2">{{ getReasonText(item.keperluan_vpn) }}</span>
                </template>

                <!-- Created Column -->
                <template #item.created_at="{ item }">
                  <span class="text-body-2">{{ formatDate(item.created_at) }}</span>
                </template>

                <!-- Actions Column -->
                <template #item.actions="{ item }">
                  <div class="d-flex gap-1">
                    <VBtn
                      icon="ri-eye-line"
                      size="small"
                      color="primary"
                      variant="text"
                      @click="viewDetails(item)"
                    />
                    <VBtn
                      icon="ri-file-edit-line"
                      size="small"
                      color="warning"
                      variant="text"
                      @click="viewDetails(item)"
                    />
                    <VBtn
                      icon="ri-quill-pen-line"
                      size="small"
                      color="info"
                      variant="text"
                      @click="getSignature(item.id, item.employee?.nama_lengkap || 'Unknown')"
                      :disabled="!item.signature_path"
                      :title="item.signature_path ? 'View Signature' : 'No Signature Available'"
                    />
                    <VBtn
                      icon="ri-delete-bin-line"
                      size="small"
                      color="error"
                      variant="text"
                      @click="deleteRequest(item.id)"
                    />
                  </div>
                </template>
              </VDataTable>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center pa-8">
              <VIcon icon="ri-inbox-line" color="disabled" size="48" />
              <div class="mt-4 text-body-1 text-disabled">No requests found</div>
              <VBtn color="primary" class="mt-4" to="/form-akses-logic">Create First Request</VBtn>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- Signature Dialog -->
    <VDialog v-model="signatureDialog" max-width="600px">
      <VCard>
        <VCardTitle class="d-flex align-center pa-4">
          <VIcon icon="ri-quill-pen-line" class="me-3" />
          {{ signatureDialogTitle }}
        </VCardTitle>
        
        <VDivider />
        
        <VCardText class="pa-4">
          <div v-if="signatureLoading" class="text-center pa-8">
            <VProgressCircular indeterminate color="primary" size="48" />
            <div class="mt-4 text-body-1">Loading signature...</div>
          </div>
          
          <div v-else-if="signatureError" class="text-center pa-8">
            <VIcon icon="ri-error-warning-line" color="error" size="48" />
            <div class="mt-4 text-body-1 text-error">{{ signatureError }}</div>
          </div>
          
          <div v-else-if="currentSignature" class="text-center">
            <img 
              :src="currentSignature" 
              alt="Signature" 
              style="max-width: 100%; max-height: 400px; border: 1px solid #e0e0e0; border-radius: 8px;"
            />
          </div>
          
          <div v-else class="text-center pa-8">
            <VIcon icon="ri-image-line" color="disabled" size="48" />
            <div class="mt-4 text-body-1 text-disabled">No signature available</div>
          </div>
        </VCardText>
        
        <VDivider />
        
        <VCardActions class="pa-4">
          <VSpacer />
          <VBtn @click="closeSignatureDialog" variant="outlined">
            Close
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </VContainer>
</template>

<style scoped>
.v-data-table {
  border-radius: 0;
}

.v-data-table :deep(.v-data-table__thead) {
  background-color: #f5f5f5;
}

.v-data-table :deep(.v-data-table__tbody tr:hover) {
  background-color: #f9f9f9;
}
</style>
