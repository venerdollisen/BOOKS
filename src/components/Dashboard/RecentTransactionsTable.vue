<template>
  <div class="overflow-x-auto">
    <table class="table">
      <thead>
        <tr>
          <th>Date</th>
          <th>Type</th>
          <th>Description</th>
          <th>Account</th>
          <th class="text-right">Amount</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="transaction in transactions"
          :key="transaction.id"
          class="cursor-pointer hover:bg-gray-50"
          @click="$emit('view-details', transaction.id)"
        >
          <td>{{ formatDate(transaction.date) }}</td>
          <td>
            <span
              :class="[
                'px-2 py-1 text-xs font-medium rounded-full',
                getTypeClass(transaction.type),
              ]"
            >
              {{ transaction.type }}
            </span>
          </td>
          <td>{{ transaction.description }}</td>
          <td>{{ transaction.account }}</td>
          <td
            :class="[
              'text-right font-medium',
              transaction.amount >= 0 ? 'text-green-600' : 'text-red-600',
            ]"
          >
            {{ formatCurrency(transaction.amount) }}
          </td>
          <td>
            <span
              :class="[
                'px-2 py-1 text-xs font-medium rounded-full',
                getStatusClass(transaction.status),
              ]"
            >
              {{ transaction.status }}
            </span>
          </td>
        </tr>
        <tr v-if="transactions.length === 0">
          <td colspan="6" class="text-center py-8 text-gray-500">
            No recent transactions
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
const props = defineProps({
  transactions: {
    type: Array,
    default: () => [],
  },
})

defineEmits(['view-details'])

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(amount || 0)
}

const getTypeClass = (type) => {
  const classes = {
    Cash: 'bg-green-100 text-green-800',
    Bank: 'bg-blue-100 text-blue-800',
    Sales: 'bg-purple-100 text-purple-800',
    Purchase: 'bg-orange-100 text-orange-800',
    Journal: 'bg-gray-100 text-gray-800',
  }
  return classes[type] || 'bg-gray-100 text-gray-800'
}

const getStatusClass = (status) => {
  const classes = {
    Paid: 'bg-green-100 text-green-800',
    Pending: 'bg-yellow-100 text-yellow-800',
    Overdue: 'bg-red-100 text-red-800',
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}
</script>
