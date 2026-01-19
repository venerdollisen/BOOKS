<template>
  <div class="overflow-x-auto">
    <table class="table">
      <thead>
        <tr>
          <th>Age Range</th>
          <th class="text-right">Amount</th>
          <th class="text-right">% of Total</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(amount, range) in data" :key="range">
          <td class="font-medium">{{ range }} days</td>
          <td class="text-right">{{ formatCurrency(amount) }}</td>
          <td class="text-right">{{ getPercentage(amount) }}%</td>
        </tr>
        <tr class="bg-gray-50 font-semibold">
          <td>Total</td>
          <td class="text-right">{{ formatCurrency(total) }}</td>
          <td class="text-right">100%</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  data: {
    type: Object,
    required: true,
  },
  type: {
    type: String,
    default: 'receivable',
  },
})

const total = computed(() => {
  return Object.values(props.data).reduce((sum, val) => sum + val, 0)
})

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(amount || 0)
}

const getPercentage = (amount) => {
  if (total.value === 0) return 0
  return ((amount / total.value) * 100).toFixed(1)
}
</script>
