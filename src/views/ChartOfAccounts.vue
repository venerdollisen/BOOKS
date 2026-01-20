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
            class="bg-[#06275c] hover:bg-[#051f47] text-white px-4 py-2 rounded-lg flex items-center gap-2"
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
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <!-- Search -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
            <input
              type="text"
              placeholder="Code or name..."
              :value="accountStore.searchQuery"
              @input="handleSearch"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <!-- Account Type Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
            <select
              :value="accountStore.selectedAccountType"
              @change="handleTypeFilter"
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

          <!-- Per Page Selector -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Per Page</label>
            <select
              :value="accountStore.pagination.per_page"
              @change="handlePerPageChange"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="10">10</option>
              <option value="15">15</option>
              <option value="25">25</option>
              <option value="50">50</option>
            </select>
          </div>

          <!-- Refresh Button -->
          <div class="flex items-end">
            <button
              @click="accountStore.fetchAccounts(1)"
              :disabled="accountStore.loading"
              class="w-full bg-gray-100 hover:bg-gray-200 px-3 py-2 rounded-lg flex items-center justify-center gap-2 disabled:opacity-50"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
              Refresh
            </button>
          </div>
        </div>
      </div>

      <!-- Info Bar -->
      <div class="bg-white rounded-lg shadow mb-4 px-4 py-3 flex justify-between items-center">
        <p class="text-sm text-gray-600">{{ accountStore.pageInfo }}</p>
        <div class="text-sm text-gray-600" v-if="accountStore.totalPages > 1">
          Page {{ accountStore.pagination.current_page }} of {{ accountStore.totalPages }}
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="accountStore.loading" class="text-center py-12">
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
              <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 cursor-pointer hover:bg-gray-100"
                  @click="accountStore.toggleSort('code')">
                <div class="flex items-center gap-2">
                  Code
                  <span v-if="accountStore.sortBy === 'code'" class="text-xs">
                    {{ accountStore.sortOrder === 'asc' ? '↑' : '↓' }}
                  </span>
                </div>
              </th>
              <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 cursor-pointer hover:bg-gray-100"
                  @click="accountStore.toggleSort('name')">
                <div class="flex items-center gap-2">
                  Name
                  <span v-if="accountStore.sortBy === 'name'" class="text-xs">
                    {{ accountStore.sortOrder === 'asc' ? '↑' : '↓' }}
                  </span>
                </div>
              </th>
              <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 cursor-pointer hover:bg-gray-100"
                  @click="accountStore.toggleSort('account_type')">
                <div class="flex items-center gap-2">
                  Type
                  <span v-if="accountStore.sortBy === 'account_type'" class="text-xs">
                    {{ accountStore.sortOrder === 'asc' ? '↑' : '↓' }}
                  </span>
                </div>
              </th>
              <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Parent</th>
              <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
              <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y">
            <tr
              v-for="account in accountStore.accounts"
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
        <div v-if="accountStore.accounts.length === 0 && !accountStore.loading" class="text-center py-12">
          <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <p class="text-gray-500">No accounts found</p>
        </div>
      </div>

      <!-- Pagination Controls -->
      <div v-if="accountStore.totalPages > 1 && !accountStore.loading" class="bg-white rounded-lg shadow mt-6 p-4 flex justify-between items-center">
        <div>
          <button
            @click="accountStore.prevPage()"
            :disabled="!accountStore.hasPrevPage"
            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            ← Previous
          </button>
        </div>

        <div class="flex gap-1">
          <button
            v-for="page in getPageNumbers()"
            :key="page"
            @click="accountStore.goToPage(page)"
            :class="{
              'bg-[#06275c] text-white': page === accountStore.pagination.current_page,
              'border border-gray-300 hover:bg-gray-50': page !== accountStore.pagination.current_page,
              'px-3 py-2 rounded-lg': true,
            }"
          >
            {{ page }}
          </button>
        </div>

        <div>
          <button
            @click="accountStore.nextPage()"
            :disabled="!accountStore.hasNextPage"
            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Next →
          </button>
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
import { ref, onMounted } from 'vue'
import { useAccountStore } from '../stores/accounts'
import AccountForm from '../components/Accounts/AccountForm.vue'

const accountStore = useAccountStore()
const showForm = ref(false)
const editingAccount = ref(null)

onMounted(async () => {
  await accountStore.fetchAccounts(1)
})

const handleSearch = async (event) => {
  await accountStore.setSearchQuery(event.target.value)
}

const handleTypeFilter = async (event) => {
  await accountStore.setSelectedAccountType(event.target.value)
}

const handlePerPageChange = async (event) => {
  await accountStore.setPerPage(parseInt(event.target.value))
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

const getPageNumbers = () => {
  const { current_page, last_page } = accountStore.pagination
  const pages = []
  const maxPagesToShow = 5
  
  if (last_page <= maxPagesToShow) {
    for (let i = 1; i <= last_page; i++) {
      pages.push(i)
    }
  } else {
    pages.push(1)
    
    if (current_page > 3) {
      pages.push('...')
    }
    
    const start = Math.max(2, current_page - 1)
    const end = Math.min(last_page - 1, current_page + 1)
    
    for (let i = start; i <= end; i++) {
      if (!pages.includes(i)) {
        pages.push(i)
      }
    }
    
    if (current_page < last_page - 2) {
      pages.push('...')
    }
    
    if (!pages.includes(last_page)) {
      pages.push(last_page)
    }
  }
  
  return pages
}
</script>
