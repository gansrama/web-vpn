<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useAuth } from '@/composables/useAuth'

const { user, isAuthenticated, updateUser } = useAuth()

// Tab management
const activeTab = ref('profile')

// Profile form data
const profileForm = ref({
  firstName: '',
  lastName: '',
  phone: '',
  address: '',
  birth_date: '',
  gender: '',
  bio: '',
  country: '',
  city: '',
  postal_code: ''
})

// Password form data
const passwordForm = ref({
  currentPassword: '',
  newPassword: '',
  confirmPassword: ''
})

// Avatar management
const avatarPreview = ref('')
const avatarFile = ref<File | null>(null)
const isUploadingAvatar = ref(false)
const avatarInputRef = ref<HTMLInputElement | null>(null)

// Password visibility
const isPasswordVisible = ref(false)
const isNewPasswordVisible = ref(false)
const isConfirmPasswordVisible = ref(false)

// Loading states
const isLoading = ref(false)
const isSaving = ref(false)

// Computed properties
const fullName = computed(() => {
  return `${profileForm.value.firstName} ${profileForm.value.lastName}`.trim()
})

// Load profile data
const loadProfile = async () => {
  try {
    isLoading.value = true
    const response = await fetch('/api/profile', {
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      }
    })

    const result = await response.json()
    
    if (response.ok && result.success) {
      // Update user data in auth composable
      if (result.user) {
        updateUser(result.user)
      }

      // Set profile form data
      if (result.profile) {
        const nameParts = (result.user?.name || '').split(' ')
        profileForm.value = {
          firstName: nameParts[0] || '',
          lastName: nameParts.slice(1).join(' ') || '',
          phone: result.profile.phone || '',
          address: result.profile.address || '',
          birth_date: result.profile.birth_date || '',
          gender: result.profile.gender || '',
          bio: result.profile.bio || '',
          country: result.profile.country || '',
          city: result.profile.city || '',
          postal_code: result.profile.postal_code || ''
        }
        avatarPreview.value = result.profile.avatar || ''
      }
    } else {
      console.error('Failed to load profile:', result.message)
    }
  } catch (error) {
    console.error('Error loading profile:', error)
  } finally {
    isLoading.value = false
  }
}

// Handle avatar file selection
const handleAvatarChange = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files && target.files[0]) {
    avatarFile.value = target.files[0]
    
    // Create preview
    const reader = new FileReader()
    reader.onload = (e) => {
      avatarPreview.value = e.target?.result as string
    }
    reader.readAsDataURL(target.files[0])
  }
}

// Trigger avatar input click
const triggerAvatarInput = () => {
  if (avatarInputRef.value) {
    avatarInputRef.value.click()
  }
}

// Upload avatar
const uploadAvatar = async () => {
  if (!avatarFile.value) return

  try {
    isUploadingAvatar.value = true
    const formData = new FormData()
    formData.append('avatar', avatarFile.value)

    const response = await fetch('/api/profile/avatar', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: formData
    })

    const result = await response.json()
    
    if (response.ok && result.success) {
      avatarPreview.value = result.avatar_url
      localStorage.setItem('userAvatar', result.avatar_url)
      
      // Emit avatar update event
      window.dispatchEvent(new CustomEvent('avatarUpdated', {
        detail: { avatar: result.avatar_url }
      }))
      
      // Show success message
      alert('Avatar uploaded successfully!')
    } else {
      alert('Failed to upload avatar: ' + result.message)
    }
  } catch (error) {
    console.error('Error uploading avatar:', error)
    alert('Error uploading avatar')
  } finally {
    isUploadingAvatar.value = false
  }
}

// Update profile
const updateProfile = async () => {
  try {
    isSaving.value = true
    const response = await fetch('/api/profile', {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify(profileForm.value)
    })

    const result = await response.json()
    
    if (response.ok && result.success) {
      // Update user data in auth composable
      if (result.user) {
        updateUser(result.user)
        
        // Emit user update event
        window.dispatchEvent(new CustomEvent('userUpdated', {
          detail: result.user
        }))
      }
      
      alert('Profile updated successfully!')
    } else {
      alert('Failed to update profile: ' + result.message)
    }
  } catch (error) {
    console.error('Error updating profile:', error)
    alert('Error updating profile')
  } finally {
    isSaving.value = false
  }
}

