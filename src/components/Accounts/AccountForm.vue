<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
      <h2 class="text-2xl font-bold mb-4">{{ isEditing ? 'Edit Account' : 'New Account' }}</h2>

      <form @submit.prevent="handleSubmit" class="space-y-4">
        <!-- Code -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Account Code *</label>
          <input
            v-model="form.code"
            type="text"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="e.g., 1000"
            :disabled="isEditing"
          />
          <span v-if="errors.code" class="text-red-500 text-sm">{{ errors.code }}</span>
        </div>

        <!-- Name -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Account Name *</label>
          <input
            v-model="form.name"
            type="text"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="e.g., Cash"
          />
          <span v-if="errors.name" class="text-red-500 text-sm">{{ errors.name }}</span>
        </div>

        <!-- Account Type -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Account Type *</label>
          <select
            v-model="form.account_type"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Select type...</option>
            <option value="Asset">Asset</option>
            <option value="Liability">Liability</option>
            <option value="Equity">Equity</option>
            <option value="Income">Income</option>
            <option value="Expense">Expense</option>
          </select>
          <span v-if="errors.account_type" class="text-red-500 text-sm">{{ errors.account_type }}</span>
        </div>

        <!-- Parent Account -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Parent Account (Optional)</label>
          <select
            v-model="form.parent_id"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">None</option>
            <option
              v-for="account in availableParents"
              :key="account.id"
              :value="account.id"
            >
              {{ account.code }} - {{ account.name }}
            </option>
          </select>
        </div>

        <!-- Description -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
          <textarea
            v-model="form.description"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Optional description..."
            rows="3"
          />
        </div>

        <!-- Active Status -->
        <div class="flex items-center">
          <input
            v-model="form.is_active"
            type="checkbox"
            class="rounded border-gray-300"
          />
          <label class="ml-2 text-sm font-medium text-gray-700">Active</label>
        </div>

        <!-- Errors -->
        <div v-if="Object.keys(errors).length > 0 && !errors.code && !errors.name && !errors.account_type" class="p-3 bg-red-50 border border-red-200 rounded text-red-700 text-sm">
          Please check the form for errors
        </div>

        <!-- Actions -->
        <div class="flex gap-2 pt-4">
          <button
            type="button"
            @click="$emit('close')"
            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="loading"
            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
          >
            {{ loading ? 'Saving...' : isEditing ? 'Update' : 'Create' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useAccountStore } from '../../stores/accounts'

const props = defineProps({
  isEditing: Boolean,
  account: Object,
})

const emit = defineEmits(['close', 'saved'])

const accountStore = useAccountStore()
const loading = ref(false)
const errors = ref({})

const form = ref({
  code: '',
  name: '',
  account_type: '',
  parent_id: null,
  description: '',
  is_active: true,
})

const availableParents = computed(() => {
  return accountStore.accounts.filter(a => {
    if (props.isEditing && a.id === props.account?.id) return false
    return true
  })
})

watch(() => props.account, (newAccount) => {
  if (newAccount) {
    form.value = { ...newAccount }
  }
}, { immediate: true })

const handleSubmit = async () => {
  errors.value = {}
  loading.value = true

  try {
    if (props.isEditing) {
      await accountStore.updateAccount(props.account.id, form.value)
    } else {
      await accountStore.createAccount(form.value)
    }
    emit('saved')
  } catch (err) {
    if (err.response?.data?.errors) {
      errors.value = err.response.data.errors
    } else {
      errors.value.general = err.response?.data?.message || 'An error occurred'
    }
  } finally {
    loading.value = false
  }
}
</script>
