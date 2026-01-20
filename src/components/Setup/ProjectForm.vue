<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-md max-h-[90vh] overflow-y-auto">
      <h2 class="text-xl font-bold mb-4">{{ isEditing ? 'Edit Project' : 'New Project' }}</h2>

      <form @submit.prevent="handleSubmit" class="space-y-4">
        <!-- Name -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Project Name
          </label>
          <input
            v-model="form.name"
            type="text"
            placeholder="e.g., Website Redesign"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            required
          />
        </div>

        <!-- Department -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Department
          </label>
          <select
            v-model="form.department_id"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            required
          >
            <option value="">Select a department</option>
            <option v-for="dept in departments" :key="dept.id" :value="dept.id">
              {{ dept.name }}
            </option>
          </select>
        </div>

        <!-- Start Date -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Start Date
          </label>
          <input
            v-model="form.start_date"
            type="date"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <!-- End Date -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            End Date
          </label>
          <input
            v-model="form.end_date"
            type="date"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <!-- Budget -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Project Budget
          </label>
          <input
            v-model.number="form.budget"
            type="number"
            placeholder="e.g., 50000"
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
            <option value="planning">Planning</option>
            <option value="active">Active</option>
            <option value="paused">Paused</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
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
            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition disabled:opacity-50"
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
import { useDepartmentStore } from '@/stores/departments'
import { useToast } from '@/composables/useToast'

const departmentStore = useDepartmentStore()
const { success, error: showError } = useToast()

const props = defineProps({
  project: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['save', 'close'])

const isEditing = computed(() => !!props.project)
const loading = ref(false)
const error = ref(null)
const departments = computed(() => departmentStore.departments)

const form = reactive({
  name: '',
  department_id: '',
  start_date: '',
  end_date: '',
  budget: 0,
  status: 'planning',
})

watch(
  () => props.project,
  (newProject) => {
    if (newProject) {
      form.name = newProject.name
      form.department_id = newProject.department_id
      form.start_date = newProject.start_date
      form.end_date = newProject.end_date
      form.budget = newProject.budget || 0
      form.status = newProject.status
    } else {
      form.name = ''
      form.department_id = ''
      form.start_date = ''
      form.end_date = ''
      form.budget = 0
      form.status = 'planning'
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
    success(`Project ${action} successfully!`)
  } catch (err) {
    error.value = err.message || 'An error occurred'
    showError(error.value)
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  if (departmentStore.departments.length === 0) {
    await departmentStore.fetchDepartments()
  }
})
</script>
