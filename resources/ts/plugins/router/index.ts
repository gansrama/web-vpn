import type { App } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import { routes } from './routes'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})

// Navigation guards
router.beforeEach(async (to, from, next) => {
  const publicPages = ['/login', '/register', '/landing', '/under-development']
  const authRequired = !publicPages.includes(to.path)
  
  // Check if user is authenticated
  const isAuthenticated = localStorage.getItem('isAuthenticated') === 'true'
  
  if (authRequired && !isAuthenticated) {
    // Redirect to login page if not authenticated
    next('/login')
  } else if (to.path === '/login' && isAuthenticated) {
    // Redirect to dashboard if already authenticated and trying to access login
    next('/dashboard')
  } else if (to.path === '/' && isAuthenticated) {
    // If authenticated and accessing root, redirect to dashboard
    next('/dashboard')
  } else {
    next()
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
