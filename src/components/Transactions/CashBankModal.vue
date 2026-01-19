<template>
  <div class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
      <div class="p-6 border-b border-gray-200">
        <h3 class="text-xl font-bold text-gray-900">
          {{ transaction ? 'Edit Transaction' : 'New Cash / Bank Transaction' }}
        </h3>
        <p class="text-sm text-gray-500 mt-1">Double-entry bookkeeping: Debits must equal Credits</p>
      </div>
      <form @submit.prevent="saveTransaction" class="p-6 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="label">Date *</label>
            <input v-model="form.date" type="date" required class="input" />
          </div>
          <div>
            <label class="label">Type *</label>
            <select v-model="form.type" required class="input" @change="updateFormBasedOnType">
              <option value="">Select Type</option>
              <option value="receipt">Receipt</option>
              <option value="payment">Payment</option>
              <option value="transfer">Transfer</option>
            </select>
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
            <h4 class="text-lg font-semibold text-gray-900">Transaction Entries</h4>
            <button type="button" @click="addEntry" class="btn btn-secondary text-sm">
              <PlusIcon class="h-4 w-4 inline mr-1" />
              Add Entry
            </button>
          </div>

          <div class="space-y-3">
            <div
              v-for="(entry, index) in form.entries"
              :key="index"
              class="grid grid-cols-12 gap-3 items-end p-3 bg-gray-50 rounded-lg"
            >
              <div class="col-span-4">
                <label class="label text-xs">Account *</label>
                <select v-model="entry.account_id" required class="input text-sm">
                  <option value="">Select Account</option>
                  <!-- Accounts would be loaded from API -->
                  <option value="1">Cash</option>
                  <option value="2">Bank Account</option>
                  <option value="3">Accounts Receivable</option>
                  <option value="4">Accounts Payable</option>
                  <option value="5">Revenue</option>
                  <option value="6">Expenses</option>
                </select>
              </div>
              <div class="col-span-3">
                <label class="label text-xs">Debit</label>
                <input
                  v-model.number="entry.debit"
                  type="number"
                  step="0.01"
                  min="0"
                  class="input text-sm"
                  placeholder="0.00"
                  @input="updateEntry(index)"
                />
              </div>
              <div class="col-span-3">
                <label class="label text-xs">Credit</label>
                <input
                  v-model.number="entry.credit"
                  type="number"
                  step="0.01"
                  min="0"
                  class="input text-sm"
                  placeholder="0.00"
                  @input="updateEntry(index)"
                />
              </div>
              <div class="col-span-2 flex justify-end">
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

        <div>
          <label class="label">Attach Document</label>
          <input type="file" @change="handleFileUpload" class="input" />
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
            Save Transaction
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
  transaction: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['close', 'saved'])

const form = ref({
  date: new Date().toISOString().split('T')[0],
  type: '',
  description: '',
  reference: '',
  entries: [
    { account_id: '', debit: 0, credit: 0 },
    { account_id: '', debit: 0, credit: 0 },
  ],
  document: null,
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
  () => props.transaction,
  (newTransaction) => {
    if (newTransaction) {
      form.value = {
        date: newTransaction.date || form.value.date,
        type: newTransaction.type || '',
        description: newTransaction.description || '',
        reference: newTransaction.reference || '',
        entries: newTransaction.entries || form.value.entries,
        document: null,
      }
    }
  },
  { immediate: true }
)

const updateFormBasedOnType = () => {
  // Auto-populate entries based on transaction type
  if (form.value.type === 'receipt') {
    // Receipt: Debit Cash/Bank, Credit Revenue/AR
    if (form.value.entries.length === 2) {
      form.value.entries[0].credit = 0
      form.value.entries[1].debit = 0
    }
  } else if (form.value.type === 'payment') {
    // Payment: Debit Expense/AP, Credit Cash/Bank
    if (form.value.entries.length === 2) {
      form.value.entries[0].credit = 0
      form.value.entries[1].debit = 0
    }
  } else if (form.value.type === 'transfer') {
    // Transfer: Debit To Account, Credit From Account
    if (form.value.entries.length === 2) {
      form.value.entries[0].credit = 0
      form.value.entries[1].debit = 0
    }
  }
}

const addEntry = () => {
  form.value.entries.push({ account_id: '', debit: 0, credit: 0 })
}

const removeEntry = (index) => {
  if (form.value.entries.length > 2) {
    form.value.entries.splice(index, 1)
  }
}

const updateEntry = (index) => {
  // Ensure only one of debit or credit has value
  const entry = form.value.entries[index]
  if (entry.debit > 0 && entry.credit > 0) {
    // If both have values, clear the one that wasn't just updated
    // This is a simple approach - you might want more sophisticated logic
    if (entry.debit > entry.credit) {
      entry.credit = 0
    } else {
      entry.debit = 0
    }
  }
}

const handleFileUpload = (event) => {
  form.value.document = event.target.files[0]
}

const saveTransaction = async () => {
  if (!isBalanced.value) {
    alert('Transaction must be balanced! Debits must equal Credits.')
    return
  }

  if (form.value.entries.length < 2) {
    alert('Transaction must have at least 2 entries.')
    return
  }

  try {
    const transactionData = {
      date: form.value.date,
      type: form.value.type,
      description: form.value.description,
      reference: form.value.reference,
      entries: form.value.entries.map((entry) => ({
        account_id: entry.account_id,
        debit: parseFloat(entry.debit) || 0,
        credit: parseFloat(entry.credit) || 0,
      })),
    }

    if (props.transaction) {
      await transactionsApi.update(props.transaction.id, transactionData)
    } else {
      if (form.value.type === 'transfer') {
        await transactionsApi.createBankTransaction(transactionData)
      } else {
        await transactionsApi.createCashTransaction(transactionData)
      }
    }
    emit('saved')
  } catch (error) {
    console.error('Error saving transaction:', error)
    alert('Error saving transaction. Please try again.')
  }
}
</script>
