<script setup lang="ts">
import { useTheme } from 'vuetify'
import { useRouter } from 'vue-router'
import { useAuth } from '@/composables/useAuth'

import logo from '@images/logo.svg?raw'
import authV1MaskDark from '@images/pages/auth-v1-mask-dark.png'
import authV1MaskLight from '@images/pages/auth-v1-mask-light.png'
import authV1Tree2 from '@images/pages/auth-v1-tree-2.png'
import authV1Tree from '@images/pages/auth-v1-tree.png'

const router = useRouter()
const theme = useTheme()
const { login, isLoading } = useAuth()

const form = ref({
  email: '',
  password: '',
  remember: false,
})

const errorMessage = ref('')

const vuetifyTheme = useTheme()

const authThemeMask = computed(() => {
  return vuetifyTheme.global.name.value === 'light'
    ? authV1MaskLight
    : authV1MaskDark
})

const isPasswordVisible = ref(false)

const handleLogin = async () => {
  try {
    errorMessage.value = ''

    // Validate form
    if (!form.value.email || !form.value.password) {
      errorMessage.value = 'Email dan password harus diisi'
      return
    }

    // Attempt login
    const result = await login(form.value.email, form.value.password)

    if (result.success) {
      // Login successful, redirect to dashboard
      await router.push('/dashboard')
    } else {
      // Login failed, show error message
      errorMessage.value = result.message || 'Login gagal. Periksa kembali email dan password Anda.'
    }
  } catch (error) {
    console.error('Login error:', error)
    errorMessage.value = 'Terjadi kesalahan. Silakan coba lagi nanti.'
  }
}
</script>

<template>
  <!-- eslint-disable vue/no-v-html -->

  <div class="auth-wrapper d-flex align-center justify-center pa-4">
    <VCard
      class="auth-card pa-4 pt-7"
      max-width="448"
    >
      <VCardItem class="justify-center">
        <RouterLink
          to="/"
          class="d-flex align-center gap-3"
        >
          <!-- eslint-disable vue/no-v-html -->
          <div
            class="d-flex"
            v-html="logo"
          />
          <h2 class="font-weight-medium text-2xl text-uppercase" :style="theme.global.current.value.dark ? 'color: #ffffff' : 'color: #1e40af'">
              Admin JSC
          </h2>
        </RouterLink>
      </VCardItem>

      <VCardText class="pt-2">
        <h4 class="text-h4 mb-1">
          Welcome to TIM JSC! 👋🏻
        </h4>
        <p class="mb-0">
          Silakan masuk ke akun dan mulai mengelola data.
        </p>
      </VCardText>

      <VCardText>
        <!-- Error Message -->
        <VAlert
          v-if="errorMessage"
          type="error"
          variant="tonal"
          class="mb-4"
        >
          {{ errorMessage }}
        </VAlert>

        <VForm @submit.prevent="handleLogin">
          <VRow>
            <!-- email -->
            <VCol cols="12">
              <VTextField
                v-model="form.email"
                label="Email"
                type="email"
                :disabled="isLoading"
                :error-messages="errorMessage && !form.email ? ['Email harus diisi'] : []"
              />
            </VCol>

            <!-- password -->
            <VCol cols="12">
              <VTextField
                v-model="form.password"
                label="Password"
                placeholder="············"
                :type="isPasswordVisible ? 'text' : 'password'"
                autocomplete="password"
                :disabled="isLoading"
                :append-inner-icon="isPasswordVisible ? 'ri-eye-off-line' : 'ri-eye-line'"
                @click:append-inner="isPasswordVisible = !isPasswordVisible"
                :error-messages="errorMessage && !form.password ? ['Password harus diisi'] : []"
              />

              <!-- remember me checkbox -->
              <div class="d-flex align-center justify-space-between flex-wrap my-6">
                <VCheckbox
                  v-model="form.remember"
                  label="Remember me"
                  :disabled="isLoading"
                />

                <a
                  class="text-primary"
                  href="javascript:void(0)"
                >
                  Lupa Password?
                </a>
              </div>

              <!-- login button -->
              <VBtn
                block
                type="submit"
                :loading="isLoading"
                :disabled="isLoading"
              >
                {{ isLoading ? 'Logging in...' : 'Login' }}
              </VBtn>
            </VCol>

            <!-- create account -->
            

            <VCol
              cols="12"
              class="d-flex align-center"
            >
            
            </VCol>

                      </VRow>
        </VForm>
      </VCardText>
    </VCard>

    <VImg
      class="auth-footer-start-tree d-none d-md-block"
      :src="authV1Tree"
      :width="250"
    />

    <VImg
      :src="authV1Tree2"
      class="auth-footer-end-tree d-none d-md-block"
      :width="350"
    />

    <!-- bg img -->
    <VImg
      class="auth-footer-mask d-none d-md-block"
      :src="authThemeMask"
    />
  </div>
</template>

<style lang="scss">
@use "@core-scss/template/pages/page-auth";
</style>
