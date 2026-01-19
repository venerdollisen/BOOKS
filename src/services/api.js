import axios from 'axios'
import { API_CONFIG } from '@/config/api'

// Determine the API base URL
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api'

// Create axios instance with default config
const apiClient = axios.create({
  baseURL: API_BASE_URL,
  withCredentials: true,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
})

// Request interceptor for adding auth token
apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Response interceptor for handling errors
apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Handle unauthorized access
      localStorage.removeItem('auth_token')
      sessionStorage.removeItem('auth_token')
      // Only redirect if not already on login page
      if (window.location.pathname !== '/login') {
        window.location.href = '/login'
      }
    }
    return Promise.reject(error)
  }
)

// Authentication API
// Uses endpoints from API_CONFIG - update src/config/api.js to match your Laravel routes
export const authApi = {
  login: (credentials) => apiClient.post(API_CONFIG.auth.login, credentials),
  register: (data) => apiClient.post(API_CONFIG.auth.register, data),
  logout: () => apiClient.post(API_CONFIG.auth.logout),
  getUser: () => apiClient.get(API_CONFIG.auth.user),
}

// Dashboard API
export const dashboardApi = {
  getSummary: () => apiClient.get('/dashboard/summary'),
  getCashBalance: (filters = {}) => apiClient.get('/dashboard/cash-balance', { params: filters }),
  getReceivablesPayables: () => apiClient.get('/dashboard/receivables-payables'),
  getProfitLoss: (period = 'monthly') => apiClient.get('/dashboard/profit-loss', { params: { period } }),
  getRecentTransactions: (limit = 10) => apiClient.get('/dashboard/recent-transactions', { params: { limit } }),
}

// Chart of Accounts API
export const accountsApi = {
  getAll: (params = {}) => apiClient.get('/accounts', { params }),
  getById: (id) => apiClient.get(`/accounts/${id}`),
  create: (data) => apiClient.post('/accounts', data),
  update: (id, data) => apiClient.put(`/accounts/${id}`, data),
  delete: (id) => apiClient.delete(`/accounts/${id}`),
  getByType: (type) => apiClient.get('/accounts', { params: { type } }),
}

// Transactions API
export const transactionsApi = {
  getAll: (params = {}) => apiClient.get('/transactions', { params }),
  getById: (id) => apiClient.get(`/transactions/${id}`),
  create: (data) => apiClient.post('/transactions', data),
  update: (id, data) => apiClient.put(`/transactions/${id}`, data),
  delete: (id) => apiClient.delete(`/transactions/${id}`),
  // Cash/Bank transactions
  createCashTransaction: (data) => apiClient.post('/transactions/cash', data),
  createBankTransaction: (data) => apiClient.post('/transactions/bank', data),
  // Journal entries
  createJournalEntry: (data) => apiClient.post('/transactions/journal', data),
  // Sales/Revenue
  createSalesEntry: (data) => apiClient.post('/transactions/sales', data),
  // Purchases/Expenses
  createPurchaseEntry: (data) => apiClient.post('/transactions/purchases', data),
  // Recurring transactions
  getRecurring: () => apiClient.get('/transactions/recurring'),
  createRecurring: (data) => apiClient.post('/transactions/recurring', data),
  updateRecurring: (id, data) => apiClient.put(`/transactions/recurring/${id}`, data),
  deleteRecurring: (id) => apiClient.delete(`/transactions/recurring/${id}`),
}

// Accounts Receivable API
export const receivablesApi = {
  getInvoices: (params = {}) => apiClient.get('/receivables/invoices', { params }),
  getInvoiceById: (id) => apiClient.get(`/receivables/invoices/${id}`),
  createInvoice: (data) => apiClient.post('/receivables/invoices', data),
  updateInvoice: (id, data) => apiClient.put(`/receivables/invoices/${id}`, data),
  deleteInvoice: (id) => apiClient.delete(`/receivables/invoices/${id}`),
  markPaid: (id, data) => apiClient.post(`/receivables/invoices/${id}/pay`, data),
  getAgingReport: () => apiClient.get('/receivables/aging'),
  getCustomerStatement: (customerId) => apiClient.get(`/receivables/customers/${customerId}/statement`),
}

