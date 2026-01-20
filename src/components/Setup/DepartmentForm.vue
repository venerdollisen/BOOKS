<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-md">
      <h2 class="text-xl font-bold mb-4">{{ isEditing ? 'Edit Department' : 'New Department' }}</h2>

      <form @submit.prevent="handleSubmit" class="space-y-4">
        <!-- Code -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Department Code
          </label>
          <input
            v-model="form.code"
            type="text"
            placeholder="e.g., SALES"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            required
          />
        </div>

        <!-- Name -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Department Name
          </label>
          <input
            v-model="form.name"
            type="text"
            placeholder="e.g., Sales Department"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            required
          />
        </div>

        <!-- Manager -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Manager Name
          </label>
          <input
            v-model="form.manager"
            type="text"
            placeholder="e.g., John Doe"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <!-- Budget -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Annual Budget
          </label>
          <input
            v-model.number="form.budget"
            type="number"
            placeholder="e.g., 100000"
            min="0"
            step="0.01"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
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
import { reactive, computed, ref, watch } from 'vue'
import { useToast } from '@/composables/useToast'

const props = defineProps({
  department: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['save', 'close'])

const { success, error: showError } = useToast()

const isEditing = computed(() => !!props.department)
const loading = ref(false)
const error = ref(null)

const form = reactive({
  code: '',
  name: '',
  manager: '',
  budget: 0,
  status: 'active',
})

watch(
  () => props.department,
  (newDept) => {
    if (newDept) {
      form.code = newDept.code
      form.name = newDept.name
      form.manager = newDept.manager || ''
      form.budget = newDept.budget || 0
      form.status = newDept.status
    } else {
      form.code = ''
      form.name = ''
      form.manager = ''
      form.budget = 0
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
    success(`Department ${action} successfully!`)
  } catch (err) {
    error.value = err.message || 'An error occurred'
    showError(error.value)
  } finally {
    loading.value = false
  }
}
</script>
