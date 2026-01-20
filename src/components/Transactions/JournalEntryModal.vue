<template>
  <div class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
      <div class="p-6 border-b border-gray-200">
        <h3 class="text-xl font-bold text-gray-900">
          {{ entry ? 'Edit Journal Entry' : 'New Journal Entry' }}
        </h3>
        <p class="text-sm text-gray-500 mt-1">Double-entry bookkeeping: Debits must equal Credits</p>
      </div>
      <form @submit.prevent="saveEntry" class="p-6 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="label">Date *</label>
            <input v-model="form.date" type="date" required class="input" />
          </div>
          <div>
            <label class="label">Reference</label>
            <input v-model="form.reference" type="text" class="input" />
          </div>
        </div>

        <div>
          <label class="label">Description *</label>
          <textarea v-model="form.description" rows="2" required class="input"></textarea>
        </div>

        <!-- Double Entry Lines -->
        <div class="border-t border-gray-200 pt-4">
          <div class="flex justify-between items-center mb-4">
            <h4 class="text-lg font-semibold text-gray-900">Journal Entries</h4>
            <button type="button" @click="addEntry" class="btn btn-secondary text-sm">
              <PlusIcon class="h-4 w-4 inline mr-1" />
              Add Entry
            </button>
          </div>

          <div class="space-y-3">
            <div
              v-for="(line, index) in form.entries"
              :key="index"
              class="grid grid-cols-14 gap-2 items-end p-3 bg-gray-50 rounded-lg"
            >
              <div class="col-span-2">
                <label class="label text-xs">Account *</label>
                <select v-model="line.account_id" required class="input text-sm">
                  <option value="">Select Account</option>
                  <option value="1">Cash</option>
                  <option value="2">Bank Account</option>
                  <option value="3">Accounts Receivable</option>
                  <option value="4">Accounts Payable</option>
                  <option value="5">Revenue</option>
                  <option value="6">Expenses</option>
                  <option value="7">Inventory</option>
                  <option value="8">Equipment</option>
                </select>
              </div>
              <div class="col-span-1">
                <label class="label text-xs">Debit</label>
                <input
                  v-model.number="line.debit"
                  type="number"
                  step="0.01"
                  min="0"
                  class="input text-sm"
                  placeholder="0.00"
                  @input="updateLine(index)"
                />
              </div>
              <div class="col-span-1">
                <label class="label text-xs">Credit</label>
                <input
                  v-model.number="line.credit"
                  type="number"
                  step="0.01"
                  min="0"
                  class="input text-sm"
                  placeholder="0.00"
                  @input="updateLine(index)"
                />
              </div>
              <div class="col-span-2">
                <label class="label text-xs">Subsidiary</label>
                <select v-model="line.subsidiary_account_id" class="input text-sm">
                  <option value="">None</option>
                  <option value="1">Head Office</option>
                  <option value="2">Branch 1</option>
                  <option value="3">Branch 2</option>
                </select>
              </div>
              <div class="col-span-2">
                <label class="label text-xs">Department</label>
                <select v-model="line.department_id" class="input text-sm">
                  <option value="">None</option>
                  <option value="1">Sales</option>
                  <option value="2">Operations</option>
                  <option value="3">Finance</option>
                </select>
              </div>
              <div class="col-span-2">
                <label class="label text-xs">Project</label>
                <select v-model="line.project_id" class="input text-sm">
                  <option value="">None</option>
                  <option value="1">Project A</option>
                  <option value="2">Project B</option>
                  <option value="3">Project C</option>
                </select>
              </div>
              <div class="col-span-1 flex justify-center items-center h-10">
                <span v-if="isLineItemAllocationComplete(line)" class="text-green-600 text-sm font-bold">✓</span>
                <span v-else-if="isLineItemAllocationPartial(line)" class="text-red-600 text-sm font-bold">⚠</span>
              </div>
              <div class="col-span-1 flex justify-end">
                <button
                  v-if="form.entries.length > 2"
                  type="button"
                  @click="removeEntry(index)"
                  class="text-red-600 hover:text-red-700"
                >
                  <TrashIcon class="h-5 w-5" />
                </button>
              </div>
            </div>
          </div>

          <!-- Balance Check -->
          <div class="mt-4 p-4 rounded-lg" :class="isBalanced ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'">
            <div class="flex justify-between items-center">
              <span class="font-medium" :class="isBalanced ? 'text-green-800' : 'text-red-800'">
                Total Debit: {{ formatCurrency(totalDebit) }}
              </span>
              <span class="font-medium" :class="isBalanced ? 'text-green-800' : 'text-red-800'">
                Total Credit: {{ formatCurrency(totalCredit) }}
              </span>
              <span class="font-bold" :class="isBalanced ? 'text-green-800' : 'text-red-800'">
                {{ isBalanced ? '✓ Balanced' : `Difference: ${formatCurrency(Math.abs(totalDebit - totalCredit))}` }}
              </span>
            </div>
          </div>
        </div>

        <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
          <button type="button" @click="$emit('close')" class="btn btn-secondary">
            Cancel
          </button>
          <button
            type="submit"
            class="btn btn-primary"
            :disabled="!isBalanced || form.entries.length < 2"
          >
            Save Journal Entry
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { transactionsApi } from '@/services/api'
import { PlusIcon, TrashIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  entry: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['close', 'saved'])

