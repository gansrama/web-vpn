import type { App } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import { routes } from './routes'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})

// Cache for authentication status to avoid repeated API calls
let authCache: { authenticated: boolean; timestamp: number } | null = null
const AUTH_CACHE_DURATION = 5 * 60 * 1000 // 5 minutes

// Navigation guards
router.beforeEach((to, from, next) => {
  const isAuthenticated = localStorage.getItem('isAuthenticated') === 'true'
  const requiresAuth = to.meta.requiresAuth !== false // Default to true if not specified
  
  if (requiresAuth && !isAuthenticated) {
    // Redirect to login page if not authenticated
    next('/login')
  } else if (to.path === '/login' && isAuthenticated) {
    // Redirect to dashboard if already authenticated and trying to access login
    next('/dashboard')
  } else if (to.path === '/' && isAuthenticated) {
    // If authenticated and accessing root, redirect to dashboard
    next('/dashboard')
  } else {
    // Continue to destination
    next()
  }
})

// Verify auth in background after navigation completes (non-blocking)
router.afterEach((to) => {
  const isAuthenticated = localStorage.getItem('isAuthenticated') === 'true'
  const requiresAuth = to.meta.requiresAuth !== false
  
  if (isAuthenticated && requiresAuth && (!authCache || Date.now() - authCache.timestamp > AUTH_CACHE_DURATION)) {
    // Verify auth in background without blocking navigation
    checkAuth().then((serverAuth) => {
      authCache = { authenticated: serverAuth, timestamp: Date.now() }
      if (!serverAuth) {
        localStorage.setItem('isAuthenticated', 'false')
        // Redirect to login if auth failed
        if (router.currentRoute.value.path !== '/login') {
          router.push('/login')
        }
      }
    }).catch((error) => {
      console.error('Background auth check failed:', error)
    })
  }
})

// Handle navigation errors
router.onError((error) => {
  console.error('Router navigation error:', error)
  // Redirect to error page or home
  if (error.name === 'ChunkLoadError') {
    // Chunk loading failed - try reloading
    window.location.reload()
  }
})

// Function to check authentication status
async function checkAuth(): Promise<boolean> {
  try {
    const response = await fetch('/api/check-auth', {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
      },
    })
    
    if (response.ok) {
      const data = await response.json()
      return data.success && data.authenticated
    }
    return false
  } catch (error) {
    console.error('Auth check failed:', error)
    return false
  }
}

export default function (app: App) {
  app.use(router)
}

export { router }
