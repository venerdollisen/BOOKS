<template>
  <div class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
      <div class="p-6 border-b border-gray-200 flex justify-between items-center">
        <div>
          <h3 class="text-xl font-bold text-gray-900">Invoice Details</h3>
          <p class="text-sm text-gray-500 mt-1">{{ invoice?.invoice_number }}</p>
        </div>
        <button @click="$emit('close')" class="text-gray-500 hover:text-gray-700 text-2xl">×</button>
      </div>

      <div class="p-6 space-y-6">
        <!-- Invoice Header -->
        <div class="grid grid-cols-2 gap-6">
          <div>
            <p class="text-xs font-semibold text-gray-600 uppercase">Bill To:</p>
            <p class="font-bold text-gray-900 mt-2">{{ invoice?.customer_name }}</p>
            <p v-if="invoice?.customer_email" class="text-sm text-gray-600">{{ invoice.customer_email }}</p>
            <p v-if="invoice?.customer_phone" class="text-sm text-gray-600">{{ invoice.customer_phone }}</p>
          </div>
          <div class="text-right">
            <div class="mb-4">
              <p class="text-xs font-semibold text-gray-600 uppercase">Invoice #</p>
              <p class="font-mono text-2xl font-bold text-gray-900">{{ invoice?.invoice_number }}</p>
            </div>
            <div>
              <p class="text-xs font-semibold text-gray-600 uppercase">Invoice Date</p>
              <p class="font-medium text-gray-900">{{ formatDate(invoice?.invoice_date) }}</p>
            </div>
          </div>
        </div>

        <!-- Invoice Details -->
        <div class="grid grid-cols-4 gap-4 bg-gray-50 p-4 rounded-lg text-sm">
          <div>
            <p class="text-gray-600 font-semibold">Due Date</p>
            <p class="text-gray-900 font-medium">{{ formatDate(invoice?.due_date) }}</p>
          </div>
          <div>
            <p class="text-gray-600 font-semibold">Total Amount</p>
            <p class="text-gray-900 font-medium">{{ formatCurrency(invoice?.total_amount) }}</p>
          </div>
          <div>
            <p class="text-gray-600 font-semibold">Paid</p>
            <p class="text-gray-900 font-medium">{{ formatCurrency(invoice?.paid_amount) }}</p>
          </div>
          <div>
            <p class="text-gray-600 font-semibold">Balance</p>
            <p class="font-medium" :class="balance > 0 ? 'text-orange-600' : 'text-green-600'">
              {{ formatCurrency(balance) }}
            </p>
          </div>
        </div>

        <!-- Line Items -->
        <div>
          <h4 class="font-semibold text-gray-900 mb-3">Line Items</h4>
          <div class="overflow-x-auto border border-gray-200 rounded-lg">
            <table class="w-full text-sm">
              <thead class="bg-gray-100">
                <tr>
                  <th class="px-4 py-2 text-left text-gray-900 font-semibold">Description</th>
                  <th class="px-4 py-2 text-right text-gray-900 font-semibold">Quantity</th>
                  <th class="px-4 py-2 text-right text-gray-900 font-semibold">Unit Price</th>
                  <th class="px-4 py-2 text-right text-gray-900 font-semibold">Amount</th>
                </tr>
              </thead>
              <tbody class="divide-y">
                <tr v-for="item in invoice?.items" :key="item.id" class="hover:bg-gray-50">
                  <td class="px-4 py-3 text-gray-900">{{ item.description }}</td>
                  <td class="px-4 py-3 text-right text-gray-600">{{ formatNumber(item.quantity) }}</td>
                  <td class="px-4 py-3 text-right text-gray-600">{{ formatCurrency(item.unit_price) }}</td>
                  <td class="px-4 py-3 text-right font-medium text-gray-900">{{ formatCurrency(item.amount) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Summary -->
        <div class="flex justify-end">
          <div class="w-full md:w-64 bg-gray-50 p-4 rounded-lg space-y-2">
            <div class="flex justify-between text-sm">
              <span class="text-gray-600">Subtotal:</span>
              <span class="font-medium">{{ formatCurrency(invoice?.total_amount) }}</span>
            </div>
            <div class="flex justify-between font-bold border-t border-gray-200 pt-2">
              <span>Total:</span>
              <span>{{ formatCurrency(invoice?.total_amount) }}</span>
            </div>
            <div class="flex justify-between font-bold border-t border-gray-200 pt-2 text-orange-600">
              <span>Remaining:</span>
              <span>{{ formatCurrency(balance) }}</span>
            </div>
          </div>
        </div>

        <!-- Status and Notes -->
        <div class="space-y-4 border-t pt-4">
          <div>
            <p class="text-xs font-semibold text-gray-600 uppercase">Status</p>
            <div class="mt-2">
              <span
                :class="[
                  'px-3 py-1 text-sm font-medium rounded-full',
                  getStatusClass(invoice?.status),
                ]"
              >
                {{ invoice?.status?.replace('_', ' ') || '-' }}
              </span>
            </div>
          </div>

          <div v-if="invoice?.notes">
            <p class="text-xs font-semibold text-gray-600 uppercase">Notes</p>
            <p class="text-sm text-gray-700 mt-2">{{ invoice.notes }}</p>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="p-6 border-t border-gray-200 flex justify-end gap-2 bg-gray-50">
        <button @click="$emit('close')" :disabled="loading" class="btn btn-secondary">Close</button>
        <button
          v-if="invoice?.status === 'draft'"
          @click="handleFinalize"
          :disabled="loading"
          class="btn btn-primary flex items-center gap-2"
          :class="{ 'opacity-50 cursor-not-allowed': loading }"
        >
          <span v-if="loading" class="inline-block animate-spin">⏳</span>
          {{ loading ? 'Finalizing...' : 'Finalize & Create Transaction' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
  invoice: Object,
  required: true,
})

const emit = defineEmits(['close', 'finalize'])

const loading = ref(false)

const balance = computed(() => {
  return (props.invoice?.total_amount || 0) - (props.invoice?.paid_amount || 0)
})

const handleFinalize = async () => {
  loading.value = true
  try {
    await emit('finalize', props.invoice)
  } finally {
    loading.value = false
  }
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('en-US')
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(amount || 0)
}

const formatNumber = (num) => {
  return Number(num || 0).toFixed(2)
}

const getStatusClass = (status) => {
  const classes = {
    paid: 'bg-green-100 text-green-800',
    unpaid: 'bg-yellow-100 text-yellow-800',
    partially_paid: 'bg-blue-100 text-blue-800',
    draft: 'bg-gray-100 text-gray-800',
    sent: 'bg-purple-100 text-purple-800',
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}
</script>
