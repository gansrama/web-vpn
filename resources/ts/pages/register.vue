<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const form = ref({
  name: '',
  email: '',
  password: '',
  confirmPassword: '',
  terms: false,
})

const isPasswordVisible = ref(false)
const isConfirmPasswordVisible = ref(false)
const isLoading = ref(false)

const handleRegister = async () => {
  try {
    isLoading.value = true
    
    // Validate form
    if (!form.value.name || !form.value.email || !form.value.password) {
      return
    }

    if (form.value.password !== form.value.confirmPassword) {
      return
    }

    if (!form.value.terms) {
      return
    }

    // Handle registration logic here
    console.log('Registration data:', form.value)
    
    // Redirect to login after successful registration
    await router.push('/login')
  } catch (error) {
    console.error('Registration error:', error)
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div class="auth-wrapper d-flex align-center justify-center pa-4">
    <VCard
      class="auth-card pa-4 pt-7"
      max-width="448"
    >
      <VCardItem class="justify-center">
        <h2 class="font-weight-medium text-2xl text-uppercase">
          TIM DEV
        </h2>
      </VCardItem>

      <VCardText class="pt-2">
        <h4 class="text-h4 mb-1">
          Adventure starts here 🚀
        </h4>
        <p class="mb-0">
          Make your app management easy and fun!
        </p>
      </VCardText>

      <VCardText>
        <VForm @submit.prevent="handleRegister">
          <VRow>
            <VCol cols="12">
              <VTextField
                v-model="form.name"
                label="Name"
                placeholder="John Doe"
                :disabled="isLoading"
              />
            </VCol>

            <VCol cols="12">
              <VTextField
                v-model="form.email"
                label="Email"
                type="email"
                placeholder="johndoe@example.com"
                :disabled="isLoading"
              />
            </VCol>

            <VCol cols="12">
              <VTextField
                v-model="form.password"
                label="Password"
                placeholder="············"
                :type="isPasswordVisible ? 'text' : 'password'"
                :append-inner-icon="isPasswordVisible ? 'ri-eye-off-line' : 'ri-eye-line'"
                @click:append-inner="isPasswordVisible = !isPasswordVisible"
                :disabled="isLoading"
              />
            </VCol>

            <VCol cols="12">
              <VTextField
                v-model="form.confirmPassword"
                label="Confirm Password"
                placeholder="············"
                :type="isConfirmPasswordVisible ? 'text' : 'password'"
                :append-inner-icon="isConfirmPasswordVisible ? 'ri-eye-off-line' : 'ri-eye-line'"
                @click:append-inner="isConfirmPasswordVisible = !isConfirmPasswordVisible"
                :disabled="isLoading"
              />
            </VCol>

            <VCol cols="12">
              <VCheckbox
                v-model="form.terms"
                :disabled="isLoading"
              >
                <template #label>
                  <span class="text-sm">
                    I agree to
                    <a
                      class="text-primary"
                      href="javascript:void(0)"
                    >privacy policy & terms</a>
                  </span>
                </template>
              </VCheckbox>
            </VCol>

            <VCol cols="12">
              <VBtn
                block
                type="submit"
                :loading="isLoading"
                :disabled="isLoading"
              >
                Sign up
              </VBtn>
            </VCol>

            <VCol cols="12" class="text-center">
              <span>Already have an account?</span>
              <RouterLink
                class="text-primary ms-1"
                to="/login"
              >
                Sign in instead
              </RouterLink>
            </VCol>
          </VRow>
        </VForm>
      </VCardText>
    </VCard>
  </div>
</template>
