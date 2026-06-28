<script setup lang="ts">
import { computed, watch, nextTick } from 'vue'
import { usePopupService } from '@/composables/usePopupService'

const { isPopupOpen, popupItem, popupType, closePopup } = usePopupService()

// Computed property for employee data extraction
const employeeInfo = computed(() => {
  if (!popupItem.value) {
    console.log('popupItem is null, returning default employee info')
    return {
      name: 'Unknown',
      email: 'Unknown',
      nomorKTP: 'N/A',
      nomorHP: 'N/A',
      unit: 'N/A',
      jabatan: 'N/A'
    }
  }

  const request = popupItem.value
  console.log('Extracting employee info from request:', request)
  
  // Try to extract name and email from different possible sources
  let name = 'Unknown'
  let email = 'Unknown'

  // For teleworking and akses-logic requests, check if employee relationship data is available
  if (request.employee) {
    console.log('Found employee data:', request.employee)
    name = request.employee.nama_lengkap || request.employee.name || 'Unknown'
    email = request.employee.email || 'Unknown'
  } else {
    console.log('No employee data, trying other sources')
    // Try catatan field first (for requests without employee relationship)
    if (request.catatan) {
      const nameMatch = request.catatan.match(/Submitted by: ([^(]+)/)
      const emailMatch = request.catatan.match(/\(([^)]+)\)/)
      if (nameMatch) name = nameMatch[1].trim()
      if (emailMatch) email = emailMatch[1].trim()
    }

    // Try direct fields if catatan didn't work
    if (name === 'Unknown') {
      name = request.nama_lengkap || request.name || request.employee_name || request.nama || 'Unknown'
    }
    if (email === 'Unknown') {
      email = request.email || request.employee_email || 'Unknown'
    }
  }

  // Extract other employee data with multiple fallbacks
  // For both request types, prioritize employee relationship data
  let nomorKTP, nomorHP, unit, jabatan
  
  if (request.employee) {
    nomorKTP = request.employee.nomor_ktp || request.employee.nomorKTP || 'N/A'
    nomorHP = request.employee.nomor_hp || request.employee.nomorHP || 'N/A'
    unit = request.employee.unit || request.employee.divisi || 'N/A'
    jabatan = request.employee.jabatan || request.employee.posisi || 'N/A'
  } else {
    // Fallback to direct fields
    nomorKTP = request.nomor_ktp || request.nomorKTP || 'N/A'
    nomorHP = request.nomor_hp || request.nomorHP || 'N/A'
    unit = request.unit || request.divisi || 'N/A'
    jabatan = request.jabatan || request.posisi || 'N/A'
    jabatan = request.jabatan || request.position || request.posisi || request.role || 'N/A'
  }

  return {
    name,
    email,
    nomorKTP,
    nomorHP,
    unit,
    jabatan
  }
})

const formatDate = (dateString: string) => {
  if (!dateString) return 'N/A'
  
  try {
    return new Date(dateString).toLocaleString('id-ID', {
      day: '2-digit',
      month: '2-digit', 
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    })
  } catch (error) {
    console.error('Error formatting date:', dateString, error)
    return 'Invalid Date'
  }
}

const getStatusColor = (status: string) => {
  if (!status) return 'default'
  
  const colors: { [key: string]: string } = {
    'pending': 'warning',
    'approved': 'success',
    'rejected': 'error',
    'completed': 'info'
  }
  return colors[status] || 'default'
}

const getAccessLevelText = (accessLevel: string) => {
  if (!accessLevel) return 'N/A'
  
  const levels: { [key: string]: string } = {
    'clientless': 'Clientless VPN',
    'full-tunnel': 'Full Tunnel VPN',
    'split-tunnel': 'Split Tunnel VPN'
  }
  return levels[accessLevel] || accessLevel
}

const getDurationText = (duration: string) => {
  if (!duration) return 'N/A'
  
  const durations: { [key: string]: string } = {
    '1-bulan': '1 Bulan',
    '3-bulan': '3 Bulan',
    '6-bulan': '6 Bulan',
    '1-tahun': '1 Tahun'
  }
  return durations[duration] || duration
}

