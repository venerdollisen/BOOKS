<template>
  <div class="space-y-6">
    <div class="card">
      <div class="mb-6">
        <h3 class="text-xl font-bold text-gray-900 mb-2">Trial Balance</h3>
        <p class="text-sm text-gray-500">As of {{ formatDate(new Date()) }}</p>
      </div>

      <div class="overflow-x-auto">
        <table class="table">
          <thead>
            <tr>
              <th>Account Code</th>
              <th>Account Name</th>
              <th class="text-right">Debit</th>
              <th class="text-right">Credit</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="account in accounts" :key="account.id">
              <td class="font-mono">{{ account.code }}</td>
              <td>{{ account.name }}</td>
              <td class="text-right">
                <span v-if="account.debit > 0" class="text-green-600">
                  {{ formatCurrency(account.debit) }}
                </span>
                <span v-else>-</span>
              </td>
              <td class="text-right">
                <span v-if="account.credit > 0" class="text-red-600">
                  {{ formatCurrency(account.credit) }}
                </span>
                <span v-else>-</span>
              </td>
            </tr>
            <tr class="bg-gray-50 font-bold border-t-4 border-gray-400">
              <td colspan="2">Total</td>
              <td class="text-right text-green-600">{{ formatCurrency(totalDebit) }}</td>
              <td class="text-right text-red-600">{{ formatCurrency(totalCredit) }}</td>
            </tr>
            <tr v-if="accounts.length === 0">
              <td colspan="4" class="text-center py-8 text-gray-500">No accounts found</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="totalDebit !== totalCredit" class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
        <p class="text-red-800 font-medium">
          ⚠️ Trial Balance is not balanced. Difference: {{ formatCurrency(Math.abs(totalDebit - totalCredit)) }}
        </p>
      </div>
      <div v-else class="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
        <p class="text-green-800 font-medium">✓ Trial Balance is balanced</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { reportsApi } from '@/services/api'

const accounts = ref([])

const totalDebit = computed(() => {
  return accounts.value.reduce((sum, account) => sum + (account.debit || 0), 0)
})

const totalCredit = computed(() => {
  return accounts.value.reduce((sum, account) => sum + (account.credit || 0), 0)
})

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(amount || 0)
}

const loadTrialBalance = async () => {
  try {
    const response = await reportsApi.getTrialBalance()
    accounts.value = response.data || []
  } catch (error) {
    console.error('Error loading trial balance:', error)
    accounts.value = []
  }
}

onMounted(() => {
  loadTrialBalance()
})
</script>
