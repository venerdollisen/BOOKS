<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <h1 class="text-3xl font-bold text-gray-900">Transactions</h1>
      <button
        @click="showForm = true"
        class="bg-[#06275c] text-white px-4 py-2 rounded-lg hover:bg-[#051f47] transition"
      >
        + New Transaction
      </button>
    </div>

    <!-- Filters Row -->
    <div class="bg-white rounded-lg shadow p-4 space-y-4">
      <!-- Search and Type/Status Row -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Search -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
          <input
            v-model.lazy="transactionStore.filters.search"
            @input="transactionStore.setSearchQuery($event.target.value)"
            type="text"
            placeholder="Search by reference..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <!-- Type Filter -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
          <select
            v-model="transactionStore.filters.type"
            @change="transactionStore.setTransactionType($event.target.value)"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">All Types</option>
            <option value="receipt">Receipt</option>
            <option value="payment">Payment</option>
            <option value="journal">Journal Entry</option>
            <option value="transfer">Transfer</option>
          </select>
        </div>

        <!-- Status Filter -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select
            v-model="transactionStore.filters.status"
            @change="transactionStore.setTransactionStatus($event.target.value)"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">All Statuses</option>
            <option value="draft">Draft</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
          </select>
        </div>

        <!-- Per Page -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Per Page</label>
          <select
            :value="transactionStore.pagination.per_page"
            @change="transactionStore.setPerPage(parseInt($event.target.value))"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="10">10</option>
            <option value="20">20</option>
            <option value="50">50</option>
            <option value="100">100</option>
          </select>
        </div>
      </div>

      <!-- Date Range Row -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
          <input
            v-model="startDate"
            type="date"
            @change="updateDateRange"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
          <input
            v-model="endDate"
            type="date"
            @change="updateDateRange"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <div class="flex items-end">
          <button
            v-if="hasActiveFilters"
            @click="transactionStore.clearFilters()"
            class="w-full px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition"
          >
            Clear Filters
          </button>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="transactionStore.loading" class="flex justify-center items-center py-12">
      <div class="text-lg text-gray-600">Loading transactions...</div>
    </div>

    <!-- Error State -->
    <div
      v-if="transactionStore.error && !transactionStore.loading"
      class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-700"
    >
      {{ transactionStore.error }}
    </div>

    <!-- Table -->
    <div
      v-if="!transactionStore.loading && transactionStore.transactions.length > 0"
      class="bg-white rounded-lg shadow overflow-hidden"
    >
      <table class="w-full">
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr>
            <th
              @click="transactionStore.toggleSort('reference')"
              class="px-6 py-3 text-left text-sm font-semibold text-gray-900 cursor-pointer hover:bg-gray-100"
            >
              Reference
              <span v-if="transactionStore.sorting.sort_by === 'reference'" class="ml-1">
                {{ transactionStore.sorting.sort_order === 'asc' ? '↑' : '↓' }}
              </span>
            </th>
            <th
              @click="transactionStore.toggleSort('transaction_date')"
              class="px-6 py-3 text-left text-sm font-semibold text-gray-900 cursor-pointer hover:bg-gray-100"
            >
              Date
              <span v-if="transactionStore.sorting.sort_by === 'transaction_date'" class="ml-1">
                {{ transactionStore.sorting.sort_order === 'asc' ? '↑' : '↓' }}
              </span>
            </th>
            <th
              @click="transactionStore.toggleSort('type')"
              class="px-6 py-3 text-left text-sm font-semibold text-gray-900 cursor-pointer hover:bg-gray-100"
            >
              Type
              <span v-if="transactionStore.sorting.sort_by === 'type'" class="ml-1">
                {{ transactionStore.sorting.sort_order === 'asc' ? '↑' : '↓' }}
              </span>
            </th>
            <th
              @click="transactionStore.toggleSort('status')"
              class="px-6 py-3 text-left text-sm font-semibold text-gray-900 cursor-pointer hover:bg-gray-100"
            >
              Status
              <span v-if="transactionStore.sorting.sort_by === 'status'" class="ml-1">
                {{ transactionStore.sorting.sort_order === 'asc' ? '↑' : '↓' }}
              </span>
            </th>
            <th
              @click="transactionStore.toggleSort('amount')"
              class="px-6 py-3 text-right text-sm font-semibold text-gray-900 cursor-pointer hover:bg-gray-100"
            >
              Amount
              <span v-if="transactionStore.sorting.sort_by === 'amount'" class="ml-1">
                {{ transactionStore.sorting.sort_order === 'asc' ? '↑' : '↓' }}
              </span>
            </th>
            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Description</th>
            <th class="px-6 py-3 text-center text-sm font-semibold text-gray-900">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <tr
            v-for="transaction in transactionStore.transactions"
            :key="transaction.id"
            class="hover:bg-gray-50 transition"
          >
            <td class="px-6 py-4 text-sm text-gray-900 font-medium">
              {{ transaction.reference }}
            </td>
            <td class="px-6 py-4 text-sm text-gray-600">
              {{ formatDate(transaction.transaction_date) }}
            </td>
            <td class="px-6 py-4 text-sm">
              <span
                :class="getTypeBadgeClass(transaction.type)"
                class="px-3 py-1 rounded-full text-xs font-semibold"
              >
                {{ transaction.type }}
              </span>
            </td>
            <td class="px-6 py-4 text-sm">
              <span
                :class="getStatusBadgeClass(transaction.status)"
                class="px-3 py-1 rounded-full text-xs font-semibold"
              >
                {{ transaction.status }}
              </span>
            </td>
            <td class="px-6 py-4 text-sm text-right font-semibold text-gray-900">
              {{ formatCurrency(transaction.amount) }}
            </td>
            <td class="px-6 py-4 text-sm text-gray-600 truncate">
              {{ transaction.description || '-' }}
            </td>
            <td class="px-6 py-4 text-sm text-center space-x-2">
              <button
                @click="editTransaction(transaction)"
                class="text-blue-600 hover:text-blue-800 transition"
                title="Edit"
              >
                ✎
              </button>
              <button
                @click="viewTransaction(transaction)"
                class="text-green-600 hover:text-green-800 transition"
                title="View Details"
              >
                👁
              </button>
              <button
                v-if="transaction.status === 'draft' || transaction.status === 'rejected'"
                @click="deleteTransaction(transaction.id)"
                class="text-red-600 hover:text-red-800 transition"
                title="Delete"
              >
                🗑
              </button>
              <button
                v-if="transaction.status === 'pending'"
                @click="approveTransaction(transaction.id)"
                class="text-green-600 hover:text-green-800 transition"
                title="Approve"
              >
                ✓
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Empty State -->
    <div
      v-if="!transactionStore.loading && transactionStore.transactions.length === 0"
      class="bg-white rounded-lg shadow p-12 text-center"
    >
      <p class="text-gray-600 text-lg mb-4">No transactions found</p>
      <button
        @click="showForm = true"
        class="bg-[#06275c] text-white px-4 py-2 rounded-lg hover:bg-[#051f47] transition"
      >
        Create First Transaction
      </button>
    </div>

    <!-- Pagination Info -->
    <div
      v-if="!transactionStore.loading && transactionStore.transactions.length > 0"
      class="flex items-center justify-between bg-white rounded-lg shadow p-4"
    >
      <p class="text-sm text-gray-600">
        Showing
        <strong>{{ transactionStore.pagination.from }}</strong>
        to
        <strong>{{ transactionStore.pagination.to }}</strong>
        of
        <strong>{{ transactionStore.pagination.total }}</strong>
        transactions
      </p>

      <!-- Pagination Controls -->
      <div class="flex gap-2 items-center">
        <button
          @click="transactionStore.prevPage()"
          :disabled="transactionStore.pagination.current_page === 1"
          class="px-3 py-1 border border-gray-300 rounded-md hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition"
        >
          Previous
        </button>

        <div class="flex gap-1">
          <button
            v-for="page in paginationButtons"
            :key="page"
            @click="transactionStore.goToPage(page)"
            :class="
              page === transactionStore.pagination.current_page
                ? 'bg-[#06275c] text-white'
                : 'border border-gray-300 hover:bg-gray-100'
            "
            class="px-3 py-1 rounded-md transition"
          >
            {{ page }}
          </button>
        </div>

        <button
          @click="transactionStore.nextPage()"
          :disabled="transactionStore.pagination.current_page === transactionStore.pagination.last_page"
          class="px-3 py-1 border border-gray-300 rounded-md hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition"
        >
          Next
        </button>
      </div>
    </div>

    <!-- Modals -->
    <TransactionForm
      v-if="showForm"
      :transaction="selectedTransaction"
      @close="closeForm"
      @saved="onTransactionSaved"
    />

    <TransactionDetails
      v-if="showDetails"
      :transaction="selectedTransaction"
      @close="closeDetails"
      @approve="onApprove"
      @reject="onReject"
      @edit="onEdit"
      @delete="onDelete"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useTransactionStore } from '@/stores/transactions';
