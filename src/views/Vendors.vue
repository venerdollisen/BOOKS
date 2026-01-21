<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h2 class="text-2xl font-bold text-gray-900">Vendors</h2>
        <p class="text-sm text-gray-500 mt-1">Manage your vendor database</p>
      </div>
      <button @click="showModal = true" class="btn btn-primary flex items-center gap-2">
        <span>+</span> New Vendor
      </button>
    </div>

    <!-- Filters -->
    <div class="card">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="label">Search</label>
          <input v-model="filters.search" type="text" class="input" placeholder="Name, email, or phone..." />
        </div>
        <div>
          <label class="label">Status</label>
          <select v-model="filters.is_active" class="input">
            <option value="">All</option>
            <option :value="true">Active</option>
            <option :value="false">Inactive</option>
          </select>
        </div>
        <div class="flex items-end gap-2">
          <button @click="loadVendors" class="btn btn-secondary flex-1">Search</button>
          <button @click="resetFilters" class="btn btn-secondary flex-1">Reset</button>
        </div>
      </div>
    </div>

    <!-- Vendors Table -->
    <div class="card">
      <div class="overflow-x-auto">
        <table class="table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Email</th>
              <th>Phone</th>
              <th>City</th>
              <th>Payment Terms</th>
              <th>Status</th>
              <th class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="vendor in vendors" :key="vendor.id">
              <td class="font-medium">{{ vendor.name }}</td>
              <td>{{ vendor.email || '-' }}</td>
              <td>{{ vendor.phone || '-' }}</td>
              <td>{{ vendor.city || '-' }}</td>
              <td>{{ vendor.payment_terms }}</td>
              <td>
                <span :class="vendor.is_active ? 'text-green-600' : 'text-red-600'">
                  {{ vendor.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="text-right space-x-2">
                <button @click="editVendor(vendor)" class="text-blue-600 hover:text-blue-800">Edit</button>
                <button @click="deleteVendor(vendor)" class="text-red-600 hover:text-red-800">Delete</button>
              </td>
            </tr>
            <tr v-if="vendors.length === 0">
              <td colspan="7" class="text-center py-8 text-gray-500">No vendors found</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="meta?.total > 0" class="flex justify-between items-center mt-4 pt-4 border-t">
        <div class="text-sm text-gray-600">
          Showing {{ (meta?.current_page - 1) * meta?.per_page + 1 }} to {{ Math.min(meta?.current_page * meta?.per_page, meta?.total) }} of {{ meta?.total }}
        </div>
        <div class="flex gap-2">
          <button
            v-for="page in getPageNumbers"
            :key="page"
            @click="currentPage = page; loadVendors()"
            :class="['px-3 py-1 rounded', page === meta?.current_page ? 'bg-blue-600 text-white' : 'bg-gray-200']"
          >
            {{ page }}
          </button>
        </div>
      </div>
    </div>

    <!-- Vendor Modal -->
    <VendorModal
      v-if="showModal"
      :vendor="selectedVendor"
      @close="showModal = false; selectedVendor = null"
      @saved="loadVendors"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { vendorsApi } from '@/services/api'
import { useToast } from '@/composables/useToast'
import VendorModal from '@/components/Settings/VendorModal.vue'

const vendors = ref([])
const meta = ref({
  total: 0,
  per_page: 15,
  current_page: 1,
  last_page: 1,
})

const filters = ref({
  search: '',
  is_active: '',
})

const currentPage = ref(1)
const showModal = ref(false)
const selectedVendor = ref(null)

const { success, error: showError } = useToast()

const getPageNumbers = computed(() => {
  const pages = []
  const maxPages = Math.min(meta.value.last_page, 5)
  const start = Math.max(1, meta.value.current_page - 2)
  const end = Math.min(meta.value.last_page, start + maxPages - 1)

  for (let i = start; i <= end; i++) {
    pages.push(i)
  }

  return pages
})

const loadVendors = async () => {
  try {
    const params = {
      page: currentPage.value,
      per_page: 15,
      ...filters.value,
    }
    const response = await vendorsApi.getVendors(params)
    vendors.value = response.data
    meta.value = response.meta
  } catch (err) {
    console.error('Error loading vendors:', err)
    showError('Error loading vendors')
  }
}

const resetFilters = () => {
  filters.value = { search: '', is_active: '' }
  currentPage.value = 1
  loadVendors()
}

const editVendor = (vendor) => {
  selectedVendor.value = vendor
  showModal.value = true
}

const deleteVendor = async (vendor) => {
  if (!confirm(`Delete vendor ${vendor.name}?`)) {
    return
  }

  try {
    await vendorsApi.deleteVendor(vendor.id)
    success('Vendor deleted successfully')
    await loadVendors()
  } catch (err) {
    showError('Error deleting vendor')
  }
}

watch(currentPage, () => {
  loadVendors()
})

onMounted(() => {
  loadVendors()
})
</script>
