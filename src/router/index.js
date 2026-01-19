import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
  {
    path: '/login',
    name: 'login',
    component: () => import('../views/Login.vue'),
    meta: { title: 'Login', requiresGuest: true },
  },
  {
    path: '/',
    name: 'dashboard',
    component: () => import('../views/Dashboard.vue'),
    meta: { title: 'Dashboard', requiresAuth: true },
  },
  {
    path: '/accounts',
    name: 'chart-of-accounts',
    component: () => import('../views/ChartOfAccounts.vue'),
    meta: { title: 'Chart of Accounts', requiresAuth: true },
  },
  {
    path: '/transactions',
    name: 'transactions',
    component: () => import('../views/Transactions/Index.vue'),
    meta: { title: 'Transactions', requiresAuth: true },
    redirect: '/transactions/cash-bank',
    children: [
      {
        path: '',
        redirect: 'cash-bank',
      },
      {
        path: 'cash-bank',
        name: 'cash-bank-transactions',
        component: () => import('../views/Transactions/CashBank.vue'),
        meta: { title: 'Cash / Bank Transactions' },
      },
      {
        path: 'journal',
        name: 'journal-entries',
        component: () => import('../views/Transactions/JournalEntries.vue'),
        meta: { title: 'Journal Entries' },
      },
      {
        path: 'sales',
        name: 'sales-revenue',
        component: () => import('../views/Transactions/SalesRevenue.vue'),
        meta: { title: 'Sales / Revenue' },
      },
      {
        path: 'purchases',
        name: 'purchases-expenses',
        component: () => import('../views/Transactions/PurchasesExpenses.vue'),
        meta: { title: 'Purchases / Expenses' },
      },
      {
        path: 'recurring',
        name: 'recurring-transactions',
        component: () => import('../views/Transactions/Recurring.vue'),
        meta: { title: 'Recurring Transactions' },
      },
    ],
  },
  {
    path: '/receivables',
    name: 'accounts-receivable',
    component: () => import('../views/AccountsReceivable.vue'),
    meta: { title: 'Accounts Receivable', requiresAuth: true },
  },
  {
    path: '/payables',
    name: 'accounts-payable',
    component: () => import('../views/AccountsPayable.vue'),
    meta: { title: 'Accounts Payable', requiresAuth: true },
  },
  {
    path: '/reports',
    name: 'financial-reports',
    component: () => import('../views/Reports/Index.vue'),
    meta: { title: 'Financial Reports', requiresAuth: true },
    redirect: '/reports/balance-sheet',
    children: [
      {
        path: '',
        redirect: 'balance-sheet',
      },
      {
        path: 'balance-sheet',
        name: 'balance-sheet',
        component: () => import('../views/Reports/BalanceSheet.vue'),
        meta: { title: 'Balance Sheet' },
      },
      {
        path: 'profit-loss',
        name: 'profit-loss',
        component: () => import('../views/Reports/ProfitLoss.vue'),
        meta: { title: 'Profit & Loss' },
      },
      {
        path: 'cash-flow',
        name: 'cash-flow',
        component: () => import('../views/Reports/CashFlow.vue'),
        meta: { title: 'Cash Flow Statement' },
      },
      {
        path: 'trial-balance',
        name: 'trial-balance',
        component: () => import('../views/Reports/TrialBalance.vue'),
        meta: { title: 'Trial Balance' },
      },
    ],
  },
  {
    path: '/payroll',
    name: 'payroll',
    component: () => import('../views/Payroll.vue'),
    meta: { title: 'Payroll / Employees', requiresAuth: true },
  },
  {
    path: '/inventory',
    name: 'inventory',
    component: () => import('../views/Inventory.vue'),
    meta: { title: 'Inventory / Products', requiresAuth: true },
  },
  {
    path: '/settings',
    name: 'settings',
    component: () => import('../views/Settings.vue'),
    meta: { title: 'Settings', requiresAuth: true },
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()
  
  // Set document title
  document.title = `${to.meta.title || 'Bookkeeping System'} - Bookkeeping System`

  // Check if route requires authentication
  if (to.meta.requiresAuth) {
    if (!authStore.isAuthenticated) {
      // Try to initialize auth from stored token
      await authStore.initAuth()
      
      if (!authStore.isAuthenticated) {
        // Redirect to login if not authenticated
        next({ name: 'login', query: { redirect: to.fullPath } })
        return
      }
    }
  }

  // Check if route requires guest (like login page)
  if (to.meta.requiresGuest && authStore.isAuthenticated) {
    // Redirect to dashboard if already logged in
    next({ name: 'dashboard' })
    return
  }

  next()
})

export default router
