import axios from 'axios'

// API Configuration
// Sanctum authentication endpoints for Laravel

export const API_CONFIG = {
  // Authentication endpoints - using Laravel Sanctum
  auth: {
    login: '/auth/login',           // POST - Login with email/password
    logout: '/auth/logout',         // POST - Logout (protected)
    user: '/auth/user',             // GET - Get current user (protected)
    register: '/auth/register',     // POST - Register new user
  },
  
  // Base API URL - set in environment variables
  baseURL: import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api',
}

// Create axios instance with default config
const api = axios.create({
  baseURL: API_CONFIG.baseURL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  withCredentials: true, // Send cookies with requests (for Sanctum)
})

// Add request interceptor to include auth token
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => Promise.reject(error)
)

// Add response interceptor to handle auth errors
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Clear auth and redirect to login
      localStorage.removeItem('auth_token')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

export default api

