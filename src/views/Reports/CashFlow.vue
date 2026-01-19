<template>
  <div class="space-y-6">
    <div class="card">
      <div class="mb-6">
        <h3 class="text-xl font-bold text-gray-900 mb-2">Cash Flow Statement</h3>
        <p class="text-sm text-gray-500">Period: {{ formatPeriod(period) }}</p>
      </div>

      <div class="space-y-6">
        <!-- Operating Activities -->
        <div>
          <h4 class="text-lg font-semibold text-gray-900 mb-4">Operating Activities</h4>
          <div class="space-y-2">
            <div v-for="item in operating" :key="item.id" class="flex justify-between py-2">
              <span class="text-gray-700">{{ item.name }}</span>
              <span class="font-medium">{{ formatCurrency(item.amount) }}</span>
            </div>
            <div class="border-t-2 border-gray-300 pt-2 mt-2 flex justify-between font-bold">
              <span>Net Cash from Operating</span>
              <span>{{ formatCurrency(netOperating) }}</span>
            </div>
          </div>
        </div>

        <!-- Investing Activities -->
        <div>
          <h4 class="text-lg font-semibold text-gray-900 mb-4">Investing Activities</h4>
          <div class="space-y-2">
            <div v-for="item in investing" :key="item.id" class="flex justify-between py-2">
              <span class="text-gray-700">{{ item.name }}</span>
              <span class="font-medium">{{ formatCurrency(item.amount) }}</span>
            </div>
            <div class="border-t-2 border-gray-300 pt-2 mt-2 flex justify-between font-bold">
              <span>Net Cash from Investing</span>
              <span>{{ formatCurrency(netInvesting) }}</span>
            </div>
          </div>
        </div>

        <!-- Financing Activities -->
        <div>
          <h4 class="text-lg font-semibold text-gray-900 mb-4">Financing Activities</h4>
          <div class="space-y-2">
            <div v-for="item in financing" :key="item.id" class="flex justify-between py-2">
              <span class="text-gray-700">{{ item.name }}</span>
              <span class="font-medium">{{ formatCurrency(item.amount) }}</span>
            </div>
            <div class="border-t-2 border-gray-300 pt-2 mt-2 flex justify-between font-bold">
              <span>Net Cash from Financing</span>
              <span>{{ formatCurrency(netFinancing) }}</span>
            </div>
          </div>
        </div>

        <!-- Net Cash Flow -->
        <div class="border-t-4 border-gray-400 pt-4">
          <div class="flex justify-between text-xl font-bold">
            <span>Net Increase/Decrease in Cash</span>
            <span>{{ formatCurrency(netCashFlow) }}</span>
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

const operating = ref([])
const investing = ref([])
const financing = ref([])

const netOperating = computed(() => {
  return operating.value.reduce((sum, item) => sum + (item.amount || 0), 0)
})

const netInvesting = computed(() => {
  return investing.value.reduce((sum, item) => sum + (item.amount || 0), 0)
})

const netFinancing = computed(() => {
  return financing.value.reduce((sum, item) => sum + (item.amount || 0), 0)
})

const netCashFlow = computed(() => {
  return netOperating.value + netInvesting.value + netFinancing.value
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

const loadCashFlow = async () => {
  try {
    const response = await reportsApi.getCashFlow({ period: props.period })
    const data = response.data || {}
    operating.value = data.operating || []
    investing.value = data.investing || []
    financing.value = data.financing || []
  } catch (error) {
    console.error('Error loading cash flow:', error)
    operating.value = []
    investing.value = []
    financing.value = []
  }
}

onMounted(() => {
  loadCashFlow()
})
</script>
