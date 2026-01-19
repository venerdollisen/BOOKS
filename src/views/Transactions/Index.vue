<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h2 class="text-2xl font-bold text-gray-900">Transactions</h2>
        <p class="text-sm text-gray-500 mt-1">Manage all financial transactions</p>
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

    <!-- Transaction content -->
    <router-view />
  </div>
</template>

<script setup>
const tabs = [
  { name: 'cash-bank', path: '/transactions/cash-bank', label: 'Cash / Bank' },
  { name: 'journal', path: '/transactions/journal', label: 'Journal Entries' },
  { name: 'sales', path: '/transactions/sales', label: 'Sales / Revenue' },
  { name: 'purchases', path: '/transactions/purchases', label: 'Purchases / Expenses' },
  { name: 'recurring', path: '/transactions/recurring', label: 'Recurring' },
]
</script>
