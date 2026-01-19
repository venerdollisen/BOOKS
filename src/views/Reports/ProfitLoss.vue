<template>
  <div class="space-y-6">
    <div class="card">
      <div class="mb-6">
        <h3 class="text-xl font-bold text-gray-900 mb-2">Profit & Loss Statement</h3>
        <p class="text-sm text-gray-500">Period: {{ formatPeriod(period) }}</p>
      </div>

      <div class="space-y-6">
        <!-- Revenue -->
        <div>
          <h4 class="text-lg font-semibold text-gray-900 mb-4">Revenue</h4>
          <div class="space-y-2">
            <div v-for="item in revenue" :key="item.id" class="flex justify-between py-2">
              <span class="text-gray-700">{{ item.name }}</span>
              <span class="font-medium text-green-600">{{ formatCurrency(item.amount) }}</span>
            </div>
            <div class="border-t-2 border-gray-300 pt-2 mt-2 flex justify-between font-bold">
              <span>Total Revenue</span>
              <span class="text-green-600">{{ formatCurrency(totalRevenue) }}</span>
            </div>
          </div>
        </div>

        <!-- Expenses -->
        <div>
          <h4 class="text-lg font-semibold text-gray-900 mb-4">Expenses</h4>
          <div class="space-y-2">
            <div v-for="item in expenses" :key="item.id" class="flex justify-between py-2">
              <span class="text-gray-700">{{ item.name }}</span>
              <span class="font-medium text-red-600">{{ formatCurrency(item.amount) }}</span>
            </div>
            <div class="border-t-2 border-gray-300 pt-2 mt-2 flex justify-between font-bold">
              <span>Total Expenses</span>
              <span class="text-red-600">{{ formatCurrency(totalExpenses) }}</span>
            </div>
          </div>
        </div>

        <!-- Net Profit -->
        <div class="border-t-4 border-gray-400 pt-4">
          <div class="flex justify-between text-xl font-bold">
            <span>Net Profit / Loss</span>
            <span :class="netProfit >= 0 ? 'text-green-600' : 'text-red-600'">
              {{ formatCurrency(netProfit) }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { reportsApi } from '@/services/api'

const props = defineProps({
  period: {
    type: String,
    default: 'monthly',
  },
})

const revenue = ref([])
const expenses = ref([])

const totalRevenue = computed(() => {
  return revenue.value.reduce((sum, item) => sum + (item.amount || 0), 0)
})

const totalExpenses = computed(() => {
  return expenses.value.reduce((sum, item) => sum + (item.amount || 0), 0)
})

const netProfit = computed(() => {
  return totalRevenue.value - totalExpenses.value
})

const formatPeriod = (period) => {
  const periods = {
    monthly: 'Monthly',
    quarterly: 'Quarterly',
    yearly: 'Yearly',
  }
  return periods[period] || period
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(amount || 0)
}

const loadProfitLoss = async () => {
  try {
    const response = await reportsApi.getProfitLoss({ period: props.period })
    const data = response.data || {}
    revenue.value = data.revenue || []
    expenses.value = data.expenses || []
  } catch (error) {
    console.error('Error loading profit & loss:', error)
    // Mock data for development
    revenue.value = [
      { id: 1, name: 'Sales Revenue', amount: 100000 },
      { id: 2, name: 'Service Income', amount: 50000 },
    ]
    expenses.value = [
      { id: 1, name: 'Salaries', amount: 30000 },
      { id: 2, name: 'Rent', amount: 10000 },
      { id: 3, name: 'Utilities', amount: 5000 },
    ]
  }
}

onMounted(() => {
  loadProfitLoss()
})
</script>