// Handle password change
const handlePasswordChange = () => {
  // Basic validation
  if (passwordForm.value.newPassword !== passwordForm.value.confirmPassword) {
    alert('New password and confirm password do not match')
    return
  }
  
  if (passwordForm.value.newPassword.length < 8) {
    alert('Password must be at least 8 characters long')
    return
  }

  // TODO: Implement password change API call
  alert('Password change functionality not yet implemented')
}

onMounted(() => {
  if (isAuthenticated.value) {
    loadProfile()
  }
})
</script>

<template>
  <div class="pa-6">
    <h1 class="text-h4 mb-6">
      Account Settings
    </h1>

    <VCard>
      <VTabs v-model="activeTab" class="mb-6">
        <VTab value="profile">Profile</VTab>
        <VTab value="security">Security</VTab>
        <VTab value="notifications">Notifications</VTab>
      </VTabs>

      <VWindow v-model="activeTab">
        <!-- Profile Tab -->
        <VWindowItem value="profile">
          <VCardText>
            <div class="mb-6">
              <h2 class="text-h5 mb-4">Profile Information</h2>
              
              <!-- Avatar Section -->
              <div class="d-flex align-center mb-6">
                <div class="me-4">
                  <VAvatar size="120" class="elevation-2">
                    <VImg 
                      v-if="avatarPreview" 
                      :src="avatarPreview" 
                      alt="Profile Avatar"
                    />
                    <VIcon v-else icon="ri-user-line" size="60" />
                  </VAvatar>
                </div>
                <div>
                  <VBtn
                    color="primary"
                    variant="outlined"
                    @click="triggerAvatarInput"
                    :disabled="isUploadingAvatar"
                    class="me-2"
                  >
                    <VIcon icon="ri-upload-line" class="me-2" />
                    {{ isUploadingAvatar ? 'Uploading...' : 'Change Avatar' }}
                  </VBtn>
                  <VBtn
                    v-if="avatarFile"
                    color="success"
                    @click="uploadAvatar"
                    :disabled="isUploadingAvatar"
                  >
                    <VIcon icon="ri-check-line" class="me-2" />
                    Save Avatar
                  </VBtn>
                  <input
                    ref="avatarInputRef"
                    type="file"
                    accept="image/*"
                    @change="handleAvatarChange"
                    style="display: none"
                  />
                </div>
              </div>

              <!-- Profile Form -->
              <VForm @submit.prevent="updateProfile">
                <VRow>
                  <VCol cols="12" md="6">
                    <VTextField
                      v-model="profileForm.firstName"
                      label="First Name"
                      prepend-inner-icon="ri-user-line"
                      :disabled="isLoading || isSaving"
                      required
                    />
                  </VCol>
                  <VCol cols="12" md="6">
                    <VTextField
                      v-model="profileForm.lastName"
                      label="Last Name"
                      prepend-inner-icon="ri-user-line"
                      :disabled="isLoading || isSaving"
                      required
                    />
                  </VCol>
                  <VCol cols="12" md="6">
                    <VTextField
                      v-model="profileForm.phone"
                      label="Phone"
                      prepend-inner-icon="ri-phone-line"
                      :disabled="isLoading || isSaving"
                    />
                  </VCol>
                  <VCol cols="12" md="6">
                    <VTextField
                      v-model="profileForm.birth_date"
                      label="Birth Date"
                      type="date"
                      prepend-inner-icon="ri-calendar-line"
                      :disabled="isLoading || isSaving"
                    />
                  </VCol>
                  <VCol cols="12" md="6">
                    <VSelect
                      v-model="profileForm.gender"
                      label="Gender"
                      :items="['Male', 'Female', 'Other']"
                      prepend-inner-icon="ri-genderless-line"
                      :disabled="isLoading || isSaving"
                    />
                  </VCol>
                  <VCol cols="12" md="6">
                    <VTextField
                      v-model="profileForm.country"
                      label="Country"
                      prepend-inner-icon="ri-global-line"
                      :disabled="isLoading || isSaving"
                    />
                  </VCol>
                  <VCol cols="12" md="6">
                    <VTextField
                      v-model="profileForm.city"
                      label="City"
                      prepend-inner-icon="ri-building-line"
                      :disabled="isLoading || isSaving"
                    />
                  </VCol>
                  <VCol cols="12" md="6">
                    <VTextField
                      v-model="profileForm.postal_code"
                      label="Postal Code"
                      prepend-inner-icon="ri-mail-line"
                      :disabled="isLoading || isSaving"
                    />
                  </VCol>
                  <VCol cols="12">
                    <VTextarea
                      v-model="profileForm.address"
                      label="Address"
                      prepend-inner-icon="ri-map-pin-line"
                      rows="3"
                      :disabled="isLoading || isSaving"
                    />
                  </VCol>
                  <VCol cols="12">
                    <VTextarea
                      v-model="profileForm.bio"
                      label="Bio"
                      prepend-inner-icon="ri-file-text-line"
                      rows="3"
                      :disabled="isLoading || isSaving"
                    />
                  </VCol>
                  <VCol cols="12">
                    <VBtn
                      type="submit"
                      color="primary"
                      :loading="isSaving"
                      :disabled="isLoading"
                    >
                      <VIcon icon="ri-save-line" class="me-2" />
                      Save Profile
                    </VBtn>
                  </VCol>
                </VRow>
              </VForm>
            </div>
          </VCardText>
        </VWindowItem>

        <!-- Security Tab -->
        <VWindowItem value="security">
          <VCardText>
            <h2 class="text-h5 mb-4">Security Settings</h2>
            
            <VForm @submit.prevent="handlePasswordChange">
              <VRow>
                <VCol cols="12">
                  <VTextField
                    v-model="passwordForm.currentPassword"
                    label="Current Password"
                    :type="isPasswordVisible ? 'text' : 'password'"
                    :append-inner-icon="isPasswordVisible ? 'ri-eye-off-line' : 'ri-eye-line'"
                    @click:append-inner="isPasswordVisible = !isPasswordVisible"
                    prepend-inner-icon="ri-lock-line"
                  />
                </VCol>

                <VCol cols="12">
                  <VTextField
                    v-model="passwordForm.newPassword"
                    label="New Password"
                    :type="isNewPasswordVisible ? 'text' : 'password'"
                    :append-inner-icon="isNewPasswordVisible ? 'ri-eye-off-line' : 'ri-eye-line'"
                    @click:append-inner="isNewPasswordVisible = !isNewPasswordVisible"
                    prepend-inner-icon="ri-lock-line"
                  />
                </VCol>

                <VCol cols="12">
                  <VTextField
                    v-model="passwordForm.confirmPassword"
                    label="Confirm New Password"
                    :type="isConfirmPasswordVisible ? 'text' : 'password'"
                    :append-inner-icon="isConfirmPasswordVisible ? 'ri-eye-off-line' : 'ri-eye-line'"
                    @click:append-inner="isConfirmPasswordVisible = !isConfirmPasswordVisible"
                    prepend-inner-icon="ri-lock-line"
                  />
                </VCol>

                <VCol cols="12">
                  <VBtn
                    type="submit"
                    color="primary"
                  >
                    <VIcon icon="ri-lock-line" class="me-2" />
                    Update Password
                  </VBtn>
                </VCol>
              </VRow>
            </VForm>
          </VCardText>
        </VWindowItem>

        <!-- Notifications Tab -->
        <VWindowItem value="notifications">
          <VCardText>
            <h2 class="text-h5 mb-4">Notification Preferences</h2>
            
            <VAlert type="info" class="mb-4">
              Notification preferences are not yet implemented. This feature will be available in a future update.
            </VAlert>
            
            <div class="text-center py-8">
              <VIcon icon="ri-notification-3-line" size="64" color="grey-lighten-1" />
              <p class="mt-4 text-grey-darken-1">
                Coming soon: Customize your notification preferences
              </p>
            </div>
          </VCardText>
        </VWindowItem>
      </VWindow>
    </VCard>
  </div>
</template>
