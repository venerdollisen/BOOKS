<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h3 class="text-xl font-bold text-gray-900">Recurring Transactions</h3>
        <p class="text-sm text-gray-500 mt-1">Automate recurring payments and entries</p>
      </div>
      <button @click="showModal = true" class="btn btn-primary">
        <PlusIcon class="h-5 w-5 inline mr-2" />
        New Recurring
      </button>
    </div>

    <!-- Recurring Transactions Table -->
    <div class="card">
      <div class="overflow-x-auto">
        <table class="table">
          <thead>
            <tr>
              <th>Description</th>
              <th>Type</th>
              <th>Frequency</th>
              <th>Amount</th>
              <th>Start Date</th>
              <th>End Date</th>
              <th>Next Run</th>
              <th>Status</th>
              <th class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="recurring in recurringTransactions" :key="recurring.id">
              <td class="font-medium">{{ recurring.description }}</td>
              <td>{{ recurring.type }}</td>
              <td>{{ recurring.frequency }}</td>
              <td class="font-medium">{{ formatCurrency(recurring.amount) }}</td>
              <td>{{ formatDate(recurring.start_date) }}</td>
              <td>{{ formatDate(recurring.end_date) || 'No end' }}</td>
              <td>{{ formatDate(recurring.next_run) }}</td>
              <td>
                <span
                  :class="[
                    'px-2 py-1 text-xs font-medium rounded-full',
                    recurring.active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800',
                  ]"
                >
                  {{ recurring.active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="text-right">
                <button
                  @click="editRecurring(recurring)"
                  class="text-primary-600 hover:text-primary-700 mr-3"
                >
                  Edit
                </button>
                <button
                  @click="deleteRecurring(recurring.id)"
                  class="text-red-600 hover:text-red-700"
                >
                  Delete
                </button>
              </td>
            </tr>
            <tr v-if="recurringTransactions.length === 0">
              <td colspan="9" class="text-center py-8 text-gray-500">
                No recurring transactions found
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { transactionsApi } from '@/services/api'
import { PlusIcon } from '@heroicons/vue/24/outline'

const recurringTransactions = ref([])
const showModal = ref(false)

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

const loadRecurring = async () => {
  try {
    const response = await transactionsApi.getRecurring()
    recurringTransactions.value = response.data || []
  } catch (error) {
    console.error('Error loading recurring transactions:', error)
    recurringTransactions.value = []
  }
}

const editRecurring = (recurring) => {
  showModal.value = true
}

const deleteRecurring = async (id) => {
  if (!confirm('Are you sure you want to delete this recurring transaction?')) return

  try {
    await transactionsApi.deleteRecurring(id)
    await loadRecurring()
  } catch (error) {
    console.error('Error deleting recurring transaction:', error)
  }
}

onMounted(() => {
  loadRecurring()
})
</script>
