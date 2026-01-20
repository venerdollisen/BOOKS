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

export const departmentsService = {
  // Get all departments with pagination and filtering
  getAll(page = 1, limit = 10, search = '', status = '') {
    const params = new URLSearchParams()
    params.append('page', page)
    params.append('per_page', limit)
    if (search) params.append('search', search)
    if (status) params.append('status', status)
    
    return api.get(`/departments?${params.toString()}`)
  },

  // Get single department by ID
  getById(id) {
    return api.get(`/departments/${id}`)
  },

  // Create new department
  create(data) {
    return api.post('/departments', data)
  },

  // Update department
  update(id, data) {
    return api.put(`/departments/${id}`, data)
  },

  // Delete department
  delete(id) {
    return api.delete(`/departments/${id}`)
  },
}
