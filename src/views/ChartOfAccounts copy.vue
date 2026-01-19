<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 py-6">
        <div class="flex justify-between items-center">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Chart of Accounts</h1>
            <p class="text-gray-600 mt-1">Manage your account structure</p>
          </div>
          <button
            @click="showForm = true"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            New Account
          </button>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div class="max-w-7xl mx-auto px-4 py-8">
      <!-- Filters -->
      <div class="bg-white rounded-lg shadow mb-6 p-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <!-- Search -->
          <div>
            <input
              type="text"
              placeholder="Search by name or code..."
              v-model="searchQuery"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <!-- Account Type Filter -->
          <div>
            <select
              v-model="selectedType"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">All Types</option>
              <option value="Asset">Asset</option>
              <option value="Liability">Liability</option>
              <option value="Equity">Equity</option>
              <option value="Income">Income</option>
              <option value="Expense">Expense</option>
            </select>
          </div>

          <!-- Refresh Button -->
          <div class="flex items-center gap-2">
            <button
              @click="loadAccounts"
              :disabled="loading"
              class="flex-1 bg-gray-100 hover:bg-gray-200 px-3 py-2 rounded-lg flex items-center justify-center gap-2 disabled:opacity-50"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
              Refresh
            </button>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin">
          <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="accountStore.error" class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-700">
        {{ accountStore.error }}
      </div>

      <!-- Accounts Table -->
      <div v-else class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
          <thead class="bg-gray-50 border-b">
            <tr>
              <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Code</th>
              <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Name</th>
              <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Type</th>
              <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Parent</th>
              <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
              <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y">
            <tr
              v-for="account in filteredAccounts"
              :key="account.id"
              class="hover:bg-gray-50 transition"
            >
              <td class="px-6 py-4 text-sm font-medium text-gray-900">
                {{ account.code }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-700">
                <div :style="{ paddingLeft: getIndentLevel(account) }">
                  {{ account.name }}
                </div>
              </td>
              <td class="px-6 py-4 text-sm">
                <span :class="getAccountTypeBadgeClass(account.account_type)" class="px-2 py-1 rounded text-xs font-medium">
                  {{ account.account_type }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm text-gray-600">
                {{ account.parent?.code || '-' }}
              </td>
              <td class="px-6 py-4 text-sm">
                <span v-if="account.is_active" class="text-green-700 bg-green-50 px-2 py-1 rounded text-xs font-medium">Active</span>
                <span v-else class="text-gray-500 bg-gray-50 px-2 py-1 rounded text-xs font-medium">Inactive</span>
              </td>
              <td class="px-6 py-4 text-sm">
                <div class="flex gap-2">
                  <button
                    @click="editAccount(account)"
                    class="text-blue-600 hover:text-blue-900"
                  >
                    Edit
                  </button>
                  <button
                    @click="deleteAccount(account)"
                    class="text-red-600 hover:text-red-900"
                  >
                    Delete
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Empty State -->
        <div v-if="filteredAccounts.length === 0" class="text-center py-12">
          <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <p class="text-gray-500">No accounts found</p>
        </div>
      </div>
    </div>

    <!-- Account Form Modal -->
    <AccountForm
      v-if="showForm"
      :is-editing="editingAccount !== null"
      :account="editingAccount"
      @close="closeForm"
      @saved="onAccountSaved"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAccountStore } from '../../stores/accounts'
import AccountForm from '../../components/Accounts/AccountForm.vue'

const accountStore = useAccountStore()
const loading = ref(false)
const showForm = ref(false)
const editingAccount = ref(null)

const searchQuery = computed({
  get: () => accountStore.searchQuery,
  set: (val) => accountStore.setSearchQuery(val)
})

const selectedType = computed({
  get: () => accountStore.selectedAccountType,
  set: (val) => accountStore.setSelectedAccountType(val)
})

const filteredAccounts = computed(() => accountStore.filteredAccounts)

onMounted(async () => {
  loading.value = true
  try {
    await accountStore.fetchAccounts()
  } finally {
    loading.value = false
  }
})

const loadAccounts = async () => {
  loading.value = true
  try {
    await accountStore.fetchAccounts()
  } finally {
    loading.value = false
  }
}

const editAccount = (account) => {
  editingAccount.value = { ...account }
  showForm.value = true
}

const closeForm = () => {
  showForm.value = false
  editingAccount.value = null
}

const onAccountSaved = () => {
  closeForm()
}

const deleteAccount = async (account) => {
  if (confirm(`Are you sure you want to delete "${account.name}"?`)) {
    try {
      await accountStore.deleteAccount(account.id)
    } catch (err) {
      alert('Failed to delete account')
    }
  }
}

const getAccountTypeBadgeClass = (type) => {
  const typeClasses = {
    'Asset': 'bg-blue-50 text-blue-700',
    'Liability': 'bg-red-50 text-red-700',
    'Equity': 'bg-purple-50 text-purple-700',
    'Income': 'bg-green-50 text-green-700',
    'Expense': 'bg-orange-50 text-orange-700',
  }
  return typeClasses[type] || 'bg-gray-50 text-gray-700'
}

const getIndentLevel = (account) => {
  return account.parent_id ? '2rem' : '0'
}
</script>

    <!-- Content -->
    <div class="max-w-7xl mx-auto px-4 py-8">
      <!-- Filters -->
      <div class="bg-white rounded-lg shadow mb-6 p-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <!-- Search -->
          <div>
            <input
              type="text"
              placeholder="Search by name or code..."
              v-model="searchQuery"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <!-- Account Type Filter -->
          <div>
            <select
              v-model="selectedType"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">All Types</option>
              <option value="Asset">Asset</option>
              <option value="Liability">Liability</option>
              <option value="Equity">Equity</option>
              <option value="Income">Income</option>
              <option value="Expense">Expense</option>
            </select>
          </div>

          <!-- Refresh Button -->
          <div class="flex items-center gap-2">
            <button
              @click="loadAccounts"
              :disabled="loading"
              class="flex-1 bg-gray-100 hover:bg-gray-200 px-3 py-2 rounded-lg flex items-center justify-center gap-2 disabled:opacity-50"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
              Refresh
            </button>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin">
          <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="accountStore.error" class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-700">
        {{ accountStore.error }}
      </div>

      <!-- Accounts Table -->
      <div v-else class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
          <thead class="bg-gray-50 border-b">
            <tr>
              <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Code</th>
              <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Name</th>
              <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Type</th>
              <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Parent</th>
              <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
              <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y">
            <tr
              v-for="account in filteredAccounts"
              :key="account.id"
              class="hover:bg-gray-50 transition"
            >
              <td class="px-6 py-4 text-sm font-medium text-gray-900">
                {{ account.code }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-700">
                <div :style="{ paddingLeft: getIndentLevel(account) }">
                  {{ account.name }}
                </div>
              </td>
              <td class="px-6 py-4 text-sm">
                <span :class="getAccountTypeBadgeClass(account.account_type)" class="px-2 py-1 rounded text-xs font-medium">
                  {{ account.account_type }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm text-gray-600">
                {{ account.parent?.code || '-' }}
              </td>
              <td class="px-6 py-4 text-sm">
                <span v-if="account.is_active" class="text-green-700 bg-green-50 px-2 py-1 rounded text-xs font-medium">Active</span>
                <span v-else class="text-gray-500 bg-gray-50 px-2 py-1 rounded text-xs font-medium">Inactive</span>
              </td>
              <td class="px-6 py-4 text-sm">
                <div class="flex gap-2">
                  <button
                    @click="editAccount(account)"
                    class="text-blue-600 hover:text-blue-900"
                  >
                    Edit
                  </button>
                  <button
                    @click="deleteAccount(account)"
                    class="text-red-600 hover:text-red-900"
                  >
                    Delete
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Empty State -->
        <div v-if="filteredAccounts.length === 0" class="text-center py-12">
          <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <p class="text-gray-500">No accounts found</p>
        </div>
      </div>
    </div>

    <!-- Account Form Modal -->
    <AccountForm
      v-if="showForm"
      :is-editing="editingAccount !== null"
      :account="editingAccount"
      @close="closeForm"
      @saved="onAccountSaved"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAccountStore } from '../../stores/accounts'
import AccountForm from '../../components/Accounts/AccountForm.vue'

const accountStore = useAccountStore()
const loading = ref(false)
const showForm = ref(false)
const editingAccount = ref(null)

const searchQuery = computed({
  get: () => accountStore.searchQuery,
  set: (val) => accountStore.setSearchQuery(val)
})

const selectedType = computed({
  get: () => accountStore.selectedAccountType,
  set: (val) => accountStore.setSelectedAccountType(val)
})

const filteredAccounts = computed(() => accountStore.filteredAccounts)

onMounted(async () => {
  loading.value = true
  try {
    await accountStore.fetchAccounts()
  } finally {
    loading.value = false
  }
})

const loadAccounts = async () => {
  loading.value = true
  try {
    await accountStore.fetchAccounts()
  } finally {
    loading.value = false
  }
}

const editAccount = (account) => {
  editingAccount.value = { ...account }
  showForm.value = true
}

const closeForm = () => {
  showForm.value = false
  editingAccount.value = null
}

const onAccountSaved = () => {
  closeForm()
}

const deleteAccount = async (account) => {
  if (confirm(`Are you sure you want to delete "${account.name}"?`)) {
    try {
      await accountStore.deleteAccount(account.id)
    } catch (err) {
      alert('Failed to delete account')
    }
  }
}

const getAccountTypeBadgeClass = (type) => {
  const baseClass = 'px-2 py-1 rounded text-xs font-medium'
  const typeClasses = {
    'Asset': 'bg-blue-50 text-blue-700',
    'Liability': 'bg-red-50 text-red-700',
    'Equity': 'bg-purple-50 text-purple-700',
    'Income': 'bg-green-50 text-green-700',
    'Expense': 'bg-orange-50 text-orange-700',
  }
  return typeClasses[type] || baseClass
}

const getIndentLevel = (account) => {
  // This could be enhanced by tracking depth in the hierarchy
  return account.parent_id ? '2rem' : '0'
}
</script>
            type="text"
            placeholder="Search accounts..."
            class="input"
          />
        </div>
        <div>
          <label class="label">Account Type</label>
          <select v-model="selectedType" class="input">
            <option value="">All Types</option>
            <option value="assets">Assets</option>
            <option value="liabilities">Liabilities</option>
            <option value="equity">Equity</option>
            <option value="income">Income</option>
            <option value="expenses">Expenses</option>
          </select>
        </div>
        <div class="flex items-end">
          <button @click="loadAccounts" class="btn btn-secondary w-full">
            Apply Filters
          </button>
        </div>
      </div>
    </div>

    <!-- Accounts Table -->
    <div class="card">
      <div class="mb-4 flex gap-2">
        <button 
          @click="viewMode = 'list'"
          :class="['btn', viewMode === 'list' ? 'btn-primary' : 'btn-secondary']"
        >
          List View
        </button>
        <button 
          @click="viewMode = 'hierarchy'"
          :class="['btn', viewMode === 'hierarchy' ? 'btn-primary' : 'btn-secondary']"
        >
          Hierarchy View
        </button>
      </div>
      
      <!-- List View -->
      <div v-if="viewMode === 'list'" class="overflow-x-auto">
        <table class="table">
          <thead>
            <tr>
              <th>Code</th>
              <th>Account Name</th>
              <th>Type</th>
              <th>Parent Account</th>
              <th>Balance</th>
              <th class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="account in filteredAccounts" :key="account.id">
              <td class="font-mono">{{ account.code }}</td>
              <td class="font-medium">{{ account.name }}</td>
              <td>
                <span
                  :class="[
                    'px-2 py-1 text-xs font-medium rounded-full',
                    getTypeClass(account.type),
                  ]"
                >
                  {{ account.type }}
                </span>
              </td>
              <td>{{ account.parent?.name || '-' }}</td>
              <td class="font-medium">{{ formatCurrency(account.balance) }}</td>
              <td class="text-right">
                <button
                  @click="editAccount(account)"
                  class="text-primary-600 hover:text-primary-700 mr-3"
                >
                  Edit
                </button>
                <button
                  @click="deleteAccount(account.id)"
                  class="text-red-600 hover:text-red-700"
                >
                  Delete
                </button>
              </td>
            </tr>
            <tr v-if="filteredAccounts.length === 0">
              <td colspan="6" class="text-center py-8 text-gray-500">
                No accounts found
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Hierarchy View -->
      <div v-else class="space-y-2">
        <div v-for="account in topLevelAccounts" :key="account.id" class="space-y-1">
          <div class="p-3 bg-gray-50 rounded border-l-4" :style="getBorderColor(account.type)">
            <div class="flex justify-between items-center">
              <div class="flex-1">
                <span class="font-mono text-sm font-semibold">{{ account.code }}</span>
                <span class="ml-2 font-medium">{{ account.name }}</span>
                <span :class="['ml-2 px-2 py-0.5 text-xs font-medium rounded-full', getTypeClass(account.type)]">
                  {{ account.type }}
                </span>
              </div>
              <span class="font-semibold">{{ formatCurrency(account.balance) }}</span>
            </div>
          </div>
          <!-- Child Accounts -->
          <div v-if="getChildAccounts(account.id).length > 0" class="ml-6 space-y-1 border-l-2 border-gray-200 pl-4">
            <div 
              v-for="child in getChildAccounts(account.id)" 
              :key="child.id"
              class="p-2 bg-white border border-gray-200 rounded flex justify-between items-center"
            >
              <div class="flex-1">
                <span class="font-mono text-xs font-semibold text-gray-600">{{ child.code }}</span>
                <span class="ml-2 text-sm">{{ child.name }}</span>
              </div>
              <div class="flex items-center gap-2">
                <span class="font-medium text-sm">{{ formatCurrency(child.balance) }}</span>
                <button
                  @click="editAccount(child)"
                  class="text-primary-600 hover:text-primary-700 text-sm"
                >
                  Edit
                </button>
                <button
                  @click="deleteAccount(child.id)"
                  class="text-red-600 hover:text-red-700 text-sm"
                >
                  Delete
                </button>
              </div>
            </div>
          </div>
        </div>
        <div v-if="topLevelAccounts.length === 0" class="text-center py-8 text-gray-500">
          No accounts found
        </div>
      </div>
    </div>

    <!-- Account Modal -->
    <AccountModal
      v-if="showModal"
      :account="selectedAccount"
      @close="showModal = false"
      @saved="handleAccountSaved"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { accountsApi } from '@/services/api'
import AccountModal from '@/components/Accounts/AccountModal.vue'
import { PlusIcon } from '@heroicons/vue/24/outline'

const accounts = ref([])
const searchQuery = ref('')
const selectedType = ref('')
const showModal = ref(false)
const selectedAccount = ref(null)
const viewMode = ref('list')

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(amount || 0)
}

