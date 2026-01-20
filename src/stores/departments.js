import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { departmentsService } from '@/services/departments'

export const useDepartmentStore = defineStore('departments', () => {
  // State
  const departments = ref([])
  const currentDepartment = ref(null)
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
    status: '',
  })

  // Computed
  const totalPages = computed(() => pagination.value.last_page)
  const hasMore = computed(() => pagination.value.page < pagination.value.last_page)

  // Methods
  const fetchDepartments = async (page = 1) => {
    loading.value = true
    error.value = null
    try {
      const { data } = await departmentsService.getAll(
        page,
        pagination.value.per_page,
        filters.value.search,
        filters.value.status
      )
      departments.value = data.data
      pagination.value = {
        page: data.current_page,
        per_page: data.per_page,
        total: data.total,
        last_page: data.last_page,
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch departments'
      console.error('Fetch departments error:', err)
    } finally {
      loading.value = false
    }
  }

  const getDepartmentById = async (id) => {
    loading.value = true
    error.value = null
    try {
      const { data } = await departmentsService.getById(id)
      currentDepartment.value = data.data
      return data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch department'
      console.error('Get department error:', err)
    } finally {
      loading.value = false
    }
  }

  const createDepartment = async (formData) => {
    loading.value = true
    error.value = null
    try {
      const { data } = await departmentsService.create(formData)
      departments.value.unshift(data.data)
      return data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to create department'
      console.error('Create department error:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const updateDepartment = async (id, formData) => {
    loading.value = true
    error.value = null
    try {
      const { data } = await departmentsService.update(id, formData)
      const index = departments.value.findIndex(d => d.id === id)
      if (index !== -1) {
        departments.value[index] = data.data
      }
      currentDepartment.value = data.data
      return data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to update department'
      console.error('Update department error:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const deleteDepartment = async (id) => {
    loading.value = true
    error.value = null
    try {
      await departmentsService.delete(id)
      departments.value = departments.value.filter(d => d.id !== id)
      if (currentDepartment.value?.id === id) {
        currentDepartment.value = null
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to delete department'
      console.error('Delete department error:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const setFilters = (newFilters) => {
    filters.value = { ...filters.value, ...newFilters }
    pagination.value.page = 1
    fetchDepartments(1)
  }

  const clearFilters = () => {
    filters.value = { search: '', status: '' }
    pagination.value.page = 1
    fetchDepartments(1)
  }

  const goToPage = (page) => {
    pagination.value.page = page
    fetchDepartments(page)
  }

  return {
    // State
    departments,
    currentDepartment,
    loading,
    error,
    pagination,
    filters,
    
    // Computed
    totalPages,
    hasMore,
    
    // Methods
    fetchDepartments,
    getDepartmentById,
    createDepartment,
    updateDepartment,
    deleteDepartment,
    setFilters,
    clearFilters,
    goToPage,
  }
})