const form = ref({
  date: new Date().toISOString().split('T')[0],
  description: '',
  reference: '',
  entries: [
    { account_id: '', debit: 0, credit: 0, subsidiary_account_id: '', department_id: '', project_id: '' },
    { account_id: '', debit: 0, credit: 0, subsidiary_account_id: '', department_id: '', project_id: '' },
  ],
})

const totalDebit = computed(() => {
  return form.value.entries.reduce((sum, entry) => sum + (parseFloat(entry.debit) || 0), 0)
})

const totalCredit = computed(() => {
  return form.value.entries.reduce((sum, entry) => sum + (parseFloat(entry.credit) || 0), 0)
})

const isBalanced = computed(() => {
  return Math.abs(totalDebit.value - totalCredit.value) < 0.01 && totalDebit.value > 0
})

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(amount || 0)
}

watch(
  () => props.entry,
  (newEntry) => {
    if (newEntry) {
      form.value = {
        date: newEntry.date || form.value.date,
        description: newEntry.description || '',
        reference: newEntry.reference || '',
        entries: newEntry.entries || form.value.entries,
      }
    }
  },
  { immediate: true }
)

const addEntry = () => {
  form.value.entries.push({ account_id: '', debit: 0, credit: 0, subsidiary_account_id: '', department_id: '', project_id: '' })
}

const isLineItemAllocationComplete = (line) => {
  return line.subsidiary_account_id && line.department_id && line.project_id
}

const isLineItemAllocationPartial = (line) => {
  const hasAny = line.subsidiary_account_id || line.department_id || line.project_id
  const hasAll = line.subsidiary_account_id && line.department_id && line.project_id
  return hasAny && !hasAll
}

const validateAllocations = () => {
  for (let i = 0; i < form.value.entries.length; i++) {
    const line = form.value.entries[i]
    if (isLineItemAllocationPartial(line)) {
      alert(`Entry ${i + 1} has incomplete allocation: All three fields (Subsidiary, Department, Project) must be filled together or left empty.`)
      return false
    }
  }
  return true
}

const removeEntry = (index) => {
  if (form.value.entries.length > 2) {
    form.value.entries.splice(index, 1)
  }
}

const updateLine = (index) => {
  // Ensure only one of debit or credit has value
  const line = form.value.entries[index]
  if (line.debit > 0 && line.credit > 0) {
    // If both have values, clear the one that wasn't just updated
    if (line.debit > line.credit) {
      line.credit = 0
    } else {
      line.debit = 0
    }
  }
}

const saveEntry = async () => {
  if (!isBalanced.value) {
    alert('Journal entry must be balanced! Debits must equal Credits.')
    return
  }

  if (form.value.entries.length < 2) {
    alert('Journal entry must have at least 2 entries.')
    return
  }

  if (!validateAllocations()) {
    return
  }

  try {
    const entryData = {
      date: form.value.date,
      description: form.value.description,
      reference: form.value.reference,
      entries: form.value.entries.map((line) => ({
        account_id: line.account_id,
        debit: parseFloat(line.debit) || 0,
        credit: parseFloat(line.credit) || 0,
        subsidiary_account_id: line.subsidiary_account_id || null,
        department_id: line.department_id || null,
        project_id: line.project_id || null,
      })),
    }

    if (props.entry) {
      await transactionsApi.update(props.entry.id, entryData)
      alert('Journal entry updated successfully!')
    } else {
      await transactionsApi.createJournalEntry(entryData)
      alert('Journal entry created successfully!')
    }
    emit('saved')
    emit('close')
  } catch (error) {
    console.error('Error saving journal entry:', error)
    alert('Error saving journal entry: ' + (error.response?.data?.message || error.message))
  }
}
</script>
