import { defineStore } from 'pinia';
import { ref, reactive } from 'vue';
import api from '@/config/api';

export const useTransactionStore = defineStore('transactions', () => {
  const transactions = ref([]);
  const currentTransaction = ref(null);
  const loading = ref(false);
  const error = ref(null);

  // Pagination state
  const pagination = reactive({
    total: 0,
    per_page: 20,
    current_page: 1,
    last_page: 1,
    from: 1,
    to: 0,
  });

  // Filter and sort state
  const filters = reactive({
    search: '',
    type: '',
    status: '',
    start_date: '',
    end_date: '',
  });

  const sorting = reactive({
    sort_by: 'transaction_date',
    sort_order: 'desc',
  });

  /**
   * Fetch transactions with current filters, sort, and pagination
   */
  async function fetchTransactions(page = 1) {
    loading.value = true;
    error.value = null;

    try {
      const params = {
        page,
        per_page: pagination.per_page,
        sort_by: sorting.sort_by,
        sort_order: sorting.sort_order,
        ...filters,
      };

      const response = await api.get('/transactions', { params });

      if (response.data.success) {
        transactions.value = response.data.data;
        Object.assign(pagination, response.data.pagination);
      } else {
        error.value = response.data.message || 'Failed to fetch transactions';
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.message || 'Error fetching transactions';
      transactions.value = [];
    } finally {
      loading.value = false;
    }
  }

  /**
   * Get a single transaction by ID
   */
  async function getTransaction(id) {
    loading.value = true;
    error.value = null;

    try {
      const response = await api.get(`/transactions/${id}`);

      if (response.data.success) {
        currentTransaction.value = response.data.data;
        return response.data.data;
      } else {
        error.value = response.data.message || 'Failed to fetch transaction';
        return null;
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.message || 'Error fetching transaction';
      return null;
    } finally {
      loading.value = false;
    }
  }

  /**
   * Create a new transaction
   */
  async function createTransaction(data) {
    loading.value = true;
    error.value = null;

    try {
      const response = await api.post('/transactions', data);

      if (response.data.success) {
        currentTransaction.value = response.data.data;
        // Refresh the list
        await fetchTransactions(pagination.current_page);
        return response.data.data;
      } else {
        error.value = response.data.message || 'Failed to create transaction';
        return null;
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.message || 'Error creating transaction';
      return null;
    } finally {
      loading.value = false;
    }
  }

  /**
   * Update an existing transaction
   */
  async function updateTransaction(id, data) {
    loading.value = true;
    error.value = null;

    try {
      const response = await api.put(`/transactions/${id}`, data);

      if (response.data.success) {
        currentTransaction.value = response.data.data;
        // Refresh the list
        await fetchTransactions(pagination.current_page);
        return response.data.data;
      } else {
        error.value = response.data.message || 'Failed to update transaction';
        return null;
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.message || 'Error updating transaction';
      return null;
    } finally {
      loading.value = false;
    }
  }

  /**
   * Delete a transaction
   */
  async function deleteTransaction(id) {
    loading.value = true;
    error.value = null;

    try {
      const response = await api.delete(`/transactions/${id}`);

      if (response.data.success) {
        currentTransaction.value = null;
        // Refresh the list
        await fetchTransactions(pagination.current_page);
        return true;
      } else {
        error.value = response.data.message || 'Failed to delete transaction';
        return false;
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.message || 'Error deleting transaction';
      return false;
    } finally {
      loading.value = false;
    }
  }

  /**
   * Approve a transaction (change status to approved)
   */
  async function approveTransaction(id) {
    loading.value = true;
    error.value = null;

    try {
      const response = await api.post(`/transactions/${id}/approve`);

      if (response.data.success) {
        currentTransaction.value = response.data.data;
        // Refresh the list
        await fetchTransactions(pagination.current_page);
        return true;
      } else {
        error.value = response.data.message || 'Failed to approve transaction';
        return false;
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.message || 'Error approving transaction';
      return false;
    } finally {
      loading.value = false;
    }
  }

  /**
   * Reject a transaction
   */
  async function rejectTransaction(id, reason) {
    loading.value = true;
    error.value = null;

    try {
      const response = await api.post(`/transactions/${id}/reject`, { reason });

      if (response.data.success) {
        currentTransaction.value = response.data.data;
        // Refresh the list
        await fetchTransactions(pagination.current_page);
        return true;
      } else {
        error.value = response.data.message || 'Failed to reject transaction';
        return false;
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.message || 'Error rejecting transaction';
      return false;
    } finally {
      loading.value = false;
    }
  }

  /**
   * Set search filter and reset to page 1
   */
  function setSearchQuery(query) {
    filters.search = query;
    fetchTransactions(1);
  }

  /**
   * Set transaction type filter
   */
  function setTransactionType(type) {
    filters.type = type;
    fetchTransactions(1);
  }

  /**
   * Set transaction status filter
   */
  function setTransactionStatus(status) {
    filters.status = status;
    fetchTransactions(1);
  }

  /**
   * Set date range filter
   */
  function setDateRange(startDate, endDate) {
    filters.start_date = startDate;
    filters.end_date = endDate;
    fetchTransactions(1);
  }

  /**
   * Toggle sort column
   */
  function toggleSort(column) {
    if (sorting.sort_by === column) {
      sorting.sort_order = sorting.sort_order === 'asc' ? 'desc' : 'asc';
    } else {
      sorting.sort_by = column;
      sorting.sort_order = 'asc';
    }
    fetchTransactions(1);
  }

  /**
   * Go to specific page
   */
  function goToPage(page) {
    fetchTransactions(page);
  }

  /**
   * Go to next page
   */
  function nextPage() {
    if (pagination.current_page < pagination.last_page) {
      fetchTransactions(pagination.current_page + 1);
    }
  }

  /**
   * Go to previous page
   */
  function prevPage() {
    if (pagination.current_page > 1) {
      fetchTransactions(pagination.current_page - 1);
    }
  }

  /**
   * Set items per page
   */
  function setPerPage(perPage) {
    pagination.per_page = Math.min(perPage, 100);
    fetchTransactions(1);
  }

  /**
   * Clear all filters
   */
  function clearFilters() {
    filters.search = '';
    filters.type = '';
    filters.status = '';
    filters.start_date = '';
    filters.end_date = '';
    fetchTransactions(1);
  }

  return {
    // State
    transactions,
    currentTransaction,
    loading,
    error,
    pagination,
    filters,
    sorting,

    // Methods
    fetchTransactions,
    getTransaction,
    createTransaction,
    updateTransaction,
    deleteTransaction,
    approveTransaction,
    rejectTransaction,
    setSearchQuery,
    setTransactionType,
    setTransactionStatus,
    setDateRange,
    toggleSort,
    goToPage,
    nextPage,
    prevPage,
    setPerPage,
    clearFilters,
  };
});
