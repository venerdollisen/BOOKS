<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h2 class="text-2xl font-bold text-gray-900">Financial Reports</h2>
        <p class="text-sm text-gray-500 mt-1">Generate and view financial statements</p>
      </div>
      <div class="flex space-x-3">
        <select v-model="selectedPeriod" class="input w-auto">
          <option value="monthly">Monthly</option>
          <option value="quarterly">Quarterly</option>
          <option value="yearly">Yearly</option>
          <option value="custom">Custom</option>
        </select>
        <button @click="exportReport" class="btn btn-primary">
          <ArrowDownTrayIcon class="h-5 w-5 inline mr-2" />
          Export
        </button>
      </div>
    </div>

    <!-- Sub-navigation -->
    <div class="card">
      <nav class="flex space-x-1" aria-label="Tabs">
        <router-link
          v-for="tab in tabs"
          :key="tab.name"
          :to="tab.path"
          class="px-4 py-2 text-sm font-medium rounded-lg transition-colors duration-200"
          :class="
            $route.path === tab.path
              ? 'bg-primary-600 text-white'
              : 'text-gray-600 hover:bg-gray-100'
          "
        >
          {{ tab.label }}
        </router-link>
      </nav>
    </div>

    <!-- Report content -->
    <router-view :period="selectedPeriod" />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { ArrowDownTrayIcon } from '@heroicons/vue/24/outline'
import { reportsApi } from '@/services/api'

const selectedPeriod = ref('monthly')
const tabs = [
  { name: 'balance-sheet', path: '/reports/balance-sheet', label: 'Balance Sheet' },
  { name: 'profit-loss', path: '/reports/profit-loss', label: 'Profit & Loss' },
  { name: 'cash-flow', path: '/reports/cash-flow', label: 'Cash Flow' },
  { name: 'trial-balance', path: '/reports/trial-balance', label: 'Trial Balance' },
]

const exportReport = async () => {
  const reportType = tabs.find((t) => t.path === location.pathname)?.name || 'balance-sheet'
  try {
    const response = await reportsApi.exportReport(reportType, 'pdf', {
      period: selectedPeriod.value,
    })
    const blob = new Blob([response.data], { type: 'application/pdf' })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = `${reportType}-${selectedPeriod.value}.pdf`
    link.click()
  } catch (error) {
    console.error('Error exporting report:', error)
  }
}
</script>
