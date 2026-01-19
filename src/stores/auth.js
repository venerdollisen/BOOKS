import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import apiClient, { authApi } from '@/services/api'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('auth_token') || null)
  const loading = ref(false)
  const error = ref(null)

  const isAuthenticated = computed(() => !!token.value && !!user.value)

  // Initialize auth from token if exists
  const initAuth = async () => {
    const storedToken = localStorage.getItem('auth_token')
    if (storedToken && !token.value) {
      token.value = storedToken
      apiClient.defaults.headers.common['Authorization'] = `Bearer ${storedToken}`
      
      try {
        const response = await authApi.getUser()
        user.value = response.data.data || response.data.user
      } catch (err) {
        // Token is invalid, clear it
        console.error('Auth initialization failed:', err)
        logout()
      }
    }
  }

  const login = async (email, password) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await authApi.login({ email, password })
      
      const { token: newToken, user: userData } = response.data.data

      token.value = newToken
      user.value = userData

      // Store token in localStorage
      localStorage.setItem('auth_token', token.value)

      // Set authorization header for subsequent requests
      apiClient.defaults.headers.common['Authorization'] = `Bearer ${token.value}`

      return {
        token: newToken,
        user: userData,
      }
    } catch (err) {
      // Handle validation errors
      if (err.response?.status === 422) {
        error.value = err.response?.data?.message || 'Validation failed'
      } else if (err.response?.status === 401) {
        error.value = 'Invalid email or password'
      } else {
        error.value = err.response?.data?.message || err.message || 'Login failed'
      }
      throw err
    } finally {
      loading.value = false
    }
  }

  const register = async (name, email, password, passwordConfirmation) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await authApi.register({ 
        name, 
        email, 
        password,
        password_confirmation: passwordConfirmation,
      })
      
      const { token: newToken, user: userData } = response.data.data

      token.value = newToken
      user.value = userData

      // Store token in localStorage
      localStorage.setItem('auth_token', token.value)

      // Set authorization header for subsequent requests
      apiClient.defaults.headers.common['Authorization'] = `Bearer ${token.value}`

      return {
        token: newToken,
        user: userData,
      }
    } catch (err) {
      if (err.response?.status === 422) {
        error.value = err.response?.data?.message || 'Validation failed'
      } else {
        error.value = err.response?.data?.message || err.message || 'Registration failed'
      }
      throw err
    } finally {
      loading.value = false
    }
  }

  const logout = async () => {
    try {
      if (token.value) {
        // Call logout endpoint to invalidate token on backend
        await authApi.logout().catch(() => {
          // Ignore errors on logout endpoint
        })
      }
    } finally {
      user.value = null
      token.value = null
      error.value = null
      localStorage.removeItem('auth_token')
      delete apiClient.defaults.headers.common['Authorization']
      
      // Redirect to login
      window.location.href = '/login'
    }
  }

  const clearError = () => {
    error.value = null
  }

  return {
    user,
    token,
    loading,
    error,
    isAuthenticated,
    login,
    register,
    logout,
    initAuth,
    clearError,
  }
})

