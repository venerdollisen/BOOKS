<template>
  <div class="card">
    <div class="flex items-center justify-between">
      <div class="flex-1">
        <p class="text-sm font-medium text-gray-600">{{ title }}</p>
        <p class="mt-2 text-3xl font-bold text-gray-900">{{ value }}</p>
        <p v-if="subtitle" class="mt-1 text-sm text-gray-500">{{ subtitle }}</p>
        <div v-if="change !== undefined" class="mt-2 flex items-center">
          <span
            :class="[
              'text-sm font-medium',
              change >= 0 ? 'text-green-600' : 'text-red-600',
            ]"
          >
            {{ change >= 0 ? '+' : '' }}{{ change }}%
          </span>
          <span class="ml-2 text-sm text-gray-500">vs last period</span>
        </div>
      </div>
      <div
        :class="[
          'h-12 w-12 rounded-lg flex items-center justify-center',
          colorClasses[color] || colorClasses.blue,
        ]"
      >
        <component :is="getIcon" class="h-6 w-6 text-white" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import {
  CurrencyDollarIcon,
  CreditCardIcon,
  DocumentTextIcon,
  ChartBarIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  title: {
    type: String,
    required: true,
  },
  value: {
    type: String,
    required: true,
  },
  subtitle: {
    type: String,
    default: null,
  },
  change: {
    type: Number,
    default: undefined,
  },
  icon: {
    type: String,
    default: 'chart-bar',
  },
  color: {
    type: String,
    default: 'blue',
  },
})

const colorClasses = {
  green: 'bg-green-500',
  blue: 'bg-blue-500',
  orange: 'bg-orange-500',
  purple: 'bg-purple-500',
  red: 'bg-red-500',
}

const iconMap = {
  'currency-dollar': CurrencyDollarIcon,
  'credit-card': CreditCardIcon,
  'document-text': DocumentTextIcon,
  'chart-bar': ChartBarIcon,
}

const getIcon = computed(() => {
  return iconMap[props.icon] || ChartBarIcon
})
</script>
