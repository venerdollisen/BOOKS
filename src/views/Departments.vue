<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Departments</h1>
        <p class="mt-1 text-gray-600">Manage organizational departments and cost centers</p>
      </div>
      <button
        @click="openCreateModal"
        class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition flex items-center gap-2"
      >
        <span>+</span>
        <span>New Department</span>
      </button>
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-lg shadow">
      <div class="flex gap-4 flex-wrap">
        <input
          v-model="filters.search"
          @input="handleSearch"
          type="text"
          placeholder="Search departments..."
          class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
        <select
          v-model="filters.status"
          @change="handleStatusFilter"
          class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option value="">All Status</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
        </select>
        <button
          @click="clearFilters"
          v-if="filters.search || filters.status"
          class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition"
        >
          Clear Filters
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-8">
      <div class="inline-block">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
      </div>
    </div>

    <!-- Error Message -->
    <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
      {{ error }}
    </div>

    <!-- Departments Table -->
    <div v-if="!loading && departments.length > 0" class="bg-white rounded-lg shadow overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Manager</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Budget</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <tr v-for="dept in departments" :key="dept.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ dept.code }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ dept.name }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ dept.manager || '-' }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ formatCurrency(dept.budget) }}</td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span
                :class="{
                  'px-3 py-1 rounded-full text-xs font-medium': true,
                  'bg-green-100 text-green-800': dept.status === 'active',
                  'bg-gray-100 text-gray-800': dept.status === 'inactive',
                }"
              >
                {{ dept.status }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
              <button
                @click="openEditModal(dept)"
                class="text-blue-600 hover:text-blue-800 transition"
              >
                Edit
              </button>
              <button
                @click="confirmDelete(dept)"
                class="text-red-600 hover:text-red-800 transition"
              >
                Delete
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Empty State -->
    <div v-if="!loading && departments.length === 0" class="text-center py-12 bg-white rounded-lg shadow">
      <p class="text-gray-500 text-lg">No departments found</p>
      <button
        @click="openCreateModal"
        class="mt-4 text-blue-600 hover:text-blue-800 transition"
      >
        Create the first department
      </button>
    </div>

    <!-- Pagination -->
    <div v-if="!loading && pagination.last_page > 1" class="flex justify-between items-center">
      <p class="text-sm text-gray-600">
        Showing page <span class="font-medium">{{ pagination.page }}</span> of
        <span class="font-medium">{{ pagination.last_page }}</span>
      </p>
      <div class="flex gap-2">
        <button
          @click="goToPreviousPage"
          :disabled="pagination.page === 1"
          class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium disabled:opacity-50 hover:bg-gray-50 transition"
        >
          Previous
        </button>
        <button
          @click="goToNextPage"
          :disabled="!hasMore"
          class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium disabled:opacity-50 hover:bg-gray-50 transition"
        >
          Next
        </button>
      </div>
    </div>

    <!-- Department Form Modal -->
    <DepartmentForm
      v-if="showForm"
      :department="editingDepartment"
      @save="handleSave"
      @close="closeModal"
    />

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteConfirm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-sm">
        <h2 class="text-lg font-bold mb-4">Delete Department?</h2>
        <p class="text-gray-600 mb-6">
          Are you sure you want to delete <strong>{{ departmentToDelete?.name }}</strong>? This action cannot be undone.
        </p>
        <div class="flex gap-4">
          <button
            @click="showDeleteConfirm = false"
            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition"
          >
            Cancel
          </button>
          <button
            @click="handleDelete"
            :disabled="loading"
            class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition disabled:opacity-50"
          >
            Delete
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useDepartmentStore } from '@/stores/departments'
import DepartmentForm from '@/components/Setup/DepartmentForm.vue'

const departmentStore = useDepartmentStore()

// Local state
const showForm = ref(false)
const showDeleteConfirm = ref(false)
const editingDepartment = ref(null)
const departmentToDelete = ref(null)
const filters = ref({
  search: '',
  status: '',
})

// Computed from store
const { departments, loading, error, pagination, filters: storeFilters, hasMore } = departmentStore

// Methods
const openCreateModal = () => {
  editingDepartment.value = null
  showForm.value = true
}

const openEditModal = (dept) => {
  editingDepartment.value = dept
  showForm.value = true
}

const closeModal = () => {
  showForm.value = false
  editingDepartment.value = null
}

const handleSave = async (formData) => {
  try {
    if (editingDepartment.value) {
      await departmentStore.updateDepartment(editingDepartment.value.id, formData)
    } else {
      await departmentStore.createDepartment(formData)
    }
    closeModal()
    await departmentStore.fetchDepartments()
  } catch (err) {
    console.error('Save error:', err)
  }
}

const confirmDelete = (dept) => {
  departmentToDelete.value = dept
  showDeleteConfirm.value = true
}

const handleDelete = async () => {
  try {
    await departmentStore.deleteDepartment(departmentToDelete.value.id)
    showDeleteConfirm.value = false
    departmentToDelete.value = null
  } catch (err) {
    console.error('Delete error:', err)
  }
}

const handleSearch = () => {
  departmentStore.setFilters({ search: filters.value.search })
}

const handleStatusFilter = () => {
  departmentStore.setFilters({ status: filters.value.status })
}

const clearFilters = () => {
  filters.value = { search: '', status: '' }
  departmentStore.clearFilters()
}

const goToPreviousPage = () => {
  if (pagination.value.page > 1) {
    departmentStore.goToPage(pagination.value.page - 1)
  }
}

const goToNextPage = () => {
  if (hasMore.value) {
    departmentStore.goToPage(pagination.value.page + 1)
  }
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(amount || 0)
}

// Lifecycle
onMounted(async () => {
  await departmentStore.fetchDepartments()
})
</script>
