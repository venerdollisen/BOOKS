import axios from 'axios'

const API_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api'

const api = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  withCredentials: true,
})

// Add auth token to requests
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

export const subsidiaryAccountsService = {
  // Get all subsidiary accounts with pagination and filtering
  getAll(page = 1, limit = 10, search = '', accountId = '', type = '', status = '') {
    const params = new URLSearchParams()
    params.append('page', page)
    params.append('per_page', limit)
    if (search) params.append('search', search)
    if (accountId) params.append('account_id', accountId)
    if (type) params.append('type', type)
    if (status) params.append('status', status)
    
    return api.get(`/subsidiary-accounts?${params.toString()}`)
  },

  // Get single subsidiary account by ID
  getById(id) {
    return api.get(`/subsidiary-accounts/${id}`)
  },

  // Create new subsidiary account
  create(data) {
    return api.post('/subsidiary-accounts', data)
  },

  // Update subsidiary account
  update(id, data) {
    return api.put(`/subsidiary-accounts/${id}`, data)
  },

  // Delete subsidiary account
  delete(id) {
    return api.delete(`/subsidiary-accounts/${id}`)
  },
}
