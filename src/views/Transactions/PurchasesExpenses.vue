<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h3 class="text-xl font-bold text-gray-900">Purchases / Expenses</h3>
        <p class="text-sm text-gray-500 mt-1">Record purchase and expense transactions</p>
      </div>
      <button @click="showModal = true" class="btn btn-primary">
        <PlusIcon class="h-5 w-5 inline mr-2" />
        New Purchase
      </button>
    </div>

    <!-- Purchases Table -->
    <div class="card">
      <div class="overflow-x-auto">
        <table class="table">
          <thead>
            <tr>
              <th>Date</th>
              <th>Bill #</th>
              <th>Supplier</th>
              <th>Description</th>
              <th class="text-right">Amount</th>
              <th>Tax</th>
              <th class="text-right">Total</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="purchase in purchases" :key="purchase.id">
              <td>{{ formatDate(purchase.date) }}</td>
              <td class="font-mono text-sm">{{ purchase.bill_number }}</td>
              <td>{{ purchase.supplier?.name || '-' }}</td>
              <td>{{ purchase.description }}</td>
              <td class="text-right">{{ formatCurrency(purchase.amount) }}</td>
              <td class="text-right">{{ formatCurrency(purchase.tax) }}</td>
              <td class="text-right font-medium">{{ formatCurrency(purchase.total) }}</td>
              <td>
                <span
                  :class="[
                    'px-2 py-1 text-xs font-medium rounded-full',
                    purchase.status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800',
                  ]"
                >
                  {{ purchase.status }}
                </span>
              </td>
            </tr>
            <tr v-if="purchases.length === 0">
              <td colspan="8" class="text-center py-8 text-gray-500">No purchases found</td>
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

const purchases = ref([])
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

const loadPurchases = async () => {
  try {
    const response = await transactionsApi.getAll({ type: 'purchases' })
    purchases.value = response.data || []
  } catch (error) {
    console.error('Error loading purchases:', error)
    purchases.value = []
  }
}

onMounted(() => {
  loadPurchases()
})
</script>
