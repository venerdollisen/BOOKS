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

