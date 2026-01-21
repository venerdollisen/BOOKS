<template>
  <div class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
      <div class="p-6 border-b border-gray-200">
        <h3 class="text-xl font-bold text-gray-900">
          {{ customer ? 'Edit Customer' : 'New Customer' }}
        </h3>
      </div>

      <form @submit.prevent="saveCustomer" class="p-6 space-y-4">
        <!-- Error Message -->
        <div v-if="errorMessage" class="bg-red-50 border border-red-200 rounded-lg p-4">
          <p class="text-sm text-red-700">{{ errorMessage }}</p>
        </div>

        <!-- Basic Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="label">Name *</label>
            <input v-model="form.name" type="text" class="input" required />
          </div>
          <div>
            <label class="label">Email</label>
            <input v-model="form.email" type="email" class="input" />
          </div>
          <div>
            <label class="label">Phone</label>
            <input v-model="form.phone" type="tel" class="input" />
          </div>
          <div>
            <label class="label">Tax ID</label>
            <input v-model="form.tax_id" type="text" class="input" />
          </div>
        </div>

        <!-- Address -->
        <div>
          <label class="label">Address</label>
          <input v-model="form.address" type="text" class="input" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="label">City</label>
            <input v-model="form.city" type="text" class="input" />
          </div>
          <div>
            <label class="label">State</label>
            <input v-model="form.state" type="text" class="input" />
          </div>
          <div>
            <label class="label">Postal Code</label>
            <input v-model="form.postal_code" type="text" class="input" />
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="label">Country</label>
            <input v-model="form.country" type="text" class="input" />
          </div>
          <div>
            <label class="label">Payment Terms</label>
            <input v-model="form.payment_terms" type="text" class="input" placeholder="e.g., Net 30" />
          </div>
        </div>

        <!-- Credit Limit -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="label">Credit Limit</label>
            <input v-model.number="form.credit_limit" type="number" step="0.01" class="input" />
          </div>
          <div>
            <label class="label">Status</label>
            <select v-model="form.is_active" class="input">
              <option :value="true">Active</option>
              <option :value="false">Inactive</option>
            </select>
          </div>
        </div>

        <!-- Notes -->
        <div>
          <label class="label">Notes</label>
          <textarea v-model="form.notes" class="input min-h-20" placeholder="Additional notes..."></textarea>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-2 pt-4 border-t">
          <button type="button" @click="$emit('close')" class="btn btn-secondary">
            Cancel
          </button>
          <button type="submit" :disabled="loading" class="btn btn-primary">
            {{ loading ? 'Saving...' : (customer ? 'Update' : 'Create') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { customersApi } from '@/services/api'
import { useToast } from '@/composables/useToast'

const props = defineProps({
  customer: Object,
})

const emit = defineEmits(['close', 'saved'])

const { success, error: showError } = useToast()

const loading = ref(false)
const errorMessage = ref('')

const form = ref({
  name: '',
  email: '',
  phone: '',
  address: '',
  city: '',
  state: '',
  postal_code: '',
  country: '',
  tax_id: '',
  credit_limit: null,
  payment_terms: 'Net 30',
  is_active: true,
  notes: '',
})

const saveCustomer = async () => {
  errorMessage.value = ''

  if (!form.value.name) {
    errorMessage.value = 'Name is required'
    return
  }

  loading.value = true

  try {
    if (props.customer) {
      await customersApi.updateCustomer(props.customer.id, form.value)
      success('Customer updated successfully')
    } else {
      await customersApi.createCustomer(form.value)
      success('Customer created successfully')
    }

    emit('saved')
    emit('close')
  } catch (err) {
    console.error('Error saving customer:', err)
    errorMessage.value = err.response?.data?.error || 'Error saving customer'
    showError(errorMessage.value)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  if (props.customer) {
    form.value = { ...props.customer }
  }
})
</script>
