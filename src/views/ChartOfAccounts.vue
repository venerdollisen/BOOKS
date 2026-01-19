<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h2 class="text-2xl font-bold text-gray-900">Chart of Accounts</h2>
        <p class="text-sm text-gray-500 mt-1">Manage your account structure</p>
      </div>
      <button @click="showModal = true" class="btn btn-primary">
        <PlusIcon class="h-5 w-5 inline mr-2" />
        Add Account
      </button>
    </div>

    <!-- Search and Filters -->
    <div class="card">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="label">Search</label>
          <input
            v-model="searchQuery"
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