// Accounts Payable API
export const payablesApi = {
  getBills: (params = {}) => apiClient.get('/payables/bills', { params }),
  getBillById: (id) => apiClient.get(`/payables/bills/${id}`),
  createBill: (data) => apiClient.post('/payables/bills', data),
  updateBill: (id, data) => apiClient.put(`/payables/bills/${id}`, data),
  deleteBill: (id) => apiClient.delete(`/payables/bills/${id}`),
  markPaid: (id, data) => apiClient.post(`/payables/bills/${id}/pay`, data),
  getAgingReport: () => apiClient.get('/payables/aging'),
  getSupplierStatement: (supplierId) => apiClient.get(`/payables/suppliers/${supplierId}/statement`),
}

// Financial Reports API
export const reportsApi = {
  getBalanceSheet: (params = {}) => apiClient.get('/reports/balance-sheet', { params }),
  getProfitLoss: (params = {}) => apiClient.get('/reports/profit-loss', { params }),
  getCashFlow: (params = {}) => apiClient.get('/reports/cash-flow', { params }),
  getTrialBalance: (params = {}) => apiClient.get('/reports/trial-balance', { params }),
  exportReport: (type, format, params = {}) => 
    apiClient.get(`/reports/${type}/export`, { params: { format, ...params }, responseType: 'blob' }),
}

// Payroll API
export const payrollApi = {
  getEmployees: () => apiClient.get('/payroll/employees'),
  getEmployeeById: (id) => apiClient.get(`/payroll/employees/${id}`),
  createEmployee: (data) => apiClient.post('/payroll/employees', data),
  updateEmployee: (id, data) => apiClient.put(`/payroll/employees/${id}`, data),
  deleteEmployee: (id) => apiClient.delete(`/payroll/employees/${id}`),
  generatePayslip: (employeeId, period) => 
    apiClient.post(`/payroll/employees/${employeeId}/payslip`, { period }, { responseType: 'blob' }),
}

// Inventory API
export const inventoryApi = {
  getProducts: (params = {}) => apiClient.get('/inventory/products', { params }),
  getProductById: (id) => apiClient.get(`/inventory/products/${id}`),
  createProduct: (data) => apiClient.post('/inventory/products', data),
  updateProduct: (id, data) => apiClient.put(`/inventory/products/${id}`, data),
  deleteProduct: (id) => apiClient.delete(`/inventory/products/${id}`),
  getStockLevels: () => apiClient.get('/inventory/stock-levels'),
  getLowStockAlerts: () => apiClient.get('/inventory/low-stock'),
  getValuationReport: () => apiClient.get('/inventory/valuation'),
}

// Settings API
export const settingsApi = {
  getCompanyProfile: () => apiClient.get('/settings/company'),
  updateCompanyProfile: (data) => apiClient.put('/settings/company', data),
  getUsers: () => apiClient.get('/settings/users'),
  createUser: (data) => apiClient.post('/settings/users', data),
  updateUser: (id, data) => apiClient.put(`/settings/users/${id}`, data),
  deleteUser: (id) => apiClient.delete(`/settings/users/${id}`),
  getTaxRates: () => apiClient.get('/settings/tax-rates'),
  updateTaxRates: (data) => apiClient.put('/settings/tax-rates', data),
  getCurrencies: () => apiClient.get('/settings/currencies'),
  updateCurrencies: (data) => apiClient.put('/settings/currencies', data),
  getPreferences: () => apiClient.get('/settings/preferences'),
  updatePreferences: (data) => apiClient.put('/settings/preferences', data),
}

export default apiClient
