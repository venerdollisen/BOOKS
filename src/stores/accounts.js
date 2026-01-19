import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '../config/api'

export const useAccountStore = defineStore('account', () => {
  // State
  const accounts = ref([])
  const loading = ref(false)
  const error = ref(null)
  
  // Pagination state
  const pagination = ref({
    total: 0,
    per_page: 15,
    current_page: 1,
    last_page: 1,
    from: 1,
    to: 0,
  })
  
  // Filter state
  const searchQuery = ref('')
  const selectedAccountType = ref('')
  const sortBy = ref('account_type')
  const sortOrder = ref('asc')

  // Computed
  const hasNextPage = computed(() => pagination.value.current_page < pagination.value.last_page)
  const hasPrevPage = computed(() => pagination.value.current_page > 1)
  const totalPages = computed(() => pagination.value.last_page)
  const pageInfo = computed(() => {
    const { from, to, total } = pagination.value
    if (total === 0) return 'No results'
    return `Showing ${from} to ${to} of ${total}`
  })

  // Actions
  const fetchAccounts = async (page = 1) => {
    loading.value = true
    error.value = null
    try {
      const params = {
        page,
        per_page: pagination.value.per_page,
        sort_by: sortBy.value,
        sort_order: sortOrder.value,
      }
      
      if (searchQuery.value) {
        params.search = searchQuery.value
      }
      
      if (selectedAccountType.value) {
        params.account_type = selectedAccountType.value
      }

      const response = await api.get('/accounts', { params })
      accounts.value = response.data.data
      pagination.value = response.data.pagination
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch accounts'
      accounts.value = []
    } finally {
      loading.value = false
    }
  }

  const goToPage = async (page) => {
    pagination.value.current_page = page
    await fetchAccounts(page)
  }

  const nextPage = async () => {
    if (hasNextPage.value) {
      await goToPage(pagination.value.current_page + 1)
    }
  }

  const prevPage = async () => {
    if (hasPrevPage.value) {
      await goToPage(pagination.value.current_page - 1)
    }
  }

  const setPerPage = async (perPage) => {
    pagination.value.per_page = perPage
    pagination.value.current_page = 1
    await fetchAccounts(1)
  }

  const setSearchQuery = async (query) => {
    searchQuery.value = query
    pagination.value.current_page = 1
    await fetchAccounts(1)
  }

  const setSelectedAccountType = async (type) => {
    selectedAccountType.value = type
    pagination.value.current_page = 1
    await fetchAccounts(1)
  }

  const setSorting = async (column, order) => {
    sortBy.value = column
    sortOrder.value = order
    pagination.value.current_page = 1
    await fetchAccounts(1)
  }

  const toggleSort = async (column) => {
    if (sortBy.value === column) {
      sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
    } else {
      sortBy.value = column
      sortOrder.value = 'asc'
    }
    await fetchAccounts(1)
  }

  const getAccount = async (id) => {
    try {
      const response = await api.get(`/accounts/${id}`)
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch account'
      throw err
    }
  }

  const createAccount = async (data) => {
    try {
      const response = await api.post('/accounts', data)
      // Refresh current page to show new account
      await fetchAccounts(pagination.value.current_page)
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to create account'
      throw err
    }
  }

  const updateAccount = async (id, data) => {
    try {
      const response = await api.put(`/accounts/${id}`, data)
      // Refresh current page to show updated account
      await fetchAccounts(pagination.value.current_page)
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to update account'
      throw err
    }
  }

  const deleteAccount = async (id) => {
    try {
      await api.delete(`/accounts/${id}`)
      // Refresh current page (or go back one page if we're on the last item of last page)
      if (accounts.value.length === 1 && pagination.value.current_page > 1) {
        await fetchAccounts(pagination.value.current_page - 1)
      } else {
        await fetchAccounts(pagination.value.current_page)
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to delete account'
      throw err
    }
  }

  return {
    // State
    accounts,
    loading,
    error,
    pagination,
    searchQuery,
    selectedAccountType,
    sortBy,
    sortOrder,
    
    // Computed
    hasNextPage,
    hasPrevPage,
    totalPages,
    pageInfo,
    
    // Methods
    fetchAccounts,
    goToPage,
    nextPage,
    prevPage,
    setPerPage,
    setSearchQuery,
    setSelectedAccountType,
    setSorting,
    toggleSort,
    getAccount,
    createAccount,
    updateAccount,
    deleteAccount,
  }
})