const getReasonText = (reason: string) => {
  if (!reason) return 'N/A'
  
  const reasons: { [key: string]: string } = {
    'remote-database': 'Akses Remote Database Server',
    'internal-network': 'Akses Internal Network',
    'file-server': 'Akses File Server',
    'dev-server': 'Akses Development Server',
    'testing-env': 'Akses Testing Environment',
    'prod-server': 'Akses Production Server',
    'monitoring': 'Akses Monitoring Tools',
    'backup-server': 'Akses Backup Server',
    'user-extension': 'Perpanjangan User Akun VPN',
    'user-new': 'Pengajuan User Akun VPN Baru',
    'user-reset': 'Reset Password User Akun VPN',
    'ip-address': 'Penambahan Akses IP Address VPN',
    'google-auth-remove': 'Delete Google Auth Code'
  }
  return reasons[reason] || reason
}

const getAccessTypeBadge = (accessType: string) => {
  if (!accessType) return { text: 'N/A', color: 'default' }
  
  const types: { [key: string]: { text: string; color: string } } = {
    'permanent': { text: 'Permanent', color: 'success' },
    'temporary': { text: 'Temporary', color: 'warning' }
  }
  return types[accessType] || { text: accessType, color: 'info' }
}

// Close popup on escape key
const handleKeydown = (event: KeyboardEvent) => {
  if (event.key === 'Escape') {
    closePopup()
  }
}

// Add and remove event listener
watch(isPopupOpen, (newValue) => {
  if (newValue) {
    console.log('Popup opened with request:', popupItem.value) // Debug popup data
    nextTick(() => {
      document.addEventListener('keydown', handleKeydown)
    })
  } else {
    document.removeEventListener('keydown', handleKeydown)
  }
})

const printRequestDetails = () => {
  if (!popupItem.value) return
  
  let printContent = ''
  
  if (popupType.value === 'akses-logic') {
    printContent = generateAksesLogicPrintContent(popupItem.value)
  } else if (popupType.value === 'teleworking') {
    printContent = generateTeleworkingPrintContent(popupItem.value)
  }

  const printWindow = window.open('', '_blank')
  if (!printWindow) return
  
  printWindow.document.write(printContent)
  printWindow.document.close()
  printWindow.print()
}

