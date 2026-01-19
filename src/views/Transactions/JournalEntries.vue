<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h3 class="text-xl font-bold text-gray-900">Journal Entries</h3>
        <p class="text-sm text-gray-500 mt-1">Manual double-entry bookkeeping</p>
      </div>
      <button @click="showModal = true" class="btn btn-primary">
        <PlusIcon class="h-5 w-5 inline mr-2" />
        New Entry
      </button>
    </div>

    <!-- Journal Entries Table -->
    <div class="card">
      <div class="overflow-x-auto">
        <table class="table">
          <thead>
            <tr>
              <th>Date</th>
              <th>Reference</th>
              <th>Description</th>
              <th class="text-right">Debit Total</th>
              <th class="text-right">Credit Total</th>
              <th class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <template v-for="entry in entries" :key="entry.id">
              <tr class="hover:bg-gray-50">
                <td>{{ formatDate(entry.date) }}</td>
                <td class="font-mono text-sm">{{ entry.reference || '-' }}</td>
                <td>{{ entry.description }}</td>
                <td class="text-right font-medium text-green-600">
                  {{ formatCurrency(entry.debit_total || 0) }}
                </td>
                <td class="text-right font-medium text-red-600">
                  {{ formatCurrency(entry.credit_total || 0) }}
                </td>
                <td class="text-right">
                  <button
                    @click="toggleDetails(entry.id)"
                    class="text-primary-600 hover:text-primary-700 mr-3"
                  >
                    {{ expandedEntries.includes(entry.id) ? 'Hide' : 'View' }}
                  </button>
                  <button
                    @click="editEntry(entry)"
                    class="text-primary-600 hover:text-primary-700 mr-3"
                  >
                    Edit
                  </button>
                </td>
              </tr>
              <!-- Expanded Details -->
              <tr v-if="expandedEntries.includes(entry.id)" class="bg-gray-50">
                <td colspan="6" class="p-4">
                  <div class="space-y-2">
                    <h5 class="font-semibold text-sm mb-2">Journal Entry Lines:</h5>
                    <table class="w-full text-sm">
                      <thead>
                        <tr class="border-b">
                          <th class="text-left py-2">Account</th>
                          <th class="text-right py-2">Debit</th>
                          <th class="text-right py-2">Credit</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr
                          v-for="(line, idx) in entry.entries"
                          :key="idx"
                          class="border-b"
                        >
                          <td class="py-2">{{ line.account?.name || 'N/A' }}</td>
                          <td class="text-right py-2 text-green-600">
                            {{ line.debit > 0 ? formatCurrency(line.debit) : '-' }}
                          </td>
                          <td class="text-right py-2 text-red-600">
                            {{ line.credit > 0 ? formatCurrency(line.credit) : '-' }}
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </td>
              </tr>
            </template>
            <tr v-if="entries.length === 0">
              <td colspan="6" class="text-center py-8 text-gray-500">No journal entries found</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Journal Entry Modal -->
    <JournalEntryModal
      v-if="showModal"
      :entry="selectedEntry"
      @close="showModal = false"
      @saved="handleEntrySaved"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { transactionsApi } from '@/services/api'
import JournalEntryModal from '@/components/Transactions/JournalEntryModal.vue'
import { PlusIcon } from '@heroicons/vue/24/outline'

const entries = ref([])
const showModal = ref(false)
const selectedEntry = ref(null)
const expandedEntries = ref([])

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US')
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(amount || 0)
}

const loadEntries = async () => {
  try {
    const response = await transactionsApi.getAll({ type: 'journal' })
    entries.value = response.data || []
  } catch (error) {
    console.error('Error loading journal entries:', error)
    entries.value = []
  }
}

const editEntry = (entry) => {
  selectedEntry.value = entry
  showModal.value = true
}

const toggleDetails = (entryId) => {
  const index = expandedEntries.value.indexOf(entryId)
  if (index > -1) {
    expandedEntries.value.splice(index, 1)
  } else {
    expandedEntries.value.push(entryId)
  }
}

const handleEntrySaved = () => {
  showModal.value = false
  selectedEntry.value = null
  loadEntries()
}

onMounted(() => {
  loadEntries()
})
</script>
