import { ref, computed, inject } from 'vue'
import { useRouter } from 'vue-router'

export interface User {
  id: number
  name: string
  email: string
  avatar?: string
}

export interface AuthState {
  user: User | null
  isAuthenticated: boolean
  isLoading: boolean
}

const authState = ref<AuthState>({
  user: null,
  isAuthenticated: false,
  isLoading: false,
})

export function useAuth() {
  const csrfToken = inject<string>('csrfToken')
  const router = useRouter()
  
  const login = async (email: string, password: string) => {
    authState.value.isLoading = true
    
    try {
      const response = await fetch('/api/login', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({ email, password })
      })

      const result = await response.json()
      
      if (response.ok && result.success) {
        const userData = result.user
        authState.value.user = userData
        authState.value.isAuthenticated = true
        
        // Save to localStorage for router guard
        localStorage.setItem('isAuthenticated', 'true')
        localStorage.setItem('user', JSON.stringify(userData))
        
        // Save avatar separately for persistence
        if (userData.avatar) {
          localStorage.setItem('userAvatar', userData.avatar)
        }
        
        return { success: true, user: userData }
      } else {
        return { success: false, message: result.message || 'Login failed' }
      }
    } catch (error) {
      console.error('Login error:', error)
      return { success: false, message: 'Network error occurred' }
    } finally {
      authState.value.isLoading = false
    }
  }
  
  const logout = async () => {
    try {
      const response = await fetch('/api/logout', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
      })

      console.log('Logout response:', response.status, response.statusText)
      
      // Clear auth state regardless of API response
      authState.value.user = null
      authState.value.isAuthenticated = false
      
      // Clear localStorage
      localStorage.removeItem('isAuthenticated')
      localStorage.removeItem('user')
      localStorage.removeItem('userAvatar')
      
      // Clear session cookies by setting expiration to past
      document.cookie.split(";").forEach(function(c) { 
        document.cookie = c.replace(/^ +/, "").replace(/=.*/, "=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/"); 
      });
      
      // Redirect to login page
      await router.push('/login')
      
    } catch (error) {
      console.error('Logout error:', error)
      // Still clear auth state and redirect on error
      authState.value.user = null
      authState.value.isAuthenticated = false
      localStorage.removeItem('isAuthenticated')
      localStorage.removeItem('user')
      localStorage.removeItem('userAvatar')
      
      // Clear all cookies
      document.cookie.split(";").forEach(function(c) { 
        document.cookie = c.replace(/^ +/, "").replace(/=.*/, "=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/"); 
      });
      
      await router.push('/login')
    }
  }
  
  const checkAuth = async () => {
    try {
      const response = await fetch('/api/check-auth', {
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
      })

      const result = await response.json()
      
      if (response.ok && result.success && result.authenticated && result.user) {
        authState.value.user = result.user
        authState.value.isAuthenticated = true
        
        // Update localStorage
        localStorage.setItem('isAuthenticated', 'true')
        localStorage.setItem('user', JSON.stringify(result.user))
        
        // Save avatar separately for persistence
        if (result.user.avatar) {
          localStorage.setItem('userAvatar', result.user.avatar)
        }
        
        return true
      } else {
        authState.value.user = null
        authState.value.isAuthenticated = false
        localStorage.removeItem('isAuthenticated')
        localStorage.removeItem('user')
        return false
      }
    } catch (error) {
      console.error('Auth check error:', error)
      authState.value.user = null
      authState.value.isAuthenticated = false
      localStorage.removeItem('isAuthenticated')
      localStorage.removeItem('user')
      return false
    }
  }
  
  const updateUser = (userData: User) => {
    authState.value.user = userData
  }
  
  return {
    authState: computed(() => authState.value),
    user: computed(() => authState.value.user),
    isAuthenticated: computed(() => authState.value.isAuthenticated),
    isLoading: computed(() => authState.value.isLoading),
    login,
    logout,
    checkAuth,
    updateUser,
  }
}