const getTypeClass = (type) => {
  const classes = {
    Assets: 'bg-green-100 text-green-800',
    Liabilities: 'bg-red-100 text-red-800',
    Equity: 'bg-blue-100 text-blue-800',
    Income: 'bg-purple-100 text-purple-800',
    Expenses: 'bg-orange-100 text-orange-800',
  }
  return classes[type] || 'bg-gray-100 text-gray-800'
}

const getBorderColor = (type) => {
  const colors = {
    Assets: 'border-green-400',
    Liabilities: 'border-red-400',
    Equity: 'border-blue-400',
    Income: 'border-purple-400',
    Expenses: 'border-orange-400',
  }
  return colors[type] || 'border-gray-400'
}

const topLevelAccounts = computed(() => {
  return accounts.value.filter(account => !account.parent_id)
})

const filteredAccounts = computed(() => {
  return accounts.value.filter(account => {
    const matchesSearch = 
      !searchQuery.value ||
      account.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      account.code.toLowerCase().includes(searchQuery.value.toLowerCase())
    
    const matchesType = !selectedType.value || account.type === selectedType.value
    
    return matchesSearch && matchesType
  })
})

const getChildAccounts = (parentId) => {
  return filteredAccounts.value.filter(account => account.parent_id === parentId)
}

const loadAccounts = async () => {
  try {
    const params = {}
    if (searchQuery.value) params.search = searchQuery.value
    if (selectedType.value) params.type = selectedType.value

    const response = await accountsApi.getAll(params)
    accounts.value = response.data || []
  } catch (error) {
    console.error('Error loading accounts:', error)
    // Use mock data for development
    accounts.value = [
      {
        id: 1,
        code: '1000',
        name: 'Cash',
        type: 'Assets',
        balance: 50000,
        parent: null,
      },
      {
        id: 2,
        code: '1100',
        name: 'Bank Accounts',
        type: 'Assets',
        balance: 100000,
        parent: null,
      },
      {
        id: 3,
        code: '1200',
        name: 'Accounts Receivable',
        type: 'Assets',
        balance: 25000,
        parent: null,
      },
    ]
  }
}

const editAccount = (account) => {
  selectedAccount.value = account
  showModal.value = true
}

const deleteAccount = async (id) => {
  if (!confirm('Are you sure you want to delete this account?')) return

  try {
    await accountsApi.delete(id)
    await loadAccounts()
  } catch (error) {
    console.error('Error deleting account:', error)
  }
}

const handleAccountSaved = () => {
  showModal.value = false
  selectedAccount.value = null
  loadAccounts()
}

onMounted(() => {
  loadAccounts()
})
</script>
