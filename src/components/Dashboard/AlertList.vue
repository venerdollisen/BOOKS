<template>
  <div class="space-y-3">
    <div
      v-for="(alert, index) in alerts"
      :key="index"
      :class="[
        'flex items-center justify-between p-4 rounded-lg border',
        getAlertClass(alert.type),
      ]"
    >
      <div class="flex items-center space-x-3">
        <component :is="getIcon(alert.type)" class="h-5 w-5" />
        <p class="text-sm font-medium">{{ alert.message }}</p>
      </div>
      <router-link
        v-if="alert.action"
        :to="alert.action"
        class="text-sm font-medium text-primary-600 hover:text-primary-700"
      >
        View →
      </router-link>
    </div>
  </div>
</template>

<script setup>
import {
  ExclamationTriangleIcon,
  InformationCircleIcon,
  CheckCircleIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  alerts: {
    type: Array,
    default: () => [],
  },
})

const getAlertClass = (type) => {
  const classes = {
    warning: 'bg-yellow-50 border-yellow-200 text-yellow-800',
    info: 'bg-blue-50 border-blue-200 text-blue-800',
    success: 'bg-green-50 border-green-200 text-green-800',
    error: 'bg-red-50 border-red-200 text-red-800',
  }
  return classes[type] || classes.info
}

const getIcon = (type) => {
  const icons = {
    warning: ExclamationTriangleIcon,
    info: InformationCircleIcon,
    success: CheckCircleIcon,
    error: ExclamationTriangleIcon,
  }
  return icons[type] || InformationCircleIcon
}
</script>
