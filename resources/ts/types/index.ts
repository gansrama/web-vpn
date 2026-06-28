// Employee Types
export interface Employee {
  id: number
  nama_lengkap: string
  nomor_ktp: string
  email: string
  username_vpn: string
  posisi_jabatan: string
  nama_organisasi: string
  nomer_hp_wa: string
  posisi_pemohon?: string // Added posisi_pemohon field
  created_at: string
  updated_at: string
}

// Akses Logic Request Types
export interface AksesLogicRequest {
  id: number
  employee_id: number
  employee?: Employee
  nama_sistem: string
  ip_address: string
  jenis_akses: string
  masa_berlaku: string
  keperluan_vpn: string
  pengguna_hak_akses: string
  sudah_menandatangani_surat_pernyataan: boolean
  memahami_kebijakan_keamanan: boolean
  status: 'pending' | 'approved' | 'rejected'
  catatan?: string
  request_type: string
  created_at: string
  updated_at: string
}

// Teleworking Request Types
export interface TeleworkingRequest {
  id: number
  employee_id: number
  employee?: Employee
  masa_berlaku: string
  keperluan_vpn: string
  pengguna_hak_akses: string
  sudah_menandatangani_surat_pernyataan: boolean
  memahami_kebijakan_keamanan: boolean
  status: 'pending' | 'approved' | 'rejected'
  catatan?: string
  request_type: string
  signature_path?: string // Added signature_path field
  posisi_pemohon?: string // Added posisi_pemohon field
  created_at: string
  updated_at: string
}

// VPN Server Types
export interface VpnServer {
  id: number
  nama_sistem: string
  server_location: string
  ip_address: string
  project: string
  is_active: boolean
  created_at: string
  updated_at: string
}

// API Response Types
export interface ApiResponse<T> {
  success: boolean
  data: T
  message?: string
  pagination?: {
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
}

// Form Data Types
export interface EmployeeFormData {
  nama_lengkap: string
  nomor_ktp: string
  email: string
  username_vpn: string
  posisi_jabatan: string
  nama_organisasi: string
  nomer_hp_wa: string
  // Add signature_image to base interface to resolve all errors
  signature_image?: File
  posisi_pemohon?: string
}

export interface AksesLogicFormData extends EmployeeFormData {
  nama_sistem: string
  jenis_akses: string
  masa_berlaku: string
  keperluan_vpn: string
  pengguna_hak_akses: string
  sudah_menandatangani_surat_pernyataan: boolean
  memahami_kebijakan_keamanan: boolean
  signature_image?: File // Added signature_image field
  posisi_pemohon?: string // Added posisi_pemohon field
  // Add all missing properties that are used in form
  employeeId?: string // For employee selection
  position?: string // For position field
  department?: string // For department field  
  manager?: string // For manager field
  duration?: string // For duration field
  reason?: string // For reason field
  accessType?: string // For accessType field
  deviceType?: string // For deviceType field
  vpnServer?: string // For vpnServer field
  accessLevel?: string // For accessLevel field
  agreeToTerms?: boolean // For agreeToTerms field
  agreeToPolicy?: boolean // For agreeToPolicy field
}

export interface TeleworkingFormData extends EmployeeFormData {
  masa_berlaku: string
  keperluan_vpn: string
  pengguna_hak_akses: string
  sudah_menandatangani_surat_pernyataan: boolean
  memahami_kebijakan_keamanan: boolean
  signature_image?: File // Added signature_image field
  posisi_pemohon?: string // Added posisi_pemohon field
  // Add all missing properties that are used in form
  employeeId?: string // For employee selection
  position?: string // For position field
  department?: string // For department field  
  manager?: string // For manager field
  duration?: string // For duration field
  reason?: string // For reason field
  accessType?: string // For accessType field
  agreeToTerms?: boolean // For agreeToTerms field
  agreeToPolicy?: boolean // For agreeToPolicy field
}
