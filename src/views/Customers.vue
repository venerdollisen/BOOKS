<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h2 class="text-2xl font-bold text-gray-900">Customers</h2>
        <p class="text-sm text-gray-500 mt-1">Manage your customer database</p>
      </div>
      <button @click="openNewCustomer" :disabled="loading" class="btn btn-primary flex items-center gap-2">
        <span>+</span> New Customer
      </button>
    </div>

    <!-- Filters -->
    <div class="card">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="label">Search</label>
          <input v-model="filters.search" type="text" class="input" placeholder="Name, email, or phone..." @keyup.enter="loadCustomers" />
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
          <button @click="loadCustomers" :disabled="loading" class="btn btn-secondary flex-1">Search</button>
          <button @click="resetFilters" :disabled="loading" class="btn btn-secondary flex-1">Reset</button>
        </div>
      </div>
    </div>

    <!-- Customers Table -->
    <div class="card">
      <div v-if="loading" class="flex justify-center py-8">
        <div class="animate-spin">⏳</div>
        <span class="ml-2">Loading customers...</span>
      </div>
      <div v-else class="overflow-x-auto">
        <table class="table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Email</th>
              <th>Phone</th>
              <th>City</th>
              <th>Credit Limit</th>
              <th>Status</th>
              <th class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="customer in customers" :key="customer.id">
              <td class="font-medium">{{ customer.name }}</td>
              <td>{{ customer.email || '-' }}</td>
              <td>{{ customer.phone || '-' }}</td>
              <td>{{ customer.city || '-' }}</td>
              <td>{{ formatCurrency(customer.credit_limit) }}</td>
              <td>
                <span :class="customer.is_active ? 'text-green-600' : 'text-red-600'">
                  {{ customer.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="text-right space-x-2">
                <button @click="editCustomer(customer)" class="text-blue-600 hover:text-blue-800">Edit</button>
                <button @click="deleteCustomer(customer)" class="text-red-600 hover:text-red-800">Delete</button>
              </td>
            </tr>
            <tr v-if="customers.length === 0 && !loading">
              <td colspan="7" class="text-center py-8 text-gray-500">No customers found</td>
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
            @click="currentPage = page"
            :disabled="loading"
            :class="['px-3 py-1 rounded', page === meta?.current_page ? 'bg-blue-600 text-white' : 'bg-gray-200 hover:bg-gray-300']"
          >
            {{ page }}
          </button>
        </div>
      </div>
    </div>

    <!-- Customer Modal -->
    <CustomerModal
      v-if="showModal"
      :customer="selectedCustomer"
      @close="showModal = false; selectedCustomer = null"
      @saved="loadCustomers"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { customersApi } from '@/services/api'
import { useToast } from '@/composables/useToast'
import CustomerModal from '@/components/Settings/CustomerModal.vue'

const customers = ref([])
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
const selectedCustomer = ref(null)
const loading = ref(false)

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

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(amount || 0)
}

const loadCustomers = async () => {
  loading.value = true
  try {
    const params = {
      page: currentPage.value,
      per_page: 15,
    }
    
    if (filters.value.search) {
      params.search = filters.value.search
    }
    
    if (filters.value.is_active !== '') {
      params.is_active = filters.value.is_active === 'true' || filters.value.is_active === true
    }

    const response = await customersApi.getCustomers(params)

    customers.value = response.data.data || []
    meta.value = response.meta || {
      total: 0,
      per_page: 15,
      current_page: 1,
      last_page: 1,
    }
  } catch (err) {
    console.error('Error loading customers:', err)
    showError('Error loading customers')
  } finally {
    loading.value = false
  }
}

const resetFilters = () => {
  filters.value = { search: '', is_active: '' }
  currentPage.value = 1
  loadCustomers()
}

const openNewCustomer = () => {
  selectedCustomer.value = null
  showModal.value = true
}

const editCustomer = (customer) => {
  selectedCustomer.value = customer
  showModal.value = true
}

const deleteCustomer = async (customer) => {
  if (!confirm(`Delete customer ${customer.name}?`)) {
    return
  }

  try {
    await customersApi.deleteCustomer(customer.id)
    success('Customer deleted successfully')
    await loadCustomers()
  } catch (err) {
    showError('Error deleting customer')
  }
}

watch(currentPage, () => {
  loadCustomers()
})

onMounted(() => {
  loadCustomers()
})
</script>