const generateAksesLogicPrintContent = (request: any) => {
  console.log('Akses Logic request data:', request) // Debug line to inspect data structure
  console.log('Employee info from computed:', employeeInfo.value) // Debug employee info
  
  const termsAgreed = request.sudah_menandatangani_surat_pernyataan ? 'Ya' : 'Tidak'
  const policyAgreed = request.memahami_kebijakan_keamanan ? 'Ya' : 'Tidak'
  const statusColor = getStatusColor(request.status)
  
  // Use the computed employee info
  const empInfo = employeeInfo.value
  
  // Extract employee data
  const employeeName = empInfo.name
  const employeeEmail = empInfo.email
  const employeeData = {
    nomorKTP: empInfo.nomorKTP,
    nomorHP: empInfo.nomorHP,
    unit: empInfo.unit,
    jabatan: empInfo.jabatan
  }
  
  const accessTypeText = getAccessTypeBadge(request.pengguna_hak_akses).text
  const accessLevelText = getAccessLevelText(request.jenis_akses)
  const pernyataanText = termsAgreed
  const kebijakanText = policyAgreed
  const formattedDate = new Date().toLocaleDateString('id-ID', { 
    day: 'numeric', 
    month: 'long', 
    year: 'numeric' 
  })
  
  // Get the origin for logo path before creating template
  const logoPath = `${window.location.origin}/images/jaya-raya-logo.png`
  
  return `<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Permohonan Hak Akses Logic</title>
    <style>
        @page { size: A4; margin: 0; }
        body { font-family: Arial, sans-serif; background: #e0e0e0; display: flex; flex-direction: column; align-items: center; padding: 20px; margin: 0; }
        .a4-page { width: 210mm; min-height: 297mm; background: white; box-shadow: 0 0 10px rgba(0,0,0,0.1); margin-bottom: 20px; padding: 20mm; box-sizing: border-box; font-size: 11pt; line-height: 1.5; position: relative; }
        @media print { body { background: none; padding: 0; } .a4-page { box-shadow: none; margin: 0; page-break-after: always; } .a4-page:last-child { page-break-after: auto; } }
        table { width: 100%; border-collapse: collapse; }
        .header-table, .header-table td { border: 1px solid black; }
        .header-table td { padding: 5px; }
        .header-logo { width: 60px; height: 70px; display: flex; align-items: center; justify-content: center; margin: 0 auto; text-align: center; font-weight: bold; font-size: 9pt; }
        .logo-fallback { display: none; text-align: center; font-weight: bold; font-size: 10pt; color: #333; line-height: 1.1; padding: 5px; border: 1px solid #ccc; background: #f9f9f9; }
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
                <td rowspan="4" width="15%"><div class="header-logo"><img src="${logoPath}" alt="JAYA RAYA" style="width: 80px; height: 90px; object-fit: contain; border: none;" onload="console.log('Logo loaded successfully from', this.src);" onerror="console.error('Logo failed to load from', this.src); this.style.display='none'; this.parentElement.querySelector('.logo-fallback').style.display='block';"><div class="logo-fallback">LOGO<br>JAYA RAYA</div></div></td>
                <td rowspan="4" width="50%">PEMERINTAH PROVINSI DAERAH KHUSUS IBUKOTA JAKARTA<br><b>UNIT PENGELOLA JAKARTA SMART CITY</b><br><span style="font-size: 9pt">Jalan Medan Merdeka Selatan 8-9 Blok B Lt. 3<br>Telepon (021) 3822255 Faximile (021) 3822255<br>Jakarta 10110</span></td>
                <td width="15%" style="text-align: left; font-size: 9pt;">No. Dok</td><td width="20%" style="text-align: left; font-size: 9pt;">: JSC.SMKI.FM.15.01</td>
            </tr>
            <tr><td style="text-align: left; font-size: 9pt;">Rev & Tgl</td><td style="text-align: left; font-size: 9pt;">: 00 (07 Agustus 2023)</td></tr>
            <tr><td style="text-align: left; font-size: 9pt;">Klasifikasi</td><td style="text-align: left; font-size: 9pt;">: INTERNAL</td></tr>
            <tr><td style="text-align: left; font-size: 9pt;">Hal</td><td style="text-align: left; font-size: 9pt;">: 1 dari 2</td></tr>
        </table>
        <h3 class="title">FORM PERMOHONAN HAK AKSES LOGIC</h3>
        <div class="form-section">
            <p style="margin-top: 0;">Saya yang bertanda tangan di bawah ini:</p>
            <table class="no-border-table" style="margin-bottom: 10px;"><tr><td width="200">Nama</td><td width="10">:</td><td>${employeeName}</td></tr></table>
            <ul class="no-bullet">
                <li style="margin-bottom: 10px;">&bull; Data Pegawai:
                    <table class="no-border-table" style="margin-left: 15px;">
                        <tr><td width="180"> Nomor KTP</td><td width="10">:</td><td>${employeeData.nomorKTP}</td></tr>
                        <tr><td> Nomor HP/WA</td><td>:</td><td>${employeeData.nomorHP}</td></tr>
                        <tr><td> Email</td><td>:</td><td>${employeeEmail}</td></tr>
                        <tr><td> Unit Kerja/Divisi</td><td>:</td><td>${employeeData.unit}</td></tr>
                        <tr><td> Posisi/Jabatan</td><td>:</td><td>${employeeData.jabatan}</td></tr>
                    </table>
                </li>
            </ul>
        </div>
        <div class="form-section">
            <p>Mengajukan permohonan hak akses logic berikut:</p>
            <table class="no-border-table" style="margin-bottom: 15px;"><tr><td width="200">&bull; Pengguna Hak Akses</td><td width="10">:</td><td>${accessTypeText}</td></tr></table>
            <p style="margin-bottom: 5px;">Akses Logic yang Diminta:</p>
            <table class="data-table">
                <thead><tr style="background-color: #f2f2f2;"><th>ID Request</th><th>Lokasi Sistem/<br>IP Address</th><th>Nama Sistem</th><th>Jenis Akses</th><th>Keterangan<br>(nama pengguna)</th></tr></thead>
                <tbody><tr><td>${request.id}</td><td>${request.ip_address}</td><td>${request.nama_sistem}</td><td>${accessLevelText}</td><td>${employeeName}</td></tr></tbody>
            </table>
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
                    <td width="20"></td>
                    <td width="250">ASN<br>(Maksimal 6 Bulan), yaitu</td>
                    <td width="10">:</td>
                    <td>dari ............................ s.d. ............................</td>
                </tr>
                <tr>
                    <td style="padding-top: 10px;"></td>
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
                <td rowspan="4" width="15%"><div class="header-logo"><img src="${logoPath}" alt="JAYA RAYA" style="width: 80px; height: 90px; object-fit: contain; border: none;" onload="console.log('Logo loaded successfully from', this.src);" onerror="console.error('Logo failed to load from', this.src); this.style.display='none'; this.parentElement.querySelector('.logo-fallback').style.display='block';"><div class="logo-fallback">LOGO<br>JAYA RAYA</div></div></td>
                <td rowspan="4" width="50%">PEMERINTAH PROVINSI DAERAH KHUSUS IBUKOTA JAKARTA<br><b>UNIT PENGELOLA JAKARTA SMART CITY</b><br><span style="font-size: 9pt">Jalan Medan Merdeka Selatan 8-9 Blok B Lt. 3<br>Telepon (021) 3822255 Faximile (021) 3822255<br>Jakarta 10110</span></td>
                <td width="15%" style="text-align: left; font-size: 9pt;">No. Dok</td><td width="20%" style="text-align: left; font-size: 9pt;">: JSC.SMKI.FM.15.01</td>
            </tr>
            <tr><td style="text-align: left; font-size: 9pt;">Rev & Tgl</td><td style="text-align: left; font-size: 9pt;">: 00 (07 Agustus 2023)</td></tr>
            <tr><td style="text-align: left; font-size: 9pt;">Klasifikasi</td><td style="text-align: left; font-size: 9pt;">: INTERNAL</td></tr>
            <tr><td style="text-align: left; font-size: 9pt;">Hal</td><td style="text-align: left; font-size: 9pt;">: 2 dari 2</td></tr>
        </table>
        <h3 class="title">FORM PERMOHONAN HAK AKSES LOGIC</h3>
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
                <tr><td height="120"></td><td></td></tr>
                <tr><td><b>( ${employeeName} )</b></td><td><b>( ........................................................ )</b></td></tr>
            </table>
        </div>
    </div>
</body>
</html>`
}

