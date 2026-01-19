<template>
  <div class="space-y-6">
    <div class="card">
      <div class="mb-6">
        <h3 class="text-xl font-bold text-gray-900 mb-2">Balance Sheet</h3>
        <p class="text-sm text-gray-500">As of {{ formatDate(new Date()) }}</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Assets -->
        <div>
          <h4 class="text-lg font-semibold text-gray-900 mb-4">Assets</h4>
          <div class="space-y-2">
            <div v-for="item in assets" :key="item.id" class="flex justify-between py-2">
              <span class="text-gray-700">{{ item.name }}</span>
              <span class="font-medium">{{ formatCurrency(item.balance) }}</span>
            </div>
            <div class="border-t-2 border-gray-300 pt-2 mt-2 flex justify-between font-bold">
              <span>Total Assets</span>
              <span>{{ formatCurrency(totalAssets) }}</span>
            </div>
          </div>
        </div>

        <!-- Liabilities & Equity -->
        <div>
          <div class="mb-6">
            <h4 class="text-lg font-semibold text-gray-900 mb-4">Liabilities</h4>
            <div class="space-y-2">
              <div v-for="item in liabilities" :key="item.id" class="flex justify-between py-2">
                <span class="text-gray-700">{{ item.name }}</span>
                <span class="font-medium">{{ formatCurrency(item.balance) }}</span>
              </div>
              <div class="border-t-2 border-gray-300 pt-2 mt-2 flex justify-between font-bold">
                <span>Total Liabilities</span>
                <span>{{ formatCurrency(totalLiabilities) }}</span>
              </div>
            </div>
          </div>

          <div>
            <h4 class="text-lg font-semibold text-gray-900 mb-4">Equity</h4>
            <div class="space-y-2">
              <div v-for="item in equity" :key="item.id" class="flex justify-between py-2">
                <span class="text-gray-700">{{ item.name }}</span>
                <span class="font-medium">{{ formatCurrency(item.balance) }}</span>
              </div>
              <div class="border-t-2 border-gray-300 pt-2 mt-2 flex justify-between font-bold">
                <span>Total Equity</span>
                <span>{{ formatCurrency(totalEquity) }}</span>
              </div>
            </div>
          </div>

          <div class="border-t-4 border-gray-400 pt-4 mt-4 flex justify-between text-xl font-bold">
            <span>Total Liabilities & Equity</span>
            <span>{{ formatCurrency(totalLiabilities + totalEquity) }}</span>
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

const assets = ref([])
const liabilities = ref([])
const equity = ref([])

const totalAssets = computed(() => {
  return assets.value.reduce((sum, item) => sum + (item.balance || 0), 0)
})

const totalLiabilities = computed(() => {
  return liabilities.value.reduce((sum, item) => sum + (item.balance || 0), 0)
})

const totalEquity = computed(() => {
  return equity.value.reduce((sum, item) => sum + (item.balance || 0), 0)
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

const loadBalanceSheet = async () => {
  try {
    const response = await reportsApi.getBalanceSheet({ period: props.period })
    const data = response.data || {}
    assets.value = data.assets || []
    liabilities.value = data.liabilities || []
    equity.value = data.equity || []
  } catch (error) {
    console.error('Error loading balance sheet:', error)
    // Mock data for development
    assets.value = [
      { id: 1, name: 'Cash', balance: 50000 },
      { id: 2, name: 'Bank Accounts', balance: 100000 },
      { id: 3, name: 'Accounts Receivable', balance: 25000 },
    ]
    liabilities.value = [
      { id: 1, name: 'Accounts Payable', balance: 15000 },
      { id: 2, name: 'Loans', balance: 50000 },
    ]
    equity.value = [{ id: 1, name: 'Owner\'s Capital', balance: 110000 }]
  }
}

onMounted(() => {
  loadBalanceSheet()
})
</script>
