<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h3 class="text-xl font-bold text-gray-900">Cash / Bank Transactions</h3>
        <p class="text-sm text-gray-500 mt-1">Record receipts, payments, and transfers</p>
      </div>
      <button @click="showModal = true" class="btn btn-primary">
        <PlusIcon class="h-5 w-5 inline mr-2" />
        New Transaction
      </button>
    </div>

    <!-- Filters -->
    <div class="card">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
            <!-- Accounts would be loaded from API -->
          </select>
        </div>
        <div>
          <label class="label">Type</label>
          <select v-model="filters.type" class="input">
            <option value="">All Types</option>
            <option value="receipt">Receipt</option>
            <option value="payment">Payment</option>
            <option value="transfer">Transfer</option>
          </select>
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
              <th>Type</th>
              <th>Description</th>
              <th>Reference</th>
              <th class="text-right">Total Debit</th>
              <th class="text-right">Total Credit</th>
              <th class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <template v-for="transaction in transactions" :key="transaction.id">
              <tr class="hover:bg-gray-50">
                <td>{{ formatDate(transaction.date) }}</td>
                <td>
                  <span
                    :class="[
                      'px-2 py-1 text-xs font-medium rounded-full',
                      getTypeClass(transaction.type),
                    ]"
                  >
                    {{ transaction.type }}
                  </span>
                </td>
                <td>{{ transaction.description }}</td>
                <td class="font-mono text-sm">{{ transaction.reference || '-' }}</td>
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
                <td colspan="7" class="p-4">
                  <div class="space-y-2">
                    <h5 class="font-semibold text-sm mb-2">Transaction Entries:</h5>
                    <table class="w-full text-sm">
                      <thead>
                        <tr class="border-b">
                          <th class="text-left py-2">Account</th>
                          <th class="text-right py-2">Debit</th>
                          <th class="text-right py-2">Credit</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr
                          v-for="(entry, idx) in transaction.entries"
                          :key="idx"
                          class="border-b"
                        >
                          <td class="py-2">{{ entry.account?.name || 'N/A' }}</td>
                          <td class="text-right py-2 text-green-600">
                            {{ entry.debit > 0 ? formatCurrency(entry.debit) : '-' }}
                          </td>
                          <td class="text-right py-2 text-red-600">
                            {{ entry.credit > 0 ? formatCurrency(entry.credit) : '-' }}
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </td>
              </tr>
            </template>
            <tr v-if="transactions.length === 0">
              <td colspan="7" class="text-center py-8 text-gray-500">
                No transactions found
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Transaction Modal -->
    <CashBankModal
      v-if="showModal"
      :transaction="selectedTransaction"
      @close="showModal = false"
      @saved="handleTransactionSaved"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { transactionsApi } from '@/services/api'
import CashBankModal from '@/components/Transactions/CashBankModal.vue'
import { PlusIcon } from '@heroicons/vue/24/outline'

const transactions = ref([])
const filters = ref({
  date_from: '',
  date_to: '',
  account_id: '',
  type: '',
})
const showModal = ref(false)
const selectedTransaction = ref(null)
const expandedTransactions = ref([])

// Watch filters for changes
watch(filters, () => {
  loadTransactions()
}, { deep: true })

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US')
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(amount || 0)
}

const getTypeClass = (type) => {
  const classes = {
    receipt: 'bg-green-100 text-green-800',
    payment: 'bg-red-100 text-red-800',
    transfer: 'bg-blue-100 text-blue-800',
  }
  return classes[type] || 'bg-gray-100 text-gray-800'
}

const loadTransactions = async () => {
  try {
    const params = {
      ...filters.value,
      // Filter by cash/bank transaction types (exclude journal entries)
      type: filters.value.type || 'receipt,payment,transfer',
    }
    const response = await transactionsApi.getAll(params)
    transactions.value = response.data || []
  } catch (error) {
    console.error('Error loading transactions:', error)
    transactions.value = []
  }
}

const editTransaction = (transaction) => {
  selectedTransaction.value = transaction
  showModal.value = true
}

const deleteTransaction = async (id) => {
  if (!confirm('Are you sure you want to delete this transaction?')) return

  try {
    await transactionsApi.delete(id)
    await loadTransactions()
  } catch (error) {
    console.error('Error deleting transaction:', error)
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

onMounted(() => {
  loadTransactions()
})
</script>
