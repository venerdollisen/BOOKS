<template>
  <div class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full">
      <div class="p-6 border-b border-gray-200">
        <h3 class="text-xl font-bold text-gray-900">Record Payment</h3>
        <p class="text-sm text-gray-500 mt-1">Invoice: {{ invoice?.invoice_number }}</p>
      </div>

      <form @submit.prevent="submitPayment" class="p-6 space-y-4">
        <!-- Error Message -->
        <div v-if="errorMessage" class="bg-red-50 border border-red-200 rounded-lg p-4">
          <p class="text-sm text-red-700">{{ errorMessage }}</p>
        </div>

        <!-- Invoice Summary -->
        <div class="bg-blue-50 p-4 rounded-lg space-y-2">
          <div class="flex justify-between text-sm">
            <span class="text-gray-600">Total Amount:</span>
            <span class="font-medium">{{ formatCurrency(invoice?.total_amount) }}</span>
          </div>
          <div class="flex justify-between text-sm">
            <span class="text-gray-600">Already Paid:</span>
            <span class="font-medium">{{ formatCurrency(invoice?.paid_amount) }}</span>
          </div>
          <div class="flex justify-between text-lg font-bold border-t border-blue-200 pt-2">
            <span>Remaining Balance:</span>
            <span class="text-orange-600">{{ formatCurrency(balance) }}</span>
          </div>
        </div>

        <!-- Payment Details -->
        <div class="space-y-4">
          <div>
            <label class="label">Payment Amount *</label>
            <input
              v-model.number="form.amount"
              type="number"
              step="0.01"
              min="0"
              :max="balance"
              class="input"
              required
              @input="validateAmount"
            />
            <p class="text-xs text-gray-500 mt-1">Maximum: {{ formatCurrency(balance) }}</p>
          </div>

          <div>
            <label class="label">Payment Date *</label>
            <input v-model="form.payment_date" type="date" class="input" required />
          </div>

          <div>
            <label class="label">Payment Method *</label>
            <select v-model="form.payment_method" class="input" required>
              <option value="">Select method</option>
              <option value="cash">Cash</option>
              <option value="check">Check</option>
              <option value="bank_transfer">Bank Transfer</option>
              <option value="credit_card">Credit Card</option>
              <option value="online">Online Payment</option>
            </select>
          </div>
        </div>

        <!-- Payment Summary -->
        <div class="bg-gray-50 p-4 rounded-lg space-y-2">
          <div class="flex justify-between">
            <span class="text-gray-600">Payment Amount:</span>
            <span class="font-medium">{{ formatCurrency(form.amount) }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600">New Balance:</span>
            <span class="font-medium text-green-600">{{ formatCurrency(balance - form.amount) }}</span>
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
            :disabled="!canSubmit || loading"
            class="btn btn-primary flex items-center gap-2"
            :class="{ 'opacity-50 cursor-not-allowed': !canSubmit || loading }"
          >
            <span v-if="loading" class="inline-block animate-spin">⏳</span>
            {{ loading ? 'Recording...' : 'Record Payment' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { receivablesApi } from '@/services/api'
import { useToast } from '@/composables/useToast'

const props = defineProps({
  invoice: Object,
  required: true,
})

const emit = defineEmits(['close', 'saved'])

const { success, error: showError } = useToast()

const form = ref({
  amount: 0,
  payment_date: new Date().toISOString().split('T')[0],
  payment_method: 'bank_transfer',
})

const errorMessage = ref('')
const loading = ref(false)

const balance = computed(() => {
  return (props.invoice?.total_amount || 0) - (props.invoice?.paid_amount || 0)
})

const canSubmit = computed(() => {
  return form.value.amount > 0 && form.value.amount <= balance.value && form.value.payment_method
})

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(amount || 0)
}

const validateAmount = () => {
  if (form.value.amount > balance.value) {
    form.value.amount = balance.value
  }
  if (form.value.amount < 0) {
    form.value.amount = 0
  }
}

const submitPayment = async () => {
  errorMessage.value = ''

  if (!form.value.amount || form.value.amount <= 0) {
    errorMessage.value = 'Payment amount must be greater than 0'
    return
  }

  if (form.value.amount > balance.value) {
    errorMessage.value = 'Payment amount cannot exceed remaining balance'
    return
  }

  loading.value = true

  try {
    await receivablesApi.recordPayment(props.invoice.id, {
      amount: form.value.amount,
      payment_date: form.value.payment_date,
      payment_method: form.value.payment_method,
    })

    success('Payment recorded successfully')
    emit('saved')
    emit('close')
  } catch (err) {
    console.error('Error recording payment:', err)
    errorMessage.value = err.response?.data?.error || 'Error recording payment'
    showError(errorMessage.value)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  form.value.amount = balance.value
})
</script>