const generateTeleworkingPrintContent = (request: any) => {
  console.log('Teleworking request data:', request) // Debug line to inspect data structure
  console.log('Employee info from computed:', employeeInfo.value) // Debug employee info
  
  const termsAgreed = request.sudah_menandatangani_surat_pernyataan ? 'Ya' : 'Tidak'
  const policyAgreed = request.memahami_kebijakan_keamanan ? 'Ya' : 'Tidak'
  const statusColor = getStatusColor(request.status)
  
  // Use the computed employee info
  const empInfo = employeeInfo.value
  
  const accessTypeText = getAccessTypeBadge(request.pengguna_hak_akses).text
  const pernyataanText = termsAgreed
  const kebijakanText = policyAgreed
  const formattedDate = new Date().toLocaleDateString('id-ID', { 
    day: 'numeric', 
    month: 'long', 
    year: 'numeric' 
  })
  
  // Get the origin for logo path before creating template
  const logoPath = `${window.location.origin}/images/jaya-raya-logo.png`
  
  return `<!DOCTYPE html>
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
        .logo-fallback { display: none; text-align: center; font-weight: bold; font-size: 10pt; color: #333; line-height: 1.1; padding: 5px; border: 1px solid #ccc; background: #f9f9f9; }
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
                <td rowspan="4" width="15%"><div class="header-logo"><img src="${logoPath}" alt="JAYA RAYA" style="width: 80px; height: 90px; object-fit: contain; border: none;" onload="console.log('Logo loaded successfully from', this.src);" onerror="console.error('Logo failed to load from', this.src); this.style.display='none'; this.parentElement.querySelector('.logo-fallback').style.display='block';"><div class="logo-fallback">LOGO<br>JAYA RAYA</div></div></td>
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
            <table class="no-border-table" style="margin-bottom: 10px;"><tr><td width="200">Nama</td><td width="10">:</td><td>${empInfo.name}</td></tr></table>
            <ul class="no-bullet">
                <li style="margin-bottom: 10px;">&bull; Data Pegawai:
                    <table class="no-border-table" style="margin-left: 15px;">
                        <tr><td width="180"><span class=""></span> Unit Kerja/Divisi</td><td width="10">:</td><td>${empInfo.unit}</td></tr>
                        <tr><td><span class=""></span> Posisi/Jabatan</td><td>:</td><td>${empInfo.jabatan}</td></tr>
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
                <td rowspan="4" width="15%"><div class="header-logo"><img src="${logoPath}" alt="JAYA RAYA" style="width: 80px; height: 90px; object-fit: contain; border: none;" onload="console.log('Logo loaded successfully from', this.src);" onerror="console.error('Logo failed to load from', this.src); this.style.display='none'; this.parentElement.querySelector('.logo-fallback').style.display='block';"><div class="logo-fallback">LOGO<br>JAYA RAYA</div></div></td>
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
                <tr><td height="120"></td><td></td></tr>
                <tr><td><b>( ${empInfo.name} )</b></td><td><b>( ........................................................ )</b></td></tr>
            </table>
        </div>
    </div>
</body>
</html>`
  }
