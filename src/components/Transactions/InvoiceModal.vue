<template>
  <div class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
      <div class="p-6 border-b border-gray-200">
        <h3 class="text-xl font-bold text-gray-900">
          {{ invoice ? 'Edit Invoice' : 'New Invoice' }}
        </h3>
      </div>

      <form @submit.prevent="saveInvoice" class="p-6 space-y-4">
        <!-- Error Message -->
        <div v-if="errorMessage" class="bg-red-50 border border-red-200 rounded-lg p-4">
          <p class="text-sm text-red-700">{{ errorMessage }}</p>
        </div>

        <!-- Invoice Details -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="label">Invoice Number *</label>
            <input v-model="form.invoice_number" type="text" class="input" required />
          </div>
          <div>
            <label class="label">Invoice Date *</label>
            <input v-model="form.invoice_date" type="date" class="input" required />
          </div>
          <div>
            <label class="label">Due Date *</label>
            <input v-model="form.due_date" type="date" class="input" required />
          </div>
        </div>

        <!-- Customer Details -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="label">Customer Name *</label>
            <input v-model="form.customer_name" type="text" class="input" required />
          </div>
          <div>
            <label class="label">Email</label>
            <input v-model="form.customer_email" type="email" class="input" />
          </div>
          <div>
            <label class="label">Phone</label>
            <input v-model="form.customer_phone" type="tel" class="input" />
          </div>
        </div>

        <!-- Notes -->
        <div>
          <label class="label">Notes</label>
          <textarea v-model="form.notes" class="input min-h-20" placeholder="Additional notes..."></textarea>
        </div>

        <!-- Line Items -->
        <div class="border-t border-gray-200 pt-4">
          <h4 class="text-lg font-semibold text-gray-900 mb-4">Line Items</h4>
          <div class="space-y-3 mb-4">
            <div
              v-for="(item, index) in form.items"
              :key="index"
              class="grid grid-cols-1 md:grid-cols-5 gap-2 items-end border border-gray-200 p-3 rounded"
            >
              <div>
                <label class="label text-xs">Description *</label>
                <input v-model="item.description" type="text" class="input text-sm" required />
              </div>
              <div>
                <label class="label text-xs">Quantity *</label>
                <input v-model.number="item.quantity" type="number" step="0.01" class="input text-sm" required />
              </div>
              <div>
                <label class="label text-xs">Unit Price *</label>
                <input v-model.number="item.unit_price" type="number" step="0.01" class="input text-sm" required />
              </div>
              <div>
                <label class="label text-xs">Account *</label>
                <select v-model="item.account_id" class="input text-sm" required>
                  <option value="">Select Account</option>
                  <option v-for="acc in accounts" :key="acc.id" :value="acc.id">
                    {{ acc.name }} ({{ acc.code }})
                  </option>
                </select>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-900">
                  {{ formatCurrency(item.quantity * item.unit_price) }}
                </p>
                <button
                  type="button"
                  @click="removeItem(index)"
                  class="text-red-600 hover:text-red-700 text-sm"
                >
                  Remove
                </button>
              </div>
            </div>
          </div>

          <button
            type="button"
            @click="addItem"
            class="btn btn-secondary text-sm mb-4"
          >
            <PlusIcon class="h-4 w-4 inline mr-1" />
            Add Item
          </button>

          <!-- Totals -->
          <div class="bg-gray-50 p-4 rounded-lg space-y-2">
            <div class="flex justify-between text-sm">
              <span class="text-gray-600">Subtotal:</span>
              <span class="font-medium">{{ formatCurrency(subtotal) }}</span>
            </div>
            <div class="flex justify-between text-lg font-bold border-t border-gray-200 pt-2">
              <span>Total:</span>
              <span>{{ formatCurrency(subtotal) }}</span>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-2 pt-4 border-t">
          <button 
            type="button" 
            @click="$emit('close')"
            :disabled="loading"
            class="btn btn-secondary"
          >
            Cancel
          </button>
          <button 
            type="submit" 
            :disabled="loading || !form.invoice_number || !form.customer_name || form.items.length === 0"
            class="btn btn-primary flex items-center gap-2"
            :class="{ 'opacity-50 cursor-not-allowed': loading }"
          >
            <span v-if="loading" class="inline-block animate-spin">⏳</span>
            {{ loading ? 'Saving...' : (invoice ? 'Update Invoice' : 'Create Invoice') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { receivablesApi } from '@/services/api'
import { useAccountStore } from '@/stores/accounts'
import { useToast } from '@/composables/useToast'
import { PlusIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  invoice: Object,
})

const emit = defineEmits(['close', 'saved'])

const accountStore = useAccountStore()
const { success, error: showError } = useToast()

const accounts = ref([])
const errorMessage = ref('')
const loading = ref(false)

const form = ref({
  invoice_number: '',
  customer_name: '',
  customer_email: '',
  customer_phone: '',
  invoice_date: new Date().toISOString().split('T')[0],
  due_date: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
  notes: '',
  items: [
    { description: '', quantity: 1, unit_price: 0, account_id: '' }
  ],
})

const subtotal = computed(() => {
  return form.value.items.reduce((sum, item) => sum + (item.quantity * item.unit_price || 0), 0)
})

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(amount || 0)
}

const addItem = () => {
  form.value.items.push({ description: '', quantity: 1, unit_price: 0, account_id: '' })
}

const removeItem = (index) => {
  form.value.items.splice(index, 1)
}

const saveInvoice = async () => {
  errorMessage.value = ''

  if (form.value.items.length === 0) {
    errorMessage.value = 'At least one line item is required'
    return
  }

  if (!form.value.invoice_number || !form.value.customer_name) {
    errorMessage.value = 'Invoice number and customer name are required'
    return
  }

  loading.value = true

  try {
    const payload = {
      invoice_number: form.value.invoice_number,
      customer_name: form.value.customer_name,
      customer_email: form.value.customer_email,
      customer_phone: form.value.customer_phone,
      invoice_date: form.value.invoice_date,
      due_date: form.value.due_date,
      notes: form.value.notes,
      items: form.value.items.map(item => ({
        description: item.description,
        quantity: item.quantity,
        unit_price: item.unit_price,
        account_id: item.account_id,
      })),
    }

    if (props.invoice) {
      await receivablesApi.updateInvoice(props.invoice.id, payload)
      success('Invoice updated successfully')
    } else {
      await receivablesApi.createInvoice(payload)
      success('Invoice created successfully')
    }

    emit('saved')
    emit('close')
  } catch (err) {
    console.error('Error saving invoice:', err)
    errorMessage.value = err.response?.data?.error || 'Error saving invoice'
    showError(errorMessage.value)
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  if (accountStore.accounts.length === 0) {
    await accountStore.fetchAccounts(1)
  }
  accounts.value = accountStore.accounts.filter(acc => acc.type === 'revenue')

  if (props.invoice) {
    form.value = {
      invoice_number: props.invoice.invoice_number,
      customer_name: props.invoice.customer_name,
      customer_email: props.invoice.customer_email,
      customer_phone: props.invoice.customer_phone,
      invoice_date: props.invoice.invoice_date,
      due_date: props.invoice.due_date,
      notes: props.invoice.notes,
      items: props.invoice.items?.map(item => ({
        description: item.description,
        quantity: item.quantity,
        unit_price: item.unit_price,
        account_id: item.account_id,
      })) || [{ description: '', quantity: 1, unit_price: 0, account_id: '' }],
    }
  }
})
</script>
