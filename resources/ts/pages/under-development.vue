<script setup lang="ts">
import { useRouter } from 'vue-router'
import { onMounted, ref } from 'vue'

const router = useRouter()
const isLoading = ref(true)

onMounted(() => {
  setTimeout(() => {
    isLoading.value = false
  }, 1000)
})

const goBack = () => {
  router.push('/dashboard')
}
</script>

<template>
  <div class="under-development-wrapper d-flex align-center justify-center pa-4">
    <VCard
      class="under-development-card text-center pa-8"
      max-width="600"
      elevation="8"
    >
      <!-- Animated Icon -->
      <div class="animated-icon mb-6">
        <div class="construction-container">
          <VIcon
            icon="ri-tools-line"
            size="80"
            color="warning"
            class="tool-icon tool-1"
          />
          <VIcon
            icon="ri-hammer-line"
            size="60"
            color="info"
            class="tool-icon tool-2"
          />
          <VIcon
            icon="ri-settings-3-line"
            size="70"
            color="success"
            class="tool-icon tool-3"
          />
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="loading-state">
        <VProgressCircular
          indeterminate
          color="primary"
          size="50"
          class="mb-4"
        />
        <h2 class="text-h4 mb-2">Memuat...</h2>
      </div>

      <!-- Main Content -->
      <div v-else class="main-content">
        <h1 class="text-h3 font-weight-bold mb-4 text-primary">
          🚧 Halaman dalam Pengembangan
        </h1>
        
        <p class="text-h6 mb-6 text-medium-emphasis">
          Halaman yang Anda tuju sedang dalam proses pengembangan.
        </p>
        
        <p class="text-body-1 mb-8 text-high-emphasis">
          Kami sedang bekerja keras untuk memberikan pengalaman terbaik untuk Anda. 
          Halaman ini akan segera tersedia dengan fitur-fitur menarik!
        </p>

        <!-- Animated Progress -->
        <div class="progress-section mb-8">
          <div class="text-caption mb-2">Progress Pengembangan</div>
          <VProgressLinear
            model-value="75"
            color="primary"
            height="8"
            rounded
            striped
          >
            <template #default="{ value }">
              <strong>{{ Math.ceil(value) }}%</strong>
            </template>
          </VProgressLinear>
        </div>

        <!-- Feature List -->
        <div class="features-list mb-8">
          <h3 class="text-h6 mb-4">Fitur yang akan datang:</h3>
          <div class="d-flex flex-column gap-2">
            <div class="feature-item d-flex align-center">
              <VIcon icon="ri-checkbox-circle-line" color="success" class="me-2" />
              <span>Dashboard interaktif</span>
            </div>
            <div class="feature-item d-flex align-center">
              <VIcon icon="ri-checkbox-circle-line" color="success" class="me-2" />
              <span>Analisis data real-time</span>
            </div>
            <div class="feature-item d-flex align-center">
              <VIcon icon="ri-time-line" color="warning" class="me-2" />
              <span>Integrasi API lanjutan</span>
            </div>
            <div class="feature-item d-flex align-center">
              <VIcon icon="ri-time-line" color="warning" class="me-2" />
              <span>Report otomatis</span>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons d-flex gap-4 justify-center">
          <VBtn
            color="primary"
            variant="elevated"
            @click="goBack"
            prepend-icon="ri-arrow-left-line"
          >
            Kembali ke Dashboard
          </VBtn>
          
          <VBtn
            color="info"
            variant="outlined"
            prepend-icon="ri-notification-line"
            @click="$router.push('/account-settings')"
          >
            Notifikasi Saya
          </VBtn>
        </div>

        <!-- Contact Info -->
        <VDivider class="my-6" />
        
        <div class="contact-info">
          <p class="text-caption text-medium-emphasis mb-2">
            Butuh bantuan? Hubungi tim pengembang kami:
          </p>
          <div class="d-flex gap-4 justify-center">
            <VBtn
              icon="ri-mail-line"
              size="small"
              variant="text"
              color="primary"
            />
            <VBtn
              icon="ri-message-3-line"
              size="small"
              variant="text"
              color="primary"
            />
            <VBtn
              icon="ri-phone-line"
              size="small"
              variant="text"
              color="primary"
            />
          </div>
        </div>
      </div>
    </VCard>

    <!-- Floating Elements Animation -->
    <div class="floating-elements">
      <div class="floating-element element-1"></div>
      <div class="floating-element element-2"></div>
      <div class="floating-element element-3"></div>
      <div class="floating-element element-4"></div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.under-development-wrapper {
  min-height: 100vh;
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
  position: relative;
  overflow: hidden;
}

.under-development-card {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-radius: 20px;
  position: relative;
  z-index: 10;
  animation: slideUp 0.6s ease-out;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.construction-container {
  position: relative;
  height: 120px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.tool-icon {
  position: absolute;
  animation: float 3s ease-in-out infinite;
}

.tool-1 {
  animation-delay: 0s;
  left: -30px;
}

.tool-2 {
  animation-delay: 0.5s;
  right: -20px;
}

.tool-3 {
  animation-delay: 1s;
  bottom: -20px;
}

@keyframes float {
  0%, 100% {
    transform: translateY(0px) rotate(0deg);
  }
  50% {
    transform: translateY(-10px) rotate(5deg);
  }
}

.loading-state {
  animation: fadeIn 0.5s ease-in;
}

.main-content {
  animation: fadeIn 0.8s ease-in;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

.feature-item {
  transition: all 0.3s ease;
  
  &:hover {
    transform: translateX(5px);
  }
}

.action-buttons {
  .v-btn {
    transition: all 0.3s ease;
    
    &:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }
  }
}

.floating-elements {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
  z-index: 1;
}

.floating-element {
  position: absolute;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.1);
  animation: float-around 15s infinite ease-in-out;
}

.element-1 {
  width: 80px;
  height: 80px;
  top: 10%;
  left: 10%;
  animation-delay: 0s;
}

.element-2 {
  width: 60px;
  height: 60px;
  top: 20%;
  right: 15%;
  animation-delay: 2s;
}

.element-3 {
  width: 100px;
  height: 100px;
  bottom: 20%;
  left: 20%;
  animation-delay: 4s;
}

.element-4 {
  width: 40px;
  height: 40px;
  bottom: 30%;
  right: 10%;
  animation-delay: 6s;
}

@keyframes float-around {
  0%, 100% {
    transform: translate(0, 0) rotate(0deg);
  }
  25% {
    transform: translate(30px, -30px) rotate(90deg);
  }
  50% {
    transform: translate(-20px, 20px) rotate(180deg);
  }
  75% {
    transform: translate(-30px, -10px) rotate(270deg);
  }
}

.progress-section {
  .v-progress-linear {
    animation: progress-grow 2s ease-out;
  }
}

@keyframes progress-grow {
  from {
    width: 0;
  }
  to {
    width: 100%;
  }
}

// Dark theme adjustments
.v-theme--dark {
  .under-development-wrapper {
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
  }
  
  .under-development-card {
    background: rgba(30, 30, 30, 0.95);
  }
}

// Responsive design
@media (max-width: 600px) {
  .under-development-card {
    margin: 16px;
    padding: 16px;
  }
  
  .construction-container {
    height: 80px;
  }
  
  .tool-icon {
    transform: scale(0.8);
  }
  
  .action-buttons {
    flex-direction: column;
    
    .v-btn {
      width: 100%;
    }
  }
}
</style>
