<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h2 class="text-2xl font-bold text-gray-900">Accounts Payable</h2>
        <p class="text-sm text-gray-500 mt-1">Manage supplier bills and payments</p>
      </div>
      <button @click="showModal = true" class="btn btn-primary">
        <PlusIcon class="h-5 w-5 inline mr-2" />
        New Bill
      </button>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <div class="card">
        <p class="text-sm font-medium text-gray-600">Total Payables</p>
        <p class="mt-2 text-2xl font-bold text-gray-900">{{ formatCurrency(summary.total) }}</p>
      </div>
      <div class="card">
        <p class="text-sm font-medium text-gray-600">Overdue</p>
        <p class="mt-2 text-2xl font-bold text-red-600">{{ formatCurrency(summary.overdue) }}</p>
      </div>
      <div class="card">
        <p class="text-sm font-medium text-gray-600">0-30 Days</p>
        <p class="mt-2 text-2xl font-bold text-gray-900">{{ formatCurrency(summary['0-30']) }}</p>
      </div>
      <div class="card">
        <p class="text-sm font-medium text-gray-600">31-60 Days</p>
        <p class="mt-2 text-2xl font-bold text-gray-900">{{ formatCurrency(summary['31-60']) }}</p>
      </div>
    </div>

    <!-- Filters -->
    <div class="card">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="label">Supplier</label>
          <select v-model="filters.supplier_id" class="input">
            <option value="">All Suppliers</option>
          </select>
        </div>
        <div>
          <label class="label">Status</label>
          <select v-model="filters.status" class="input">
            <option value="">All Status</option>
            <option value="unpaid">Unpaid</option>
            <option value="partially_paid">Partially Paid</option>
            <option value="paid">Paid</option>
            <option value="overdue">Overdue</option>
          </select>
        </div>
        <div class="flex items-end">
          <button @click="loadBills" class="btn btn-secondary w-full">Apply Filters</button>
        </div>
      </div>
    </div>

    <!-- Bills Table -->
    <div class="card">
      <div class="overflow-x-auto">
        <table class="table">
          <thead>
            <tr>
              <th>Bill #</th>
              <th>Date</th>
              <th>Supplier</th>
              <th>Amount</th>
              <th>Paid</th>
              <th>Balance</th>
              <th>Due Date</th>
              <th>Status</th>
              <th class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="bill in bills" :key="bill.id">
              <td class="font-mono font-medium">{{ bill.bill_number }}</td>
              <td>{{ formatDate(bill.date) }}</td>
              <td>{{ bill.supplier?.name || '-' }}</td>
              <td class="font-medium">{{ formatCurrency(bill.total) }}</td>
              <td>{{ formatCurrency(bill.paid_amount) }}</td>
              <td class="font-medium">{{ formatCurrency(bill.balance) }}</td>
              <td>{{ formatDate(bill.due_date) }}</td>
              <td>
                <span
                  :class="[
                    'px-2 py-1 text-xs font-medium rounded-full',
                    getStatusClass(bill.status),
                  ]"
                >
                  {{ bill.status }}
                </span>
              </td>
              <td class="text-right">
                <button
                  @click="viewBill(bill)"
                  class="text-primary-600 hover:text-primary-700 mr-3"
                >
                  View
                </button>
                <button
                  v-if="bill.balance > 0"
                  @click="recordPayment(bill)"
                  class="text-green-600 hover:text-green-700"
                >
                  Pay
                </button>
              </td>
            </tr>
            <tr v-if="bills.length === 0">
              <td colspan="9" class="text-center py-8 text-gray-500">No bills found</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { payablesApi } from '@/services/api'
import { PlusIcon } from '@heroicons/vue/24/outline'

const bills = ref([])
const summary = ref({
  total: 0,
  overdue: 0,
  '0-30': 0,
  '31-60': 0,
})
const filters = ref({
  supplier_id: '',
  status: '',
})
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

const getStatusClass = (status) => {
  const classes = {
    paid: 'bg-green-100 text-green-800',
    unpaid: 'bg-yellow-100 text-yellow-800',
    partially_paid: 'bg-blue-100 text-blue-800',
    overdue: 'bg-red-100 text-red-800',
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const loadBills = async () => {
  try {
    const params = { ...filters.value }
    const response = await payablesApi.getBills(params)
    bills.value = response.data || []

    const aging = await payablesApi.getAgingReport()
    summary.value = aging.data || summary.value
  } catch (error) {
    console.error('Error loading bills:', error)
    bills.value = []
  }
}

const viewBill = (bill) => {
  // Navigate to bill detail or open modal
}

const recordPayment = async (bill) => {
  const amount = prompt(`Enter payment amount (Balance: ${formatCurrency(bill.balance)}):`)
  if (amount && parseFloat(amount) > 0) {
    try {
      await payablesApi.markPaid(bill.id, { amount: parseFloat(amount) })
      await loadBills()
    } catch (error) {
      console.error('Error recording payment:', error)
    }
  }
}

onMounted(() => {
  loadBills()
})
</script>
