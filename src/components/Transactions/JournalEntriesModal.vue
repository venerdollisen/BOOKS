<template>
  <div class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-5xl w-full max-h-[90vh] overflow-y-auto">
      <div class="p-6 border-b border-gray-200">
        <h3 class="text-xl font-bold text-gray-900">
          {{ transaction ? 'Edit Journal Entry' : 'New Journal Entry' }}
        </h3>
        <p class="text-sm text-gray-500 mt-1">Double-entry bookkeeping: Debits must equal Credits</p>
      </div>
      <form @submit.prevent="saveTransaction" class="p-6 space-y-4">
        <!-- Error Message -->
        <div v-if="errorMessage" class="bg-red-50 border border-red-200 rounded-lg p-4">
          <p class="text-sm text-red-700">{{ errorMessage }}</p>
        </div>

        <!-- Top Section -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="label">Reference Number</label>
            <input v-model="form.reference" type="text" class="input" />
          </div>
          <div>
            <label class="label">Date *</label>
            <input v-model="form.date" type="date" required class="input" />
          </div>
          <div>
            <label class="label">Check Number</label>
            <input v-model="form.checkNumber" type="text" class="input" placeholder="e.g., CHK-001" />
          </div>
        </div>

        <!-- Second Row: Check Date, Bank, Billing Number -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="label">Check Date</label>
            <input v-model="form.checkDate" type="date" class="input" />
          </div>
          <div>
            <label class="label">Bank</label>
            <input v-model="form.bank" type="text" class="input" placeholder="Bank name" />
          </div>
          <div>
            <label class="label">Billing Number</label>
            <input v-model="form.billingNumber" type="text" class="input" placeholder="Invoice/Bill number" />
          </div>
        </div>

        <!-- Payee/Description Textarea -->
        <div>
          <label class="label">Payee / Description *</label>
          <textarea v-model="form.payeeDescription" required class="input min-h-20" placeholder="Enter payee name and transaction details..."></textarea>
        </div>

        <!-- File Uploads -->
        <div class="grid grid-cols-1 md:grid-cols-1 gap-4">
          <div>
            <label class="label">Upload File</label>
            <div class="flex items-center gap-2">
              <input 
                ref="fileInput" 
                type="file" 
                class="input flex-1" 
                @change="handleFileUpload"
              />
              <button
                v-if="form.attachedFile"
                type="button"
                @click="clearFile"
                class="text-red-600 hover:text-red-700"
              >
                ✕
              </button>
            </div>
            <div v-if="form.attachedFileName" class="mt-2">
              <p class="text-sm text-gray-600">📎 {{ form.attachedFileName }}</p>
            </div>
          </div>
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

          <div class="overflow-x-auto border border-gray-200 rounded-lg">
            <table class="w-full text-sm">
              <thead class="bg-gray-200">
                <tr>
                  <th class="px-3 py-2 text-left font-semibold">Account</th>
                  <th class="px-3 py-2 text-left font-semibold">Subsidiary</th>
                  <th class="px-3 py-2 text-left font-semibold">Department</th>
                  <th class="px-3 py-2 text-left font-semibold">Project</th>
                  <th class="px-3 py-2 text-center font-semibold">Debit</th>
                  <th class="px-3 py-2 text-center font-semibold">Credit</th>
                  <th class="px-3 py-2 text-center font-semibold">Status</th>
                  <th class="px-3 py-2 text-center font-semibold">Action</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(entry, index) in form.entries"
                  :key="index"
                  class="border-t border-gray-200 hover:bg-gray-100"
                >
                  <td class="px-3 py-2">
                    <select v-model="entry.account_id" required class="input text-sm w-full">
                      <option value="">Select Account</option>
                      <option v-for="account in accounts" :key="account.id" :value="account.id">
                        {{ account.code }} - {{ account.name }}
                      </option>
                    </select>
                  </td>
                  <td class="px-3 py-2">
                    <select v-model="entry.subsidiary_account_id" class="input text-sm w-full">
                      <option value="">None</option>
                      <option v-for="subsidiary in subsidiaryAccounts" :key="subsidiary.id" :value="subsidiary.id">
                        {{ subsidiary.name }}
                      </option>
                    </select>
                  </td>
                  <td class="px-3 py-2">
                    <select v-model="entry.department_id" class="input text-sm w-full">
                      <option value="">None</option>
                      <option v-for="department in departments" :key="department.id" :value="department.id">
                        {{ department.name }}
                      </option>
                    </select>
                  </td>
                  <td class="px-3 py-2">
                    <select v-model="entry.project_id" class="input text-sm w-full">
                      <option value="">None</option>
                      <option v-for="project in projects" :key="project.id" :value="project.id">
                        {{ project.name }}
                      </option>
                    </select>
                  </td>
                  <td class="px-3 py-2">
                    <input
                      v-model.number="entry.debit"
                      type="number"
                      step="0.01"
                      min="0"
                      class="input text-sm w-full text-right"
                      placeholder="0.00"
                      @input="updateEntry(index)"
                    />
                  </td>
                  <td class="px-3 py-2">
                    <input
                      v-model.number="entry.credit"
                      type="number"
                      step="0.01"
                      min="0"
                      class="input text-sm w-full text-right"
                      placeholder="0.00"
                      @input="updateEntry(index)"
                    />
                  </td>
                  <td class="px-3 py-2 text-center">
                    <span v-if="isLineItemAllocationComplete(entry)" class="text-green-600 font-bold">✓</span>
                    <span v-else-if="isLineItemAllocationPartial(entry)" class="text-red-600 font-bold">⚠</span>
                  </td>
                  <td class="px-3 py-2 text-center">
                    <button
                      v-if="form.entries.length > 2"
                      type="button"
                      @click="removeEntry(index)"
                      class="text-red-600 hover:text-red-700"
                    >
                      <TrashIcon class="h-5 w-5" />
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
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

        <!-- Form Actions -->
        <!-- Form Actions -->
        <div class="flex gap-3 justify-end border-t pt-6">
          <button
            type="button"
            @click="$emit('close')"
            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition"
            :disabled="loading"
          >
            Cancel
          </button>
          <button
            v-if="transaction && !loading"
            type="button"
            @click="printEntry"
            class="px-4 py-2 border border-purple-300 text-purple-600 rounded-md hover:bg-purple-50 transition"
          >
            🖨 Print
          </button>
          <button
            type="submit"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition disabled:opacity-50"
            :disabled="loading"
          >
            <span v-if="loading" class="inline-block mr-2">⏳</span>
            {{ loading ? 'Saving...' : transaction ? 'Update' : 'Create' }}
            Entry
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { useTransactionStore } from '@/stores/transactions'
import { useAccountStore } from '@/stores/accounts'
import { useDepartmentStore } from '@/stores/departments'
import { useProjectStore } from '@/stores/projects'
import { useSubsidiaryAccountStore } from '@/stores/subsidiaryAccounts'
import { useToast } from '@/composables/useToast'
import { PlusIcon, TrashIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  transaction: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['saved', 'close'])

