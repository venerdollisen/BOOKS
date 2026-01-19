<template>
  <div class="overflow-x-auto">
    <table class="table">
      <thead>
        <tr>
          <th>Item</th>
          <th class="text-right">Amount</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="font-medium">Total Revenue</td>
          <td class="text-right text-green-600">{{ formatCurrency(data.revenue) }}</td>
        </tr>
        <tr>
          <td class="font-medium">Total Expenses</td>
          <td class="text-right text-red-600">({{ formatCurrency(data.expenses) }})</td>
        </tr>
        <tr class="bg-gray-50 font-semibold border-t-2 border-gray-300">
          <td>Net Profit/Loss</td>
          <td
            :class="[
              'text-right',
              data.net >= 0 ? 'text-green-600' : 'text-red-600',
            ]"
          >
            {{ formatCurrency(data.net) }}
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
const props = defineProps({
  data: {
    type: Object,
    required: true,
  },
})

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(amount || 0)
}
</script>
