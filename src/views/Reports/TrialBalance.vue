<template>
  <div class="space-y-6">
    <!-- Header -->
    <div>
      <h1 class="text-3xl font-bold text-gray-900">Trial Balance</h1>
      <p class="text-sm text-gray-500 mt-1">Verify all accounts are balanced (Total Debits = Total Credits)</p>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="bg-white rounded-lg shadow p-8 text-center">
      <div class="inline-block">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-[#06275c]"></div>
      </div>
      <p class="mt-4 text-gray-600">Loading trial balance...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg shadow p-4">
      <p class="text-red-700">{{ error }}</p>
    </div>

    <!-- Trial Balance Table -->
    <div v-else class="bg-white rounded-lg shadow overflow-hidden">
      <!-- Status Bar -->
      <div class="bg-gray-50 border-b border-gray-200 p-4">
        <div class="grid grid-cols-3 gap-4">
          <div>
            <p class="text-sm text-gray-600">Total Debits</p>
            <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(report.totals.debits) }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-600">Total Credits</p>
            <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(report.totals.credits) }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-600">Status</p>
            <p
              :class="[
                'text-2xl font-bold mt-1',
                report.totals.is_balanced ? 'text-green-600' : 'text-red-600'
              ]"
            >
              {{ report.totals.is_balanced ? '✓ Balanced' : '✗ Not Balanced' }}
            </p>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-100 border-b border-gray-200">
            <tr>
              <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Code</th>
              <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Account Name</th>
              <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Type</th>
              <th class="px-6 py-3 text-right text-sm font-semibold text-gray-900">Debits</th>
              <th class="px-6 py-3 text-right text-sm font-semibold text-gray-900">Credits</th>
              <th class="px-6 py-3 text-right text-sm font-semibold text-gray-900">Balance</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr
              v-for="account in report.data"
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
              <td class="px-6 py-4 text-sm text-right text-gray-900 font-mono">
                {{ account.debits > 0 ? formatCurrency(account.debits) : '-' }}
              </td>
              <td class="px-6 py-4 text-sm text-right text-gray-900 font-mono">
                {{ account.credits > 0 ? formatCurrency(account.credits) : '-' }}
              </td>
              <td class="px-6 py-4 text-sm text-right font-mono font-semibold" :class="getBalanceClass(account.balance)">
                {{ formatCurrency(account.balance) }}
              </td>
            </tr>

            <!-- Totals Row -->
            <tr class="bg-gray-50 border-t-2 border-gray-300 font-bold">
              <td colspan="3" class="px-6 py-4 text-right text-sm">TOTALS</td>
              <td class="px-6 py-4 text-sm text-right text-gray-900 font-mono">
                {{ formatCurrency(report.totals.debits) }}
              </td>
              <td class="px-6 py-4 text-sm text-right text-gray-900 font-mono">
                {{ formatCurrency(report.totals.credits) }}
              </td>
              <td :class="['px-6 py-4 text-sm text-right font-mono', report.totals.is_balanced ? 'text-green-600' : 'text-red-600']">
                {{ formatCurrency(report.totals.difference) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/config/api'

const loading = ref(false)
const error = ref('')
const report = ref({
  data: [],
  totals: {
    debits: 0,
    credits: 0,
    difference: 0,
    is_balanced: false,
  },
})

function formatCurrency(value) {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(value)
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

async function fetchTrialBalance() {
  loading.value = true
  error.value = ''

  try {
    const response = await api.get('/reports/trial-balance')
    if (response.data.success) {
      report.value = response.data
    } else {
      error.value = 'Failed to load trial balance'
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Error loading trial balance'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchTrialBalance()
})
</script>