const transactionStore = useTransactionStore()
const accountStore = useAccountStore()
const departmentStore = useDepartmentStore()
const projectStore = useProjectStore()
const subsidiaryAccountStore = useSubsidiaryAccountStore()
const { success, error: showError } = useToast()

const loading = ref(false)
const accounts = ref([])
const departments = ref([])
const projects = ref([])
const subsidiaryAccounts = ref([])
const errorMessage = ref('')
const fileInput = ref(null)

const form = reactive({
  date: new Date().toISOString().split('T')[0],
  reference: '',
  checkNumber: '',
  checkDate: '',
  bank: '',
  billingNumber: '',
  payeeDescription: '',
  attachedFile: null,
  attachedFileName: '',
  entries: [
    { account_id: '', debit: 0, credit: 0, subsidiary_account_id: '', department_id: '', project_id: '' },
    { account_id: '', debit: 0, credit: 0, subsidiary_account_id: '', department_id: '', project_id: '' },
  ],
})

const totalDebit = computed(() => {
  return form.entries.reduce((sum, entry) => sum + (parseFloat(entry.debit) || 0), 0)
})

const totalCredit = computed(() => {
  return form.entries.reduce((sum, entry) => sum + (parseFloat(entry.credit) || 0), 0)
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

onMounted(() => {
  fetchAllocations()
})

watch(
  () => props.transaction,
  (newTransaction) => {
    if (newTransaction) {
      form.date = newTransaction.transaction_date ? newTransaction.transaction_date.split('T')[0] : form.date
      form.reference = newTransaction.reference || ''
      form.checkNumber = newTransaction.check_number || ''
      form.checkDate = newTransaction.check_date ? newTransaction.check_date.split('T')[0] : ''
      form.bank = newTransaction.bank || ''
      form.billingNumber = newTransaction.billing_number || ''
      form.payeeDescription = newTransaction.payee_description || ''
      form.entries = newTransaction.items?.map((item) => ({
        account_id: item.account_id,
        debit: item.type === 'debit' ? parseFloat(item.amount) : 0,
        credit: item.type === 'credit' ? parseFloat(item.amount) : 0,
        subsidiary_account_id: item.subsidiary_account_id ? item.subsidiary_account_id.toString() : '',
        department_id: item.department_id ? item.department_id.toString() : '',
        project_id: item.project_id ? item.project_id.toString() : ''
      })) || form.entries
    } else {
      form.date = new Date().toISOString().split('T')[0]
      form.reference = ''
      form.checkNumber = ''
      form.checkDate = ''
      form.bank = ''
      form.billingNumber = ''
      form.payeeDescription = ''
      form.attachedFile = null
      form.attachedFileName = ''
      form.entries = [
        { account_id: '', debit: 0, credit: 0, subsidiary_account_id: '', department_id: '', project_id: '' },
        { account_id: '', debit: 0, credit: 0, subsidiary_account_id: '', department_id: '', project_id: '' },
      ]
    }
    errorMessage.value = ''
  },
  { immediate: true }
)

async function fetchAllocations() {
  try {
    if (accountStore.accounts.length === 0) {
      await accountStore.fetchAccounts(1)
    }
    accounts.value = accountStore.accounts

    if (departmentStore.departments.length === 0) {
      await departmentStore.fetchDepartments()
    }
    departments.value = departmentStore.departments

    if (projectStore.projects.length === 0) {
      await projectStore.fetchProjects()
    }
    projects.value = projectStore.projects

    if (subsidiaryAccountStore.subsidiaryAccounts.length === 0) {
      await subsidiaryAccountStore.fetchSubsidiaryAccounts()
    }
    subsidiaryAccounts.value = subsidiaryAccountStore.subsidiaryAccounts
  } catch (err) {
    console.error('Error fetching allocations:', err)
  }
}

const addEntry = () => {
  form.entries.push({ account_id: '', debit: 0, credit: 0, subsidiary_account_id: '', department_id: '', project_id: '' })
}

const removeEntry = (index) => {
  if (form.entries.length > 2) {
    form.entries.splice(index, 1)
  }
}

const updateEntry = (index) => {
  const entry = form.entries[index]
  if (entry.debit > 0 && entry.credit > 0) {
    if (entry.debit > entry.credit) {
      entry.credit = 0
    } else {
      entry.debit = 0
    }
  }
}

const isLineItemAllocationPartial = (entry) => {
  const { subsidiary_account_id, department_id, project_id } = entry
  const hasValue = [subsidiary_account_id, department_id, project_id].filter((v) => v).length
  return hasValue > 0 && hasValue < 3
}

const isLineItemAllocationComplete = (entry) => {
  const { subsidiary_account_id, department_id, project_id } = entry
  return subsidiary_account_id && department_id && project_id
}

const saveTransaction = async () => {
  errorMessage.value = ''

  if (!isBalanced.value) {
    errorMessage.value = 'Transaction must be balanced! Debits must equal Credits.'
    return
  }

  if (form.entries.length < 2) {
    errorMessage.value = 'Entry must have at least 2 lines.'
    return
  }

  if (!form.date || !form.payeeDescription) {
    errorMessage.value = 'Date and Payee/Description are required.'
    return
  }

  loading.value = true

  try {
    // Create FormData to handle submission
    const formData = new FormData()
    formData.append('reference', form.reference || generateUniqueReference())
    formData.append('payee_description', form.payeeDescription)
    formData.append('transaction_date', form.date)
    formData.append('type', 'journal')
    formData.append('amount', totalDebit.value)
    formData.append('status', 'draft')
    formData.append('check_number', form.checkNumber)
    formData.append('check_date', form.checkDate)
    formData.append('bank', form.bank)
    formData.append('billing_number', form.billingNumber)

    // Add file if present
    if (form.attachedFile) {
      formData.append('attached_file', form.attachedFile)
    }

    // Add items as JSON
    formData.append('items', JSON.stringify(form.entries.map((entry) => ({
      account_id: entry.account_id,
      type: entry.debit > 0 ? 'debit' : 'credit',
      amount: entry.debit > 0 ? parseFloat(entry.debit) : parseFloat(entry.credit),
      subsidiary_account_id: entry.subsidiary_account_id || null,
      department_id: entry.department_id || null,
      project_id: entry.project_id || null
    }))))

    if (props.transaction) {
      await transactionStore.updateTransaction(props.transaction.id, formData)
      success('Entry updated successfully!')
    } else {
      await transactionStore.createTransaction(formData)
      success('Entry created successfully!')
    }

    emit('saved')
    emit('close')
  } catch (error) {
    console.error('Error saving entry:', error)
    
    let errorMsg = 'Error saving entry'
    
    // Handle Laravel validation errors with field-specific messages
    if (error.response?.data?.errors) {
      const errors = error.response.data.errors
      const errorMessages = []
      for (const [field, messages] of Object.entries(errors)) {
        if (Array.isArray(messages)) {
          errorMessages.push(...messages)
        } else {
          errorMessages.push(messages)
        }
      }
      errorMsg = errorMessages.length > 0 ? errorMessages[0] : error.response.data.message
    } else if (error.response?.data?.message) {
      errorMsg = error.response.data.message
    } else if (error.message) {
      errorMsg = error.message
    }
    
    errorMessage.value = errorMsg
    showError(errorMsg)
  } finally {
    loading.value = false
  }
}

const generateUniqueReference = () => {
  const timestamp = Date.now()
  const random = Math.random().toString(36).substring(2, 8).toUpperCase()
  return `JNL-${random}-${timestamp}`
}

const handleFileUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    form.attachedFile = file
    form.attachedFileName = file.name
  }
}

