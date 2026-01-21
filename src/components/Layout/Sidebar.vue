<template>
  <div>
    <!-- Mobile overlay -->
    <div
      v-if="isOpen"
      @click="$emit('close')"
      class="fixed inset-0 bg-gray-600 bg-opacity-75 z-40 lg:hidden"
    ></div>

    <!-- Sidebar -->
    <aside
      :class="[
        'fixed top-0 left-0 z-50 h-full w-64 shadow-lg transform transition-transform duration-300 ease-in-out lg:translate-x-0',
        'bg-[#06275c]',
        isOpen ? 'translate-x-0' : '-translate-x-full',
      ]"
    >
      <div class="flex flex-col h-full">
        <!-- Logo -->
        <div class="flex items-center justify-between h-16 px-6 border-b border-[#051f47]">
          <div class="flex items-center space-x-2">
            <div class="h-8 w-8 bg-white rounded-lg flex items-center justify-center">
              <span class="text-[#06275c] font-bold text-lg">B</span>
            </div>
            <span class="text-xl font-bold text-white">Bookkeeping</span>
          </div>
          <button
            @click="$emit('close')"
            class="lg:hidden p-1 rounded-md text-gray-400 hover:text-gray-500"
          >
            <XMarkIcon class="h-6 w-6" />
          </button>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
          <template v-for="item in menuItems" :key="item.name">
            <!-- Menu Item with Submenu -->
            <div v-if="item.submenu && item.submenu.length > 0">
              <button
                @click="toggleSubmenu(item.name)"
                class="w-full flex items-center justify-between px-4 py-3 text-sm font-medium rounded-lg transition-colors duration-200"
                :class="
                  isActiveParent(item)
                    ? 'bg-white bg-opacity-20 text-white'
                    : 'text-gray-200 hover:bg-white hover:bg-opacity-10'
                "
              >
                <div class="flex items-center">
                  <component :is="item.icon" class="mr-3 h-5 w-5" />
                  {{ item.label }}
                </div>
                <ChevronRightIcon
                  :class="[
                    'h-4 w-4 transition-transform duration-200',
                    expandedMenus[item.name] ? 'rotate-90' : '',
                  ]"
                />
              </button>
              <!-- Submenu Items -->
              <div
                v-show="expandedMenus[item.name]"
                class="ml-4 mt-1 space-y-1"
              >
                <router-link
                  v-for="subItem in item.submenu"
                  :key="subItem.name"
                  :to="subItem.path"
                  @click="$emit('close')"
                  class="flex items-center px-4 py-2 text-sm rounded-lg transition-colors duration-200"
                  :class="
                    $route.name === subItem.name
                      ? 'bg-white text-[#06275c] font-medium'
                      : 'text-gray-300 hover:bg-white hover:bg-opacity-10'
                  "
                >
                  <ChevronRightIcon class="mr-2 h-4 w-4" />
                  {{ subItem.label }}
                </router-link>
              </div>
            </div>

            <!-- Regular Menu Item (no submenu) -->
            <router-link
              v-else
              :to="item.path"
              @click="$emit('close')"
              class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors duration-200"
              :class="
                $route.path === item.path
                  ? 'bg-white text-[#06275c]'
                  : 'text-gray-200 hover:bg-white hover:bg-opacity-10'
              "
            >
              <component :is="item.icon" class="mr-3 h-5 w-5" />
              {{ item.label }}
            </router-link>
          </template>
        </nav>
      </div>
    </aside>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useRoute } from 'vue-router'
import {
  HomeIcon,
  ChartBarIcon,
  DocumentTextIcon,
  CreditCardIcon,
  ClipboardDocumentListIcon,
  ChartPieIcon,
  UserGroupIcon,
  CubeIcon,
  Cog6ToothIcon,
  XMarkIcon,
  ChevronRightIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false,
  },
})

defineEmits(['close'])

const route = useRoute()
const expandedMenus = ref({})

const menuItems = [
  { name: 'dashboard', path: '/', label: 'Dashboard', icon: HomeIcon },
  { name: 'chart-of-accounts', path: '/accounts', label: 'Chart of Accounts', icon: ChartBarIcon },
  {
    name: 'transactions',
    path: '/transactions',
    label: 'Transactions',
    icon: DocumentTextIcon,
    submenu: [
      { name: 'cash-bank-transactions', path: '/transactions/cash-bank', label: 'Cash Receipt' },
      { name: 'check-disbursement', path: '/transactions/check-disbursement', label: 'Check Disbursement' },
      { name: 'journal-entries', path: '/transactions/journal', label: 'Journal Entries' },
      { name: 'sales-revenue', path: '/transactions/sales', label: 'Sales / Revenue' },
      { name: 'purchases-expenses', path: '/transactions/purchases', label: 'Purchases / Expenses' },
      { name: 'recurring-transactions', path: '/transactions/recurring', label: 'Recurring Transactions' },
    ],
  },
  {
    name: 'accounts-receivable',
    path: '/receivables',
    label: 'Accounts Receivable',
    icon: CreditCardIcon,
  },
  {
    name: 'accounts-payable',
    path: '/payables',
    label: 'Accounts Payable',
    icon: ClipboardDocumentListIcon,
  },
  {
    name: 'financial-reports',
    path: '/reports',
    label: 'Financial Reports',
    icon: ChartPieIcon,
    submenu: [
      { name: 'balance-sheet', path: '/reports/balance-sheet', label: 'Balance Sheet' },
      { name: 'profit-loss', path: '/reports/profit-loss', label: 'Profit & Loss' },
      { name: 'cash-flow', path: '/reports/cash-flow', label: 'Cash Flow' },
      { name: 'trial-balance', path: '/reports/trial-balance', label: 'Trial Balance' },
      { name: 'general-ledger', path: '/reports/general-ledger', label: 'General Ledger' },
      { name: 'periods', path: '/reports/periods', label: 'Period Management' },
    ],
  },
  { name: 'payroll', path: '/payroll', label: 'Payroll / Employees', icon: UserGroupIcon },
  { name: 'inventory', path: '/inventory', label: 'Inventory / Products', icon: CubeIcon },
  {
    name: 'setup',
    path: '/setup',
    label: 'Setup & Configuration',
    icon: Cog6ToothIcon,
    submenu: [
      { name: 'customers', path: '/setup/customers', label: 'Customers' },
      { name: 'vendors', path: '/setup/vendors', label: 'Vendors' },
      { name: 'departments', path: '/setup/departments', label: 'Departments' },
      { name: 'projects', path: '/setup/projects', label: 'Projects' },
      { name: 'subsidiary-accounts', path: '/setup/subsidiary-accounts', label: 'Subsidiary Accounts' },
    ],
  },
  { name: 'settings', path: '/settings', label: 'Settings / Administration', icon: Cog6ToothIcon },
]

const isActiveParent = (item) => {
  if (item.submenu) {
    return item.submenu.some((subItem) => route.name === subItem.name || route.path.startsWith(subItem.path))
  }
  return route.path === item.path || route.path.startsWith(item.path + '/')
}

const toggleSubmenu = (menuName) => {
  expandedMenus.value[menuName] = !expandedMenus.value[menuName]
}

// Auto-expand submenu if current route is in that submenu
watch(
  () => route.path,
  (newPath) => {
    menuItems.forEach((item) => {
      if (item.submenu) {
        const isInSubmenu = item.submenu.some((subItem) => 
          newPath === subItem.path || newPath.startsWith(subItem.path)
        )
        if (isInSubmenu) {
          expandedMenus.value[item.name] = true
        }
      }
    })
  },
  { immediate: true }
)
</script>
