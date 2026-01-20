import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { projectsService } from '@/services/projects'

export const useProjectStore = defineStore('projects', () => {
  // State
  const projects = ref([])
  const currentProject = ref(null)
  const loading = ref(false)
  const error = ref(null)
  const pagination = ref({
    page: 1,
    per_page: 10,
    total: 0,
    last_page: 1,
  })
  const filters = ref({
    search: '',
    department_id: '',
    status: '',
  })

  // Computed
  const totalPages = computed(() => pagination.value.last_page)
  const hasMore = computed(() => pagination.value.page < pagination.value.last_page)

  // Methods
  const fetchProjects = async (page = 1) => {
    loading.value = true
    error.value = null
    try {
      const { data } = await projectsService.getAll(
        page,
        pagination.value.per_page,
        filters.value.search,
        filters.value.department_id,
        filters.value.status
      )
      projects.value = data.data
      pagination.value = {
        page: data.current_page,
        per_page: data.per_page,
        total: data.total,
        last_page: data.last_page,
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch projects'
      console.error('Fetch projects error:', err)
    } finally {
      loading.value = false
    }
  }

  const getProjectById = async (id) => {
    loading.value = true
    error.value = null
    try {
      const { data } = await projectsService.getById(id)
      currentProject.value = data.data
      return data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch project'
      console.error('Get project error:', err)
    } finally {
      loading.value = false
    }
  }

  const createProject = async (formData) => {
    loading.value = true
    error.value = null
    try {
      const { data } = await projectsService.create(formData)
      projects.value.unshift(data.data)
      return data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to create project'
      console.error('Create project error:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const updateProject = async (id, formData) => {
    loading.value = true
    error.value = null
    try {
      const { data } = await projectsService.update(id, formData)
      const index = projects.value.findIndex(p => p.id === id)
      if (index !== -1) {
        projects.value[index] = data.data
      }
      currentProject.value = data.data
      return data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to update project'
      console.error('Update project error:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const deleteProject = async (id) => {
    loading.value = true
    error.value = null
    try {
      await projectsService.delete(id)
      projects.value = projects.value.filter(p => p.id !== id)
      if (currentProject.value?.id === id) {
        currentProject.value = null
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to delete project'
      console.error('Delete project error:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const setFilters = (newFilters) => {
    filters.value = { ...filters.value, ...newFilters }
    pagination.value.page = 1
    fetchProjects(1)
  }

  const clearFilters = () => {
    filters.value = { search: '', department_id: '', status: '' }
    pagination.value.page = 1
    fetchProjects(1)
  }

  const goToPage = (page) => {
    pagination.value.page = page
    fetchProjects(page)
  }

  return {
    // State
    projects,
    currentProject,
    loading,
    error,
    pagination,
    filters,
    
    // Computed
    totalPages,
    hasMore,
    
    // Methods
    fetchProjects,
    getProjectById,
    createProject,
    updateProject,
    deleteProject,
    setFilters,
    clearFilters,
    goToPage,
  }
})
