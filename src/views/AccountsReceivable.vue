<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h2 class="text-2xl font-bold text-gray-900">Accounts Receivable</h2>
        <p class="text-sm text-gray-500 mt-1">Manage customer invoices and payments</p>
      </div>
      <button 
        @click="openNewInvoice" 
        :disabled="loading"
        class="btn btn-primary flex items-center gap-2"
      >
        <PlusIcon v-if="!loading" class="h-5 w-5" />
        <span v-if="loading" class="inline-block animate-spin">⏳</span>
        {{ loading ? 'Loading...' : 'New Invoice' }}
      </button>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
      <div class="card">
        <p class="text-sm font-medium text-gray-600">Total Receivables</p>
        <p class="mt-2 text-2xl font-bold text-gray-900">{{ formatCurrency(summary.total) }}</p>
      </div>
      <div class="card">
        <p class="text-sm font-medium text-gray-600">0-30 Days</p>
        <p class="mt-2 text-2xl font-bold text-green-600">{{ formatCurrency(summary['0-30']) }}</p>
      </div>
      <div class="card">
        <p class="text-sm font-medium text-gray-600">31-60 Days</p>
        <p class="mt-2 text-2xl font-bold text-yellow-600">{{ formatCurrency(summary['31-60']) }}</p>
      </div>
      <div class="card">
        <p class="text-sm font-medium text-gray-600">61-90 Days</p>
        <p class="mt-2 text-2xl font-bold text-orange-600">{{ formatCurrency(summary['61-90']) }}</p>
      </div>
      <div class="card">
        <p class="text-sm font-medium text-gray-600">90+ Days</p>
        <p class="mt-2 text-2xl font-bold text-red-600">{{ formatCurrency(summary['90+']) }}</p>
      </div>
    </div>

    <!-- Filters -->
    <div class="card">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="label">Customer Name</label>
          <input v-model="filters.customer_name" type="text" class="input" placeholder="Search customer..." />
        </div>
        <div>
          <label class="label">Status</label>
          <select v-model="filters.status" class="input">
            <option value="">All Status</option>
            <option value="draft">Draft</option>
            <option value="sent">Sent</option>
            <option value="unpaid">Unpaid</option>
            <option value="partially_paid">Partially Paid</option>
            <option value="paid">Paid</option>
          </select>
        </div>
        <div>
          <label class="label">Date Range</label>
          <input v-model="filters.from_date" type="date" class="input" />
        </div>
        <div class="flex items-end gap-2">
          <button @click="loadInvoices" class="btn btn-secondary flex-1">Apply Filters</button>
          <button @click="resetFilters" class="btn btn-secondary flex-1">Reset</button>
        </div>
      </div>
    </div>

    <!-- Invoices Table -->
    <div class="card">
      <div class="overflow-x-auto">
        <table class="table">
          <thead>
            <tr>
              <th>Invoice #</th>
              <th>Date</th>
              <th>Customer</th>
              <th>Amount</th>
              <th>Paid</th>
              <th>Balance</th>
              <th>Due Date</th>
              <th>Status</th>
              <th class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="invoice in invoices" :key="invoice.id">
              <td class="font-mono font-medium">{{ invoice.invoice_number }}</td>
              <td>{{ formatDate(invoice.invoice_date) }}</td>
              <td>{{ invoice.customer_name }}</td>
              <td class="font-medium">{{ formatCurrency(invoice.total_amount) }}</td>
              <td>{{ formatCurrency(invoice.paid_amount) }}</td>
              <td class="font-medium" :class="invoice.total_amount - invoice.paid_amount > 0 ? 'text-orange-600' : 'text-green-600'">
                {{ formatCurrency(invoice.total_amount - invoice.paid_amount) }}
              </td>
              <td>{{ formatDate(invoice.due_date) }}</td>
              <td>
                <span
                  :class="[
                    'px-2 py-1 text-xs font-medium rounded-full',
                    getStatusClass(invoice.status),
                  ]"
                >
                  {{ invoice.status?.replace('_', ' ') || '-' }}
                </span>
              </td>
              <td class="text-right space-x-2">
                <button
                  @click="editInvoice(invoice)"
                  class="text-blue-600 hover:text-blue-800 transition"
                  v-if="['draft', 'sent'].includes(invoice.status)"
                  title="Edit"
                >
                  ✎
                </button>
                <button
                  @click="viewInvoice(invoice)"
                  class="text-green-600 hover:text-green-800 transition"
                  title="View Details"
                >
                  👁
                </button>
                <button
                  v-if="invoice.status === 'draft'"
                  @click="finalizeInvoice(invoice)"
                  class="text-purple-600 hover:text-purple-800 transition"
                  title="Finalize"
                >
                  ✓
                </button>
                <button
                  v-if="invoice.status !== 'draft' && (invoice.total_amount - invoice.paid_amount) > 0"
                  @click="recordPayment(invoice)"
                  class="text-emerald-600 hover:text-emerald-800 transition"
                  title="Record Payment"
                >
                  💰
                </button>
                <button
                  @click="deleteInvoice(invoice)"
                  class="text-red-600 hover:text-red-800 transition"
                  v-if="invoice.status === 'draft'"
                  title="Delete"
                >
                  🗑
                </button>
              </td>
            </tr>
            <tr v-if="invoices.length === 0">
              <td colspan="9" class="text-center py-8 text-gray-500">No invoices found</td>
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
            @click="currentPage = page; loadInvoices()"
            :class="['px-3 py-1 rounded', page === meta?.current_page ? 'bg-blue-600 text-white' : 'bg-gray-200']"
          >
            {{ page }}
          </button>
        </div>
      </div>
    </div>

    <!-- Invoice Modal -->
    <InvoiceModal
      v-if="showModal"
      :invoice="selectedInvoice"
      @close="showModal = false; selectedInvoice = null"
      @saved="loadInvoices"
    />

    <!-- Payment Modal -->
    <PaymentModal
      v-if="showPaymentModal"
      :invoice="selectedInvoice"
      @close="showPaymentModal = false"
      @saved="loadInvoices"
    />

    <!-- Invoice View Modal -->
    <InvoiceViewModal
      v-if="showViewModal"
      :invoice="selectedInvoice"
      @close="showViewModal = false"
      @finalize="finalizeInvoice"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { receivablesApi } from '@/services/api'
