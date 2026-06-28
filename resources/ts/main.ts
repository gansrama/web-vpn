import { createApp } from 'vue'

import App from '@/App.vue'
import { registerPlugins } from '@core/utils/plugins'

// Styles
import 'vuetify/styles'
import '@core-scss/template/index.scss'
import '@layouts/styles/index.scss'

// Add CSRF token to meta tag for JavaScript access
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')

// Create vue app
const app = createApp(App)

// Provide CSRF token globally
app.provide('csrfToken', csrfToken)

// Register plugins
registerPlugins(app)

// Mount vue app
app.mount('#app')

// Remove loading screen after app is mounted
const loadingBg = document.getElementById('loading-bg')
if (loadingBg) {
  loadingBg.style.display = 'none'
  setTimeout(() => {
    loadingBg.remove()
  }, 500)
}