import TransactionForm from './TransactionForm.vue';
import TransactionDetails from './TransactionDetails.vue';

const transactionStore = useTransactionStore();
const showForm = ref(false);
const showDetails = ref(false);
const selectedTransaction = ref(null);
const startDate = ref('');
const endDate = ref('');

const hasActiveFilters = computed(() => {
  return (
    transactionStore.filters.search ||
    transactionStore.filters.type ||
    transactionStore.filters.status ||
    transactionStore.filters.start_date ||
    transactionStore.filters.end_date
  );
});

const paginationButtons = computed(() => {
  const current = transactionStore.pagination.current_page;
  const last = transactionStore.pagination.last_page;
  const buttons = [];

  if (last <= 5) {
    for (let i = 1; i <= last; i++) {
      buttons.push(i);
    }
  } else {
    buttons.push(1);
    if (current > 3) buttons.push('...');
    for (let i = Math.max(2, current - 1); i <= Math.min(last - 1, current + 1); i++) {
      if (!buttons.includes(i)) buttons.push(i);
    }
    if (current < last - 2) buttons.push('...');
    buttons.push(last);
  }

  return buttons.filter((btn, idx, arr) => arr.indexOf(btn) === idx);
});

function formatDate(date) {
  return new Intl.DateTimeFormat('en-US').format(new Date(date));
}