import { useToast } from '@/composables/useToast'
import { PlusIcon } from '@heroicons/vue/24/outline'
import InvoiceModal from '@/components/Transactions/InvoiceModal.vue'
import PaymentModal from '@/components/Transactions/PaymentModal.vue'
import InvoiceViewModal from '@/components/Transactions/InvoiceViewModal.vue'

const invoices = ref([])
const summary = ref({
  total: 0,
  '0-30': 0,
  '31-60': 0,
  '61-90': 0,
  '90+': 0,
})

const meta = ref({
  total: 0,
  per_page: 15,
  current_page: 1,
  last_page: 1,
})

const filters = ref({
  customer_name: '',
  status: '',
  from_date: '',
})

const currentPage = ref(1)
const showModal = ref(false)
const showPaymentModal = ref(false)
const showViewModal = ref(false)
const selectedInvoice = ref(null)
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

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('en-US')
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(amount || 0)
}

const getStatusClass = (status) => {
  const classes = {
    paid: 'bg-green-100 text-green-800',
    unpaid: 'bg-yellow-100 text-yellow-800',
    partially_paid: 'bg-blue-100 text-blue-800',
    draft: 'bg-gray-100 text-gray-800',
    sent: 'bg-purple-100 text-purple-800',
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const loadInvoices = async () => {
  loading.value = true
  try {
    const params = {
      ...filters.value,
      page: currentPage.value,
    }
    const response = await receivablesApi.getInvoices(params)
    invoices.value = response.data.data
    meta.value = response.meta

    const agingResponse = await receivablesApi.getAgingReport()
    summary.value = agingResponse.data
  } catch (err) {
    console.error('Error loading invoices:', err)
    showError('Error loading invoices')
  } finally {
    loading.value = false
  }
}

const resetFilters = () => {
  filters.value = { customer_name: '', status: '', from_date: '' }
  currentPage.value = 1
  loadInvoices()
}

const openNewInvoice = () => {
  selectedInvoice.value = null
  showModal.value = true
}

const editInvoice = (invoice) => {
  selectedInvoice.value = invoice
  showModal.value = true
}

const viewInvoice = (invoice) => {
  selectedInvoice.value = invoice
  showViewModal.value = true
}

const finalizeInvoice = async (invoice) => {
  if (!confirm('Finalize this invoice? This will create an accounting transaction.')) {
    return
  }

  try {
    await receivablesApi.finalizeInvoice(invoice.id)
    success('Invoice finalized successfully')
    await loadInvoices()
  } catch (err) {
    console.error('Error finalizing invoice:', err)
    showError(err.response?.data?.error || 'Error finalizing invoice')
  }
}

const recordPayment = (invoice) => {
  selectedInvoice.value = invoice
  showPaymentModal.value = true
}

const deleteInvoice = async (invoice) => {
  if (!confirm(`Delete invoice ${invoice.invoice_number}?`)) {
    return
  }

  try {
    await receivablesApi.deleteInvoice(invoice.id)
    success('Invoice deleted successfully')
    await loadInvoices()
  } catch (err) {
    console.error('Error deleting invoice:', err)
    showError('Error deleting invoice')
  }
}

watch(currentPage, () => {
  loadInvoices()
})

onMounted(() => {
  loadInvoices()
})
</script>
