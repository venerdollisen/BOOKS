<template>
  <div class="space-y-6">
    <!-- Header -->
    <div>
      <h1 class="text-3xl font-bold text-gray-900">General Ledger</h1>
      <p class="text-sm text-gray-500 mt-1">Detailed account transactions with running balances</p>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="bg-white rounded-lg shadow p-8 text-center">
      <div class="inline-block">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-[#06275c]"></div>
      </div>
      <p class="mt-4 text-gray-600">Loading general ledger...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg shadow p-4">
      <p class="text-red-700">{{ error }}</p>
    </div>

    <!-- GL Summary Table -->
    <div v-else class="bg-white rounded-lg shadow overflow-hidden">
      <!-- Summary Stats -->
      <div class="bg-gray-50 border-b border-gray-200 p-4">
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
          <div>
            <p class="text-sm text-gray-600">Assets</p>
            <p class="text-xl font-bold" :class="totals.assets >= 0 ? 'text-green-600' : 'text-red-600'">
              {{ formatCurrency(totals.assets) }}
            </p>
          </div>
          <div>
            <p class="text-sm text-gray-600">Liabilities</p>
            <p class="text-xl font-bold" :class="totals.liabilities >= 0 ? 'text-blue-600' : 'text-red-600'">
              {{ formatCurrency(totals.liabilities) }}
            </p>
          </div>
          <div>
            <p class="text-sm text-gray-600">Equity</p>
            <p class="text-xl font-bold text-purple-600">
              {{ formatCurrency(totals.equity) }}
            </p>
          </div>
          <div>
            <p class="text-sm text-gray-600">Revenue</p>
            <p class="text-xl font-bold text-green-600">
              {{ formatCurrency(totals.revenue) }}
            </p>
          </div>
          <div>
            <p class="text-sm text-gray-600">Expenses</p>
            <p class="text-xl font-bold text-orange-600">
              {{ formatCurrency(totals.expense) }}
            </p>
          </div>
        </div>
      </div>

      <!-- GL Summary Table -->
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-100 border-b border-gray-200">
            <tr>
              <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Code</th>
              <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Account Name</th>
              <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Type</th>
              <th class="px-6 py-3 text-right text-sm font-semibold text-gray-900">Balance</th>
              <th class="px-6 py-3 text-center text-sm font-semibold text-gray-900">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr
              v-for="account in glData"
              :key="account.id"
              class="hover:bg-gray-50 transition"
            >
              <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ account.code }}</td>
              <td class="px-6 py-4 text-sm text-gray-700">{{ account.name }}</td>
              <td class="px-6 py-4 text-sm">
                <span
                  :class="[
                    'px-2 py-1 rounded-full text-xs font-medium',
                    getTypeClass(account.type)
                  ]"
                >
                  {{ account.type }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm text-right font-mono font-semibold" :class="getBalanceClass(account.balance)">
                {{ formatCurrency(account.balance) }}
              </td>
              <td class="px-6 py-4 text-center">
                <button
                  @click="viewDetails(account.id)"
                  class="text-[#06275c] hover:text-[#051f47] font-medium text-sm"
                >
                  Details →
                </button>
              </td>
            </tr>

            <tr v-if="glData.length === 0">
              <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                No account activity found
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- GL Detail Modal -->
    <div v-if="showDetail" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
        <!-- Header -->
        <div class="sticky top-0 bg-white border-b border-gray-200 p-6 flex justify-between items-center">
          <div>
            <h2 class="text-2xl font-bold text-gray-900">{{ selectedAccount.code }} - {{ selectedAccount.name }}</h2>
            <p class="text-sm text-gray-500 mt-1">Final Balance: {{ formatCurrency(selectedAccount.finalBalance) }}</p>
          </div>
          <button
            @click="showDetail = false"
            class="text-gray-400 hover:text-gray-600 text-2xl"
          >
            ×
          </button>
        </div>

        <!-- Detail Table -->
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
              <tr>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Date</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Reference</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Description</th>
                <th class="px-6 py-3 text-right text-sm font-semibold text-gray-900">Debit</th>
                <th class="px-6 py-3 text-right text-sm font-semibold text-gray-900">Credit</th>
                <th class="px-6 py-3 text-right text-sm font-semibold text-gray-900">Balance</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr
                v-for="item in selectedAccountDetail"
                :key="item.id"
                class="hover:bg-gray-50 transition"
              >
                <td class="px-6 py-3 text-sm text-gray-700">{{ formatDate(item.date) }}</td>
                <td class="px-6 py-3 text-sm font-medium text-gray-900">{{ item.reference }}</td>
                <td class="px-6 py-3 text-sm text-gray-700">{{ item.description }}</td>
                <td class="px-6 py-3 text-sm text-right font-mono" v-if="item.debit">
                  {{ formatCurrency(item.debit) }}
                </td>
                <td class="px-6 py-3 text-sm text-right text-gray-400" v-else>-</td>
                <td class="px-6 py-3 text-sm text-right font-mono" v-if="item.credit">
                  {{ formatCurrency(item.credit) }}
                </td>
                <td class="px-6 py-3 text-sm text-right text-gray-400" v-else>-</td>
                <td class="px-6 py-3 text-sm text-right font-mono font-semibold" :class="getBalanceClass(item.balance)">
                  {{ formatCurrency(item.balance) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/config/api'

const loading = ref(false)
const error = ref('')
const showDetail = ref(false)
const glData = ref([])
const selectedAccount = ref({})
const selectedAccountDetail = ref([])
const totals = ref({
  assets: 0,
  liabilities: 0,
  equity: 0,
  revenue: 0,
  expense: 0,
})

function formatCurrency(value) {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(value)
}

function formatDate(dateStr) {
  return new Date(dateStr).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: '2-digit',
  })
}

function getTypeClass(type) {
  const classes = {
    asset: 'bg-blue-100 text-blue-800',
    liability: 'bg-red-100 text-red-800',
    equity: 'bg-green-100 text-green-800',
    revenue: 'bg-purple-100 text-purple-800',
    expense: 'bg-orange-100 text-orange-800',
  }
  return classes[type] || 'bg-gray-100 text-gray-800'
}

function getBalanceClass(balance) {
  if (balance === 0) return 'text-gray-900'
  return balance > 0 ? 'text-green-600' : 'text-red-600'
}

async function fetchGLSummary() {
  loading.value = true
  error.value = ''

  try {
    const response = await api.get('/reports/gl-summary')
    if (response.data.success) {
      glData.value = response.data.data
      totals.value = response.data.totals
    } else {
      error.value = 'Failed to load GL summary'
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Error loading GL summary'
  } finally {
    loading.value = false
  }
}

async function viewDetails(accountId) {
  try {
    const response = await api.get(`/reports/gl/${accountId}`)
    if (response.data.success) {
      selectedAccount.value = {
        ...response.data.account,
        finalBalance: response.data.final_balance,
      }
      selectedAccountDetail.value = response.data.data
      showDetail.value = true
    }
  } catch (err) {
    error.value = 'Error loading account details'
  }
}

onMounted(() => {
  fetchGLSummary()
})
</script>