const clearFile = () => {
  form.attachedFile = null
  form.attachedFileName = ''
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const printEntry = () => {
  if (!props.transaction) return
  
  const printWindow = window.open('', '', 'height=600,width=800')
  const debits = form.entries.reduce((sum, entry) => sum + (parseFloat(entry.debit) || 0), 0)
  const credits = form.entries.reduce((sum, entry) => sum + (parseFloat(entry.credit) || 0), 0)
  
  const content = `
    <!DOCTYPE html>
    <html>
    <head>
      <title>Journal Entry - ${props.transaction.reference}</title>
      <style>
        body {
          font-family: Arial, sans-serif;
          margin: 20px;
          color: #333;
        }
        h1 {
          color: #06275c;
          border-bottom: 2px solid #06275c;
          padding-bottom: 10px;
        }
        .entry-header {
          display: grid;
          grid-template-columns: 1fr 1fr;
          gap: 20px;
          margin-bottom: 30px;
        }
        .entry-field {
          margin-bottom: 10px;
        }
        .entry-field label {
          font-weight: bold;
          color: #06275c;
        }
        table {
          width: 100%;
          border-collapse: collapse;
          margin-top: 20px;
        }
        th {
          background-color: #06275c;
          color: white;
          padding: 10px;
          text-align: left;
          border: 1px solid #ddd;
        }
        td {
          padding: 10px;
          border: 1px solid #ddd;
        }
        tr:nth-child(even) {
          background-color: #f9f9f9;
        }
        .text-right {
          text-align: right;
        }
        .balance-section {
          margin-top: 20px;
          padding: 15px;
          background-color: #f0f9ff;
          border: 2px solid #06275c;
          border-radius: 5px;
        }
        .balance-row {
          font-weight: bold;
          font-size: 16px;
          color: #06275c;
          margin: 5px 0;
        }
        .balanced {
          color: green;
        }
        @media print {
          body {
            margin: 0;
          }
        }
      </style>
    </head>
    <body>
      <h1>Journal Entry</h1>
      
      <div class="entry-header">
        <div>
          <div class="entry-field">
            <label>Reference:</label> ${props.transaction.reference}
          </div>
          <div class="entry-field">
            <label>Date:</label> ${new Date(props.transaction.transaction_date).toLocaleDateString('en-US')}
          </div>
          <div class="entry-field">
            <label>Status:</label> ${props.transaction.status}
          </div>
        </div>
        <div>
          ${form.payeeDescription ? `
          <div class="entry-field">
            <label>Payee/Description:</label> ${form.payeeDescription}
          </div>
          ` : ''}
          ${form.checkNumber ? `
          <div class="entry-field">
            <label>Check Number:</label> ${form.checkNumber}
          </div>
          ` : ''}
          ${form.bank ? `
          <div class="entry-field">
            <label>Bank:</label> ${form.bank}
          </div>
          ` : ''}
        </div>
      </div>

      <h2>Transaction Entries</h2>
      <table>
        <thead>
          <tr>
            <th>Account</th>
            <th>Subsidiary</th>
            <th>Department</th>
            <th>Project</th>
            <th class="text-right">Debit</th>
            <th class="text-right">Credit</th>
          </tr>
        </thead>
        <tbody>
          ${form.entries.map(entry => `
            <tr>
              <td>${accounts.value.find(a => a.id == entry.account_id)?.name || 'N/A'}</td>
              <td>${subsidiaryAccounts.value.find(s => s.id == entry.subsidiary_account_id)?.name || '-'}</td>
              <td>${departments.value.find(d => d.id == entry.department_id)?.name || '-'}</td>
              <td>${projects.value.find(p => p.id == entry.project_id)?.name || '-'}</td>
              <td class="text-right">${entry.debit > 0 ? '$' + parseFloat(entry.debit).toFixed(2) : '-'}</td>
              <td class="text-right">${entry.credit > 0 ? '$' + parseFloat(entry.credit).toFixed(2) : '-'}</td>
            </tr>
          `).join('')}
        </tbody>
      </table>

      <div class="balance-section">
        <div class="balance-row">Total Debit: $${debits.toFixed(2)}</div>
        <div class="balance-row">Total Credit: $${credits.toFixed(2)}</div>
        <div class="balance-row ${Math.abs(debits - credits) < 0.01 ? 'balanced' : ''}">
          ${Math.abs(debits - credits) < 0.01 ? '✓ Balanced' : '✗ Not Balanced'}
        </div>
      </div>

      <script>
        window.print();
        window.onafterprint = () => window.close();
      <\/script>
    </body>
    </html>
  `
  printWindow.document.write(content)
  printWindow.document.close()
}
</script>
