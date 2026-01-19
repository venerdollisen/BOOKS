<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h2 class="text-2xl font-bold text-gray-900">Dashboard</h2>
        <p class="text-sm text-gray-500 mt-1">Financial overview and quick insights</p>
      </div>
      <div class="flex space-x-3">
        <select
          v-model="selectedPeriod"
          @change="loadDashboardData"
          class="input w-auto"
        >
          <option value="monthly">Monthly</option>
          <option value="quarterly">Quarterly</option>
          <option value="yearly">Yearly</option>
        </select>
        <button class="btn btn-primary">
          <ArrowDownTrayIcon class="h-5 w-5 inline mr-2" />
          Export
        </button>
      </div>
    </div>

    <!-- Cash Balance -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <DashboardCard
        title="Cash Balance"
        :value="formatCurrency(cashBalance.total)"
        :change="cashBalance.change"
        icon="currency-dollar"
        color="green"
      />
      <DashboardCard
        title="Accounts Receivable"
        :value="formatCurrency(receivables.total)"
        :subtitle="`${receivables.overdue} overdue`"
        icon="credit-card"
        color="blue"
      />
      <DashboardCard
        title="Accounts Payable"
        :value="formatCurrency(payables.total)"
        :subtitle="`${payables.overdue} overdue`"
        icon="document-text"
        color="orange"
      />
      <DashboardCard
        title="Net Profit"
        :value="formatCurrency(profitLoss.net)"
        :change="profitLoss.change"
        icon="chart-bar"
        color="purple"
      />
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div class="card">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Cash Flow Trend</h3>
        <CashFlowChart :data="cashFlowData" />
      </div>
      <div class="card">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Expense Distribution</h3>
        <ExpenseChart :data="expenseData" />
      </div>
    </div>

    <!-- Aging Summary -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div class="card">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Receivables Aging</h3>
        <AgingTable :data="receivablesAging" type="receivable" />
      </div>
      <div class="card">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Payables Aging</h3>
        <AgingTable :data="payablesAging" type="payable" />
      </div>
    </div>

    <!-- Profit & Loss Overview -->
    <div class="card">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">Profit & Loss Overview</h3>
      <ProfitLossTable :data="profitLossDetails" />
    </div>

    <!-- Recent Transactions -->
    <div class="card">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-900">Recent Transactions</h3>
        <router-link to="/transactions" class="text-sm text-primary-600 hover:text-primary-700">
          View All →
        </router-link>
      </div>
      <RecentTransactionsTable :transactions="recentTransactions" />
    </div>

    <!-- Notifications/Alerts -->
    <div v-if="alerts.length > 0" class="card">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">Alerts & Notifications</h3>
      <AlertList :alerts="alerts" />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { dashboardApi } from '@/services/api'
import DashboardCard from '@/components/Dashboard/DashboardCard.vue'
import CashFlowChart from '@/components/Dashboard/CashFlowChart.vue'
import ExpenseChart from '@/components/Dashboard/ExpenseChart.vue'
import AgingTable from '@/components/Dashboard/AgingTable.vue'
import ProfitLossTable from '@/components/Dashboard/ProfitLossTable.vue'
import RecentTransactionsTable from '@/components/Dashboard/RecentTransactionsTable.vue'
import AlertList from '@/components/Dashboard/AlertList.vue'
import { ArrowDownTrayIcon } from '@heroicons/vue/24/outline'

const selectedPeriod = ref('monthly')
const cashBalance = ref({ total: 0, change: 0 })
const receivables = ref({ total: 0, overdue: 0 })
const payables = ref({ total: 0, overdue: 0 })
const profitLoss = ref({ net: 0, change: 0 })
const cashFlowData = ref([])
const expenseData = ref([])
const receivablesAging = ref({ '0-30': 0, '31-60': 0, '61+': 0 })
const payablesAging = ref({ '0-30': 0, '31-60': 0, '61+': 0 })
const profitLossDetails = ref({ revenue: 0, expenses: 0, net: 0 })
const recentTransactions = ref([])
const alerts = ref([])

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(amount || 0)
}

const loadDashboardData = async () => {
  try {
    // Load all dashboard data
    const [summary, receivablesPayables, profitLossData, recent] = await Promise.all([
      dashboardApi.getSummary(),
      dashboardApi.getReceivablesPayables(),
      dashboardApi.getProfitLoss(selectedPeriod.value),
      dashboardApi.getRecentTransactions(10),
    ])

    // Update reactive refs (adjust based on actual API response structure)
    cashBalance.value = summary.data.cashBalance || { total: 0, change: 0 }
    receivables.value = receivablesPayables.data.receivables || { total: 0, overdue: 0 }
    payables.value = receivablesPayables.data.payables || { total: 0, overdue: 0 }
    profitLoss.value = profitLossData.data || { net: 0, change: 0 }
    recentTransactions.value = recent.data || []

    // Mock data for charts (replace with actual API data)
    cashFlowData.value = [
      { month: 'Jan', amount: 50000 },
      { month: 'Feb', amount: 55000 },
      { month: 'Mar', amount: 52000 },
      { month: 'Apr', amount: 60000 },
    ]

    expenseData.value = [
      { category: 'Salaries', amount: 30000 },
      { category: 'Rent', amount: 10000 },
      { category: 'Utilities', amount: 5000 },
      { category: 'Supplies', amount: 3000 },
    ]

    receivablesAging.value = receivablesPayables.data.receivablesAging || {
      '0-30': 0,
      '31-60': 0,
      '61+': 0,
    }
    payablesAging.value = receivablesPayables.data.payablesAging || {
      '0-30': 0,
      '31-60': 0,
      '61+': 0,
    }

    profitLossDetails.value = profitLossData.data || { revenue: 0, expenses: 0, net: 0 }

    alerts.value = [
      { type: 'warning', message: '3 invoices are overdue', action: '/receivables' },
      { type: 'info', message: 'Low stock alert: Office Supplies', action: '/inventory' },
    ]
  } catch (error) {
    console.error('Error loading dashboard data:', error)
  }
}

onMounted(() => {
  loadDashboardData()
})
</script>
