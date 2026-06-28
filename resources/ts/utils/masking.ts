/**
 * Utility functions for masking sensitive employee information
 * These functions are used for display purposes only
 * Original data is preserved for form submission and backend processing
 */

/**
 * Masks email address - shows first 2 characters and domain
 * Example: john.doe@example.com -> jo***@example.com
 */
export const maskEmail = (email: string): string => {
  if (!email || email.length === 0) return ''
  
  const atIndex = email.indexOf('@')
  if (atIndex <= 0) return email
  
  const username = email.substring(0, atIndex)
  const domain = email.substring(atIndex)
  
  if (username.length <= 2) {
    return `${username[0]}***${domain}`
  }
  
  return `${username.substring(0, 2)}***${domain}`
}

/**
 * Masks phone number - shows first 3 and last 3 digits
 * Example: 08123456789 -> 081****789
 */
export const maskPhone = (phone: string): string => {
  if (!phone || phone.length === 0) return ''
  
  // Remove non-numeric characters first
  const cleanPhone = phone.replace(/\D/g, '')
  
  if (cleanPhone.length <= 6) {
    return cleanPhone.substring(0, 2) + '***'
  }
  
  const start = cleanPhone.substring(0, 3)
  const end = cleanPhone.substring(cleanPhone.length - 3)
  const middle = '*'.repeat(cleanPhone.length - 6)
  
  return start + middle + end
}

/**
 * Masks NIK (Nomor Induk Kependudukan) - shows first 4 and last 4 digits
 * Example: 1234567890123456 -> 1234********3456
 */
export const maskNIK = (nik: string): string => {
  if (!nik || nik.length === 0) return ''
  
  // Remove non-numeric characters first
  const cleanNIK = nik.replace(/\D/g, '')
  
  if (cleanNIK.length <= 8) {
    return cleanNIK.substring(0, 2) + '***'
  }
  
  const start = cleanNIK.substring(0, 4)
  const end = cleanNIK.substring(cleanNIK.length - 4)
  const middle = '*'.repeat(cleanNIK.length - 8)
  
  return start + middle + end
}

/**
 * Creates a masked version of employee data for display
 * Original data is preserved for submission
 */
export const createMaskedEmployeeData = (employeeData: any) => {
  return {
    ...employeeData,
    email: maskEmail(employeeData.email || ''),
    nomer_hp_wa: maskPhone(employeeData.nomer_hp_wa || ''),
    nomor_ktp: maskNIK(employeeData.nomor_ktp || '')
  }
}
