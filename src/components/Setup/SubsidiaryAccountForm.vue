<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-md max-h-[90vh] overflow-y-auto">
      <h2 class="text-xl font-bold mb-4">{{ isEditing ? 'Edit Subsidiary Account' : 'New Subsidiary Account' }}</h2>

      <form @submit.prevent="handleSubmit" class="space-y-4">
        <!-- Code -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Account Code
          </label>
          <input
            v-model="form.code"
            type="text"
            placeholder="e.g., SAL-DOM"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            required
          />
        </div>

        <!-- Name -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Account Name
          </label>
          <input
            v-model="form.name"
            type="text"
            placeholder="e.g., Domestic Sales"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            required
          />
        </div>

        <!-- Main Account -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Main Account
          </label>
          <select
            v-model="form.account_id"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            required
          >
            <option value="">Select a main account</option>
            <option v-for="account in accounts" :key="account.id" :value="account.id">
              {{ account.code }} - {{ account.name }}
            </option>
          </select>
        </div>

        <!-- Type -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Type
          </label>
          <select
            v-model="form.type"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            required
          >
            <option value="cost_center">Cost Center</option>
            <option value="profit_center">Profit Center</option>
            <option value="branch">Branch</option>
            <option value="division">Division</option>
            <option value="custom">Custom</option>
          </select>
        </div>

        <!-- Status -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Status
          </label>
          <select
            v-model="form.status"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>

        <!-- Buttons -->
        <div class="flex gap-4 pt-4">
          <button
            type="button"
            @click="$emit('close')"
            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="loading"
            class="flex-1 px-4 py-2 bg-[#06275c] text-white rounded-lg hover:bg-[#051f47] transition disabled:opacity-50"
          >
            {{ isEditing ? 'Update' : 'Create' }}
          </button>
        </div>
      </form>

      <div v-if="error" class="mt-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
        {{ error }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, computed, ref, watch, onMounted } from 'vue'
import { accountsApi } from '@/services/api'
import { useToast } from '@/composables/useToast'

const props = defineProps({
  account: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['save', 'close'])

const { success, error: showError } = useToast()

const isEditing = computed(() => !!props.account)
const loading = ref(false)
const error = ref(null)
const accounts = ref([])

const form = reactive({
  code: '',
  name: '',
  account_id: '',
  type: 'cost_center',
  status: 'active',
})

watch(
  () => props.account,
  (newAccount) => {
    if (newAccount) {
      form.code = newAccount.code
      form.name = newAccount.name
      form.account_id = newAccount.account_id
      form.type = newAccount.type
      form.status = newAccount.status
    } else {
      form.code = ''
      form.name = ''
      form.account_id = ''
      form.type = 'cost_center'
      form.status = 'active'
    }
    error.value = null
  },
  { immediate: true }
)

const handleSubmit = async () => {
  error.value = null
  loading.value = true

  try {
    emit('save', { ...form })
    const action = isEditing.value ? 'updated' : 'created'
    success(`Subsidiary account ${action} successfully!`)
  } catch (err) {
    error.value = err.message || 'An error occurred'
    showError(error.value)
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  try {
    const { data } = await accountsApi.getAll()
    accounts.value = data.data
  } catch (err) {
    console.error('Failed to load accounts:', err)
    showError('Failed to load accounts')
  }
})
</script>