function formatCurrency(value) {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(value);
}

function getTypeBadgeClass(type) {
  const classes = {
    receipt: 'bg-green-100 text-green-800',
    payment: 'bg-red-100 text-red-800',
    journal: 'bg-blue-100 text-blue-800',
    transfer: 'bg-purple-100 text-purple-800',
  };
  return classes[type] || 'bg-gray-100 text-gray-800';
}

function getStatusBadgeClass(status) {
  const classes = {
    draft: 'bg-gray-100 text-gray-800',
    pending: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
}

function editTransaction(transaction) {
  selectedTransaction.value = transaction;
  showForm.value = true;
}

function viewTransaction(transaction) {
  selectedTransaction.value = transaction;
  showDetails.value = true;
}

async function deleteTransaction(id) {
  if (confirm('Are you sure you want to delete this transaction?')) {
    const success = await transactionStore.deleteTransaction(id);
    if (success) {
      alert('Transaction deleted successfully');
    }
  }
}

async function approveTransaction(id) {
  const success = await transactionStore.approveTransaction(id);
  if (success) {
    alert('Transaction approved');
    await transactionStore.fetchTransactions(transactionStore.pagination.current_page);
  }
}

function updateDateRange() {
  transactionStore.setDateRange(startDate.value, endDate.value);
}

function closeForm() {
  showForm.value = false;
  selectedTransaction.value = null;
}

function closeDetails() {
  showDetails.value = false;
  selectedTransaction.value = null;
}

function onTransactionSaved() {
  closeForm();
  // Store will auto-refresh list
}

async function onApprove(id) {
  const success = await transactionStore.approveTransaction(id);
  if (success) {
    closeDetails();
    alert('Transaction approved');
  }
}

async function onReject(id, reason) {
  const success = await transactionStore.rejectTransaction(id, reason);
  if (success) {
    closeDetails();
    alert('Transaction rejected');
  }
}

function onEdit(transaction) {
  selectedTransaction.value = transaction;
  closeDetails();
  showForm.value = true;
}

async function onDelete(id) {
  if (confirm('Are you sure you want to delete this transaction?')) {
    const success = await transactionStore.deleteTransaction(id);
    if (success) {
      closeDetails();
      alert('Transaction deleted successfully');
    }
  }
}

onMounted(() => {
  transactionStore.fetchTransactions();
});
</script>
