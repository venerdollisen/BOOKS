<template>
  <div class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
      <div class="p-6 border-b border-gray-200">
        <h3 class="text-xl font-bold text-gray-900">
          {{ account ? 'Edit Account' : 'Add New Account' }}
        </h3>
      </div>
      <form @submit.prevent="saveAccount" class="p-6 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="label">Account Code *</label>
            <input v-model="form.code" type="text" required class="input" />
          </div>
          <div>
            <label class="label">Account Name *</label>
            <input v-model="form.name" type="text" required class="input" />
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="label">Account Type *</label>
            <select v-model="form.type" required class="input" @change="loadParentAccounts">
              <option value="">Select Type</option>
              <option value="Assets">Assets</option>
              <option value="Liabilities">Liabilities</option>
              <option value="Equity">Equity</option>
              <option value="Income">Income</option>
              <option value="Expenses">Expenses</option>
            </select>
          </div>
          <div>
            <label class="label">Parent Account</label>
            <select v-model="form.parent_id" class="input">
              <option value="">None (Top-level Account)</option>
              <option 
                v-for="parentAccount in availableParentAccounts" 
                :key="parentAccount.id"
                :value="parentAccount.id"
                :disabled="parentAccount.id === account?.id"
              >
                {{ parentAccount.code }} - {{ parentAccount.name }}
              </option>
            </select>
          </div>
        </div>

        <div>
          <label class="label">Description</label>
          <textarea v-model="form.description" rows="3" class="input"></textarea>
        </div>

        <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
          <button type="button" @click="$emit('close')" class="btn btn-secondary">
            Cancel
          </button>
          <button type="submit" class="btn btn-primary">Save Account</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { accountsApi } from '@/services/api'

const props = defineProps({
  account: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['close', 'saved'])

const form = ref({
  code: '',
  name: '',
  type: '',
  parent_id: '',
  description: '',
})

const parentAccounts = ref([])
const availableParentAccounts = ref([])

watch(
  () => props.account,
  (newAccount) => {
    if (newAccount) {
      form.value = {
        code: newAccount.code || '',
        name: newAccount.name || '',
        type: newAccount.type || '',
        parent_id: newAccount.parent_id || '',
        description: newAccount.description || '',
      }
      loadParentAccounts()
    } else {
      form.value = {
        code: '',
        name: '',
        type: '',
        parent_id: '',
        description: '',
      }
      availableParentAccounts.value = []
    }
  },
  { immediate: true }
)

const loadParentAccounts = async () => {
  if (!form.value.type) {
    availableParentAccounts.value = []
    return
  }
  
  try {
    const response = await accountsApi.getAll({
      type: form.value.type
    })
    parentAccounts.value = response.data || []
    availableParentAccounts.value = parentAccounts.value.filter(
      acc => acc.id !== props.account?.id
    )
  } catch (error) {
    console.error('Error loading parent accounts:', error)
    parentAccounts.value = []
    availableParentAccounts.value = []
  }
}

const saveAccount = async () => {
  try {
    if (props.account) {
      await accountsApi.update(props.account.id, form.value)
    } else {
      await accountsApi.create(form.value)
    }
    emit('saved')
  } catch (error) {
    console.error('Error saving account:', error)
    alert('Error saving account. Please try again.')
  }
}
</script>
