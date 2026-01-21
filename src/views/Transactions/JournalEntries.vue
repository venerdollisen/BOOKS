<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h3 class="text-xl font-bold text-gray-900">Journal Entries</h3>
        <p class="text-sm text-gray-500 mt-1">Record general ledger entries</p>
      </div>
      <button @click="showModal = true" class="btn btn-primary">
        <PlusIcon class="h-5 w-5 inline mr-2" />
        New Transaction
      </button>
    </div>

    <!-- Filters -->
    <div class="card">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
        <div>
          <label class="label">Date From</label>
          <input v-model="filters.date_from" type="date" class="input" />
        </div>
        <div>
          <label class="label">Date To</label>
          <input v-model="filters.date_to" type="date" class="input" />
        </div>
        <div>
          <label class="label">Account</label>
          <select v-model="filters.account_id" class="input">
            <option value="">All Accounts</option>
            <option v-for="account in accounts" :key="account.id" :value="account.id">
              {{ account.code }} - {{ account.name }}
            </option>
          </select>
        </div>
        <div class="flex gap-2">
          <button @click="applyFilters" class="btn btn-primary flex-1">Filter</button>
          <button @click="clearFilters" class="btn btn-secondary flex-1">Clear</button>
        </div>
      </div>
    </div>

    <!-- Transactions Table -->
    <div class="card">
      <div class="overflow-x-auto">
        <table class="table">
          <thead>
            <tr>
              <th>Date</th>
              <th>Reference</th>
              <th>Description</th>
              <th class="text-right">Total Debit</th>
              <th class="text-right">Total Credit</th>
              <th class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <template v-for="transaction in transactions" :key="transaction.id">
              <tr class="hover:bg-gray-50">
                <td>{{ formatDate(transaction.transaction_date) }}</td>
                <td class="font-mono text-sm">{{ transaction.reference || '-' }}</td>
                <td>{{ transaction.description }}</td>
                <td class="text-right font-medium text-green-600">
                  {{ formatCurrency(transaction.debit_total || 0) }}
                </td>
                <td class="text-right font-medium text-red-600">
                  {{ formatCurrency(transaction.credit_total || 0) }}
                </td>
                <td class="text-right">
                  <button
                    @click="toggleDetails(transaction.id)"
                    class="text-primary-600 hover:text-primary-700 mr-3"
                  >
                    {{ expandedTransactions.includes(transaction.id) ? 'Hide' : 'View' }}
                  </button>
                  <button
                    @click="editTransaction(transaction)"
                    class="text-primary-600 hover:text-primary-700 mr-3"
                  >
                    Edit
                  </button>
                  <button
                    @click="deleteTransaction(transaction.id)"
                    class="text-red-600 hover:text-red-700"
                  >
                    Delete
                  </button>
                </td>
              </tr>
              <!-- Expanded Details -->
              <tr v-if="expandedTransactions.includes(transaction.id)" class="bg-gray-50">
                <td colspan="6" class="p-4">
                  <div class="space-y-2">
                    <h5 class="font-semibold text-sm mb-2">Journal Entry Lines:</h5>
                    <table class="w-full text-sm">
                      <thead>
                        <tr class="border-b bg-gray-100">
                          <th class="text-left py-2 px-3">Account</th>
                          <th class="text-right py-2 px-3">Debit</th>
                          <th class="text-right py-2 px-3">Credit</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr
                          v-for="(entry, idx) in transaction.items"
                          :key="idx"
                          class="border-b"
                        >
                          <td class="py-2 px-3">{{ entry.account?.name || 'N/A' }}</td>
                          <td class="text-right py-2 px-3 text-green-600 font-medium">
                            {{ entry.type === 'debit' ? formatCurrency(entry.amount) : '-' }}
                          </td>
                          <td class="text-right py-2 px-3 text-red-600 font-medium">
                            {{ entry.type === 'credit' ? formatCurrency(entry.amount) : '-' }}
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </td>
              </tr>
            </template>
            <tr v-if="transactions.length === 0">
              <td colspan="6" class="text-center py-8 text-gray-500">
                No entries found
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Journal Entry Modal -->
    <JournalEntriesModal
      v-if="showModal"
      :transaction="selectedTransaction"
      @close="showModal = false"
      @saved="handleTransactionSaved"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useTransactionStore } from '@/stores/transactions'
import { useAccountStore } from '@/stores/accounts'
import { PlusIcon } from '@heroicons/vue/24/outline'
import JournalEntriesModal from '@/components/Transactions/JournalEntriesModal.vue'

const transactionStore = useTransactionStore()
const accountStore = useAccountStore()

const showModal = ref(false)
const selectedTransaction = ref(null)
const expandedTransactions = ref([])
const accounts = ref([])
const filters = ref({
  date_from: '',
  date_to: '',
  account_id: '',
  type: 'journal',
})

const transactions = computed(() => transactionStore.transactions)
const loading = computed(() => transactionStore.loading)

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(amount || 0)
}

const loadTransactions = async () => {
  // Clear all filters first
  transactionStore.filters.search = ''
  transactionStore.filters.status = ''
  transactionStore.filters.account_id = ''
  transactionStore.filters.start_date = ''
  transactionStore.filters.end_date = ''
  
  // Set the type filter and date filters
  transactionStore.filters.type = 'journal'
  transactionStore.filters.start_date = filters.value.date_from || ''
  transactionStore.filters.end_date = filters.value.date_to || ''
  transactionStore.filters.account_id = filters.value.account_id || ''
  
  await transactionStore.fetchTransactions(1)
}

const editTransaction = (transaction) => {
  selectedTransaction.value = transaction
  showModal.value = true
}

const deleteTransaction = async (id) => {
  if (!confirm('Are you sure you want to delete this entry?')) return

  const success = await transactionStore.deleteTransaction(id)
  if (success) {
    await loadTransactions()
  }
}

const handleTransactionSaved = () => {
  showModal.value = false
  selectedTransaction.value = null
  loadTransactions()
}

const toggleDetails = (transactionId) => {
  const index = expandedTransactions.value.indexOf(transactionId)
  if (index > -1) {
    expandedTransactions.value.splice(index, 1)
  } else {
    expandedTransactions.value.push(transactionId)
  }
}

const applyFilters = async () => {
  await loadTransactions()
}

const clearFilters = async () => {
  filters.value = {
    date_from: '',
    date_to: '',
    account_id: '',
    type: 'journal',
  }
  await loadTransactions()
}

onMounted(async () => {
  // Clear old transactions immediately
  transactionStore.transactions = []
  
  await accountStore.fetchAccounts(1)
  accounts.value = accountStore.accounts
  await loadTransactions()
})
</script>