</script>

<template>
  <VDialog
    v-model="isPopupOpen"
    max-width="800"
    persistent
  >
    <VCard v-if="popupItem">
      <!-- Header -->
      <VCardItem class="bg-primary text-white">
        <VCardTitle class="d-flex align-center">
          <VIcon class="me-3">
            {{ popupType === 'akses-logic' ? 'ri-shield-keyhole-line' : 'ri-home-office-line' }}
          </VIcon>
          Request Details - #{{ popupItem?.id || 'N/A' }}
        </VCardTitle>
        
        <template #append>
          <VBtn
            icon
            variant="text"
            @click="closePopup"
            class="text-white"
          >
            <VIcon>ri-close-line</VIcon>
          </VBtn>
        </template>
      </VCardItem>

      <!-- Content -->
      <VCardText id="popup-content">
        <!-- Request Information -->
        <div class="section mb-6">
          <h3 class="text-h6 mb-3">Request Information</h3>
          <VRow>
            <VCol cols="12" md="6">
              <div class="d-flex justify-space-between mb-2">
                <span class="font-weight-medium">Request ID:</span>
                <span>#{{ popupItem?.id || 'N/A' }}</span>
              </div>
            </VCol>
            <VCol cols="12" md="6">
              <div class="d-flex justify-space-between mb-2">
                <span class="font-weight-medium">Status:</span>
                <VChip
                  :color="getStatusColor(popupItem?.status || '')"
                  size="small"
                >
                  {{ popupItem?.status || 'N/A' }}
                </VChip>
              </div>
            </VCol>
            <VCol cols="12" md="6">
              <div class="d-flex justify-space-between mb-2">
                <span class="font-weight-medium">Created:</span>
                <span>{{ formatDate(popupItem?.created_at) }}</span>
              </div>
            </VCol>
            <VCol cols="12" md="6">
              <div class="d-flex justify-space-between mb-2">
                <span class="font-weight-medium">Updated:</span>
                <span>{{ formatDate(popupItem?.updated_at) }}</span>
              </div>
            </VCol>
          </VRow>
        </div>

        <!-- Employee Information -->
        <div class="section mb-6">
          <h3 class="text-h6 mb-3">Employee Information</h3>
          <VRow>
            <VCol cols="12" md="6">
              <div class="d-flex justify-space-between mb-2">
                <span class="font-weight-medium">Employee ID:</span>
                <span>{{ popupItem.employee_id }}</span>
              </div>
            </VCol>
            <VCol cols="12" md="6">
              <div class="d-flex justify-space-between mb-2">
                <span class="font-weight-medium">Name:</span>
                <span>{{ employeeInfo.name }}</span>
              </div>
            </VCol>
          </VRow>
        </div>

        <!-- VPN Configuration (for akses-logic) -->
        <div v-if="popupType === 'akses-logic'" class="section mb-6">
          <h3 class="text-h6 mb-3">VPN Configuration</h3>
          <VRow>
            <VCol cols="12" md="6">
              <div class="d-flex justify-space-between mb-2">
                <span class="font-weight-medium">VPN Server:</span>
                <span>{{ popupItem?.nama_sistem || 'N/A' }}</span>
              </div>
            </VCol>
            <VCol cols="12" md="6">
              <div class="d-flex justify-space-between mb-2">
                <span class="font-weight-medium">Reason:</span>
                <span>{{ getReasonText(popupItem?.keperluan_vpn || '') }}</span>
              </div>
            </VCol>
            <VCol cols="12" md="6">
              <div class="d-flex justify-space-between mb-2">
                <span class="font-weight-medium">Access Level:</span>
                <span>{{ getAccessLevelText(popupItem?.jenis_akses || '') }}</span>
              </div>
            </VCol>
            <VCol cols="12" md="6">
              <div class="d-flex justify-space-between mb-2">
                <span class="font-weight-medium">Duration:</span>
                <span>{{ getDurationText(popupItem?.masa_berlaku || '') }}</span>
              </div>
            </VCol>
            <VCol cols="12" md="6">
              <div class="d-flex justify-space-between mb-2">
                <span class="font-weight-medium">Access Type:</span>
                <VChip
                  :color="getAccessTypeBadge(popupItem?.pengguna_hak_akses || '').color"
                  size="small"
                >
                  {{ getAccessTypeBadge(popupItem?.pengguna_hak_akses || '').text }}
                </VChip>
              </div>
            </VCol>
          </VRow>
        </div>

        <!-- Teleworking Details -->
        <div v-else class="section mb-6">
          <h3 class="text-h6 mb-3">Teleworking Details</h3>
          <VRow>
            <VCol cols="12" md="6">
              <div class="d-flex justify-space-between mb-2">
                <span class="font-weight-medium">Duration:</span>
                <span>{{ getDurationText(popupItem?.masa_berlaku || '') }}</span>
              </div>
            </VCol>
            <VCol cols="12" md="6">
              <div class="d-flex justify-space-between mb-2">
                <span class="font-weight-medium">Reason:</span>
                <span>{{ getReasonText(popupItem?.keperluan_vpn || '') }}</span>
              </div>
            </VCol>
          </VRow>
        </div>

        <!-- Agreements -->
        <div class="section mb-6">
          <h3 class="text-h6 mb-3">Agreements</h3>
          <VRow>
            <VCol cols="12" md="6">
              <div class="d-flex justify-space-between mb-2">
                <span class="font-weight-medium">Terms Agreement:</span>
                <VChip
                  :color="popupItem?.sudah_menandatangani_surat_pernyataan ? 'success' : 'error'"
                  size="small"
                >
                  {{ popupItem?.sudah_menandatangani_surat_pernyataan ? 'Ya' : 'Tidak' }}
                </VChip>
              </div>
            </VCol>
            <VCol cols="12" md="6">
              <div class="d-flex justify-space-between mb-2">
                <span class="font-weight-medium">Policy Agreement:</span>
                <VChip
                  :color="popupItem?.memahami_kebijakan_keamanan ? 'success' : 'error'"
                  size="small"
                >
                  {{ popupItem?.memahami_kebijakan_keamanan ? 'Ya' : 'Tidak' }}
                </VChip>
              </div>
            </VCol>
          </VRow>
        </div>
      </VCardText>

      <!-- Actions -->
      <VDivider />
      
      <VCardActions class="pa-4">
        <VBtn
          color="success"
          prepend-icon="ri-printer-line"
          @click="printRequestDetails"
        >
          Print Data
        </VBtn>
        
        <VSpacer />
        
        <VBtn
          color="error"
          prepend-icon="ri-close-line"
          @click="closePopup"
        >
          Close
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
