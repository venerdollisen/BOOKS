<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h3 class="text-xl font-bold text-gray-900">Sales / Revenue</h3>
        <p class="text-sm text-gray-500 mt-1">Record sales and revenue transactions</p>
      </div>
      <button @click="showModal = true" class="btn btn-primary">
        <PlusIcon class="h-5 w-5 inline mr-2" />
        New Sale
      </button>
    </div>

    <!-- Sales Table -->
    <div class="card">
      <div class="overflow-x-auto">
        <table class="table">
          <thead>
            <tr>
              <th>Date</th>
              <th>Invoice #</th>
              <th>Customer</th>
              <th>Description</th>
              <th class="text-right">Amount</th>
              <th>Tax</th>
              <th class="text-right">Total</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="sale in sales" :key="sale.id">
              <td>{{ formatDate(sale.date) }}</td>
              <td class="font-mono text-sm">{{ sale.invoice_number }}</td>
              <td>{{ sale.customer?.name || '-' }}</td>
              <td>{{ sale.description }}</td>
              <td class="text-right">{{ formatCurrency(sale.amount) }}</td>
              <td class="text-right">{{ formatCurrency(sale.tax) }}</td>
              <td class="text-right font-medium">{{ formatCurrency(sale.total) }}</td>
              <td>
                <span
                  :class="[
                    'px-2 py-1 text-xs font-medium rounded-full',
                    sale.status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800',
                  ]"
                >
                  {{ sale.status }}
                </span>
              </td>
            </tr>
            <tr v-if="sales.length === 0">
              <td colspan="8" class="text-center py-8 text-gray-500">No sales found</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { transactionsApi } from '@/services/api'
import { PlusIcon } from '@heroicons/vue/24/outline'

const sales = ref([])
const showModal = ref(false)

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US')
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(amount || 0)
}

const loadSales = async () => {
  try {
    const response = await transactionsApi.getAll({ type: 'sales' })
    sales.value = response.data || []
  } catch (error) {
    console.error('Error loading sales:', error)
    sales.value = []
  }
}

onMounted(() => {
  loadSales()
})
</script>
