<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Period Management</h1>
        <p class="text-sm text-gray-500 mt-1">Manage fiscal periods and close accounting cycles</p>
      </div>
      <button
        @click="showForm = true"
        class="bg-[#06275c] hover:bg-[#051f47] text-white px-4 py-2 rounded-lg flex items-center gap-2"
      >
        + New Period
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="bg-white rounded-lg shadow p-8 text-center">
      <div class="inline-block">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-[#06275c]"></div>
      </div>
      <p class="mt-4 text-gray-600">Loading periods...</p>
    </div>

    <!-- Periods Table -->
    <div v-else class="bg-white rounded-lg shadow overflow-hidden">
      <div v-if="periods.length === 0" class="p-8 text-center text-gray-500">
        <p class="mb-4">No periods created yet</p>
        <button
          @click="showForm = true"
          class="btn btn-primary"
        >
          Create First Period
        </button>
      </div>

      <table v-else class="w-full">
        <thead class="bg-gray-100 border-b border-gray-200">
          <tr>
            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Period Name</th>
            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Start Date</th>
            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">End Date</th>
            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Notes</th>
            <th class="px-6 py-3 text-center text-sm font-semibold text-gray-900">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <tr
            v-for="period in periods"
            :key="period.id"
            class="hover:bg-gray-50 transition"
          >
            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ period.name }}</td>
            <td class="px-6 py-4 text-sm text-gray-700">{{ formatDate(period.start_date) }}</td>
            <td class="px-6 py-4 text-sm text-gray-700">{{ formatDate(period.end_date) }}</td>
            <td class="px-6 py-4 text-sm">
              <span
                :class="[
                  'px-2 py-1 rounded-full text-xs font-medium',
                  getStatusClass(period.status)
                ]"
              >
                {{ period.status }}
              </span>
            </td>
            <td class="px-6 py-4 text-sm text-gray-700">{{ period.notes || '-' }}</td>
            <td class="px-6 py-4 text-center text-sm">
              <div class="flex justify-center gap-2">
                <button
                  v-if="period.status === 'open'"
                  @click="editPeriod(period)"
                  class="text-blue-600 hover:text-blue-800 font-medium"
                >
                  Edit
                </button>
                <button
                  v-if="period.status === 'open'"
                  @click="closePeriod(period.id)"
                  class="text-orange-600 hover:text-orange-800 font-medium"
                >
                  Close
                </button>
                <button
                  v-if="period.status === 'open'"
                  @click="deletePeriod(period.id)"
                  class="text-red-600 hover:text-red-800 font-medium"
                >
                  Delete
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Period Form Modal -->
    <div v-if="showForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
        <!-- Header -->
        <div class="border-b border-gray-200 px-6 py-4 flex justify-between items-center">
          <h2 class="text-xl font-bold text-gray-900">
            {{ editingPeriod ? 'Edit Period' : 'New Period' }}
          </h2>
          <button
            @click="closeForm"
            class="text-gray-400 hover:text-gray-600 text-2xl"
          >
            ×
          </button>
        </div>

        <!-- Form -->
        <form @submit.prevent="submitForm" class="p-6 space-y-4">
          <!-- Period Name -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Period Name</label>
            <input
              v-model="form.name"
              type="text"
              placeholder="e.g., January 2026"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#06275c]"
            />
            <span v-if="formErrors.name" class="text-xs text-red-600 mt-1">{{ formErrors.name[0] }}</span>
          </div>

          <!-- Start Date -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
            <input
              v-model="form.start_date"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#06275c]"
            />
            <span v-if="formErrors.start_date" class="text-xs text-red-600 mt-1">{{ formErrors.start_date[0] }}</span>
          </div>

          <!-- End Date -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
            <input
              v-model="form.end_date"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#06275c]"
            />
            <span v-if="formErrors.end_date" class="text-xs text-red-600 mt-1">{{ formErrors.end_date[0] }}</span>
          </div>

          <!-- Notes -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
            <textarea
              v-model="form.notes"
              rows="3"
              placeholder="Any notes about this period..."
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#06275c]"
            ></textarea>
          </div>

          <!-- Buttons -->
          <div class="flex gap-3 pt-4">
            <button
              type="button"
              @click="closeForm"
              class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="formLoading"
              class="flex-1 px-4 py-2 bg-[#06275c] text-white rounded-lg hover:bg-[#051f47] disabled:opacity-50"
            >
              {{ formLoading ? 'Saving...' : 'Save Period' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/config/api'
import { useToast } from '@/composables/useToast'

const loading = ref(false)
const showForm = ref(false)
const formLoading = ref(false)
const formErrors = ref({})
const periods = ref([])
const editingPeriod = ref(null)
const form = ref({
  name: '',
  start_date: '',
  end_date: '',
  notes: '',
})

const { success, error: showError } = useToast()

function formatDate(dateStr) {
  return new Date(dateStr).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: '2-digit',
  })
}

function getStatusClass(status) {
  const classes = {
    open: 'bg-green-100 text-green-800',
    closed: 'bg-yellow-100 text-yellow-800',
    locked: 'bg-red-100 text-red-800',
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

async function fetchPeriods() {
  loading.value = true

  try {
    const response = await api.get('/periods')
    if (response.data.success) {
      periods.value = response.data.data
    }
  } catch (err) {
    showError('Error loading periods')
  } finally {
    loading.value = false
  }
}

function openForm() {
  showForm.value = true
  editingPeriod.value = null
  form.value = { name: '', start_date: '', end_date: '', notes: '' }
  formErrors.value = {}
}

function editPeriod(period) {
  editingPeriod.value = period
  form.value = { ...period }
  showForm.value = true
  formErrors.value = {}
}

function closeForm() {
  showForm.value = false
  editingPeriod.value = null
  form.value = { name: '', start_date: '', end_date: '', notes: '' }
  formErrors.value = {}
}

async function submitForm() {
  formLoading.value = true
  formErrors.value = {}

  try {
    if (editingPeriod.value) {
      const response = await api.put(`/periods/${editingPeriod.value.id}`, form.value)
      if (response.data.success) {
        success('Period updated successfully!')
        closeForm()
        fetchPeriods()
      }
    } else {
      const response = await api.post('/periods', form.value)
      if (response.data.success) {
        success('Period created successfully!')
        closeForm()
        fetchPeriods()
      }
    }
  } catch (err) {
    if (err.response?.data?.errors) {
      formErrors.value = err.response.data.errors
    } else {
      showError(err.response?.data?.message || 'Error saving period')
    }
  } finally {
    formLoading.value = false
  }
}

async function closePeriod(periodId) {
  if (!confirm('Close this period? Transactions can no longer be posted to closed periods.')) {
    return
  }

  try {
    const response = await api.post(`/periods/${periodId}/close`)
    if (response.data.success) {
      success('Period closed successfully!')
      fetchPeriods()
    }
  } catch (err) {
    showError(err.response?.data?.message || 'Error closing period')
  }
}

async function deletePeriod(periodId) {
  if (!confirm('Delete this period? This cannot be undone.')) {
    return
  }

  try {
    const response = await api.delete(`/periods/${periodId}`)
    if (response.data.success) {
      success('Period deleted successfully!')
      fetchPeriods()
    }
  } catch (err) {
    showError(err.response?.data?.message || 'Error deleting period')
  }
}

onMounted(() => {
  fetchPeriods()
})
</script>
