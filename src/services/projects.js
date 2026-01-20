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

export const projectsService = {
  // Get all projects with pagination and filtering
  getAll(page = 1, limit = 10, search = '', departmentId = '', status = '') {
    const params = new URLSearchParams()
    params.append('page', page)
    params.append('per_page', limit)
    if (search) params.append('search', search)
    if (departmentId) params.append('department_id', departmentId)
    if (status) params.append('status', status)
    
    return api.get(`/projects?${params.toString()}`)
  },

  // Get single project by ID
  getById(id) {
    return api.get(`/projects/${id}`)
  },

  // Create new project
  create(data) {
    return api.post('/projects', data)
  },

  // Update project
  update(id, data) {
    return api.put(`/projects/${id}`, data)
  },

  // Delete project
  delete(id) {
    return api.delete(`/projects/${id}`)
  },
}
