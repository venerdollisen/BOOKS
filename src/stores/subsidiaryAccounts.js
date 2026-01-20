import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { subsidiaryAccountsService } from '@/services/subsidiaryAccounts'

export const useSubsidiaryAccountStore = defineStore('subsidiaryAccounts', () => {
  // State
  const subsidiaryAccounts = ref([])
  const currentSubsidiaryAccount = ref(null)
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
    account_id: '',
    type: '',
    status: '',
  })

  // Computed
  const totalPages = computed(() => pagination.value.last_page)
  const hasMore = computed(() => pagination.value.page < pagination.value.last_page)

  // Methods
  const fetchSubsidiaryAccounts = async (page = 1) => {
    loading.value = true
    error.value = null
    try {
      const { data } = await subsidiaryAccountsService.getAll(
        page,
        pagination.value.per_page,
        filters.value.search,
        filters.value.account_id,
        filters.value.type,
        filters.value.status
      )
      subsidiaryAccounts.value = data.data
      pagination.value = {
        page: data.current_page,
        per_page: data.per_page,
        total: data.total,
        last_page: data.last_page,
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch subsidiary accounts'
      console.error('Fetch subsidiary accounts error:', err)
    } finally {
      loading.value = false
    }
  }

  const getSubsidiaryAccountById = async (id) => {
    loading.value = true
    error.value = null
    try {
      const { data } = await subsidiaryAccountsService.getById(id)
      currentSubsidiaryAccount.value = data.data
      return data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch subsidiary account'
      console.error('Get subsidiary account error:', err)
    } finally {
      loading.value = false
    }
  }

  const createSubsidiaryAccount = async (formData) => {
    loading.value = true
    error.value = null
    try {
      const { data } = await subsidiaryAccountsService.create(formData)
      subsidiaryAccounts.value.unshift(data.data)
      return data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to create subsidiary account'
      console.error('Create subsidiary account error:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const updateSubsidiaryAccount = async (id, formData) => {
    loading.value = true
    error.value = null
    try {
      const { data } = await subsidiaryAccountsService.update(id, formData)
      const index = subsidiaryAccounts.value.findIndex(sa => sa.id === id)
      if (index !== -1) {
        subsidiaryAccounts.value[index] = data.data
      }
      currentSubsidiaryAccount.value = data.data
      return data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to update subsidiary account'
      console.error('Update subsidiary account error:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const deleteSubsidiaryAccount = async (id) => {
    loading.value = true
    error.value = null
    try {
      await subsidiaryAccountsService.delete(id)
      subsidiaryAccounts.value = subsidiaryAccounts.value.filter(sa => sa.id !== id)
      if (currentSubsidiaryAccount.value?.id === id) {
        currentSubsidiaryAccount.value = null
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to delete subsidiary account'
      console.error('Delete subsidiary account error:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const setFilters = (newFilters) => {
    filters.value = { ...filters.value, ...newFilters }
    pagination.value.page = 1
    fetchSubsidiaryAccounts(1)
  }

  const clearFilters = () => {
    filters.value = { search: '', account_id: '', type: '', status: '' }
    pagination.value.page = 1
    fetchSubsidiaryAccounts(1)
  }

  const goToPage = (page) => {
    pagination.value.page = page
    fetchSubsidiaryAccounts(page)
  }

  return {
    // State
    subsidiaryAccounts,
    currentSubsidiaryAccount,
    loading,
    error,
    pagination,
    filters,
    
    // Computed
    totalPages,
    hasMore,
    
    // Methods
    fetchSubsidiaryAccounts,
    getSubsidiaryAccountById,
    createSubsidiaryAccount,
    updateSubsidiaryAccount,
    deleteSubsidiaryAccount,
    setFilters,
    clearFilters,
    goToPage,
  }
})
