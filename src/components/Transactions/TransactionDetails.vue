<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 overflow-y-auto py-8">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl p-6">
      <!-- Header -->
      <div class="flex justify-between items-center mb-6 pb-4 border-b">
        <h2 class="text-2xl font-bold text-gray-900">Transaction Details</h2>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
          <span class="text-2xl">&times;</span>
        </button>
      </div>

      <!-- Transaction Info -->
      <div class="space-y-6">
        <!-- Header Info -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <p class="text-sm text-gray-600 mb-1">Reference</p>
            <p class="text-lg font-semibold text-gray-900">{{ transaction.reference }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-600 mb-1">Date</p>
            <p class="text-lg font-semibold text-gray-900">{{ formatDate(transaction.transaction_date) }}</p>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <p class="text-sm text-gray-600 mb-1">Type</p>
            <span :class="getTypeBadgeClass(transaction.type)" class="px-3 py-1 rounded-full text-sm font-semibold">
              {{ transaction.type }}
            </span>
          </div>
          <div>
            <p class="text-sm text-gray-600 mb-1">Status</p>
            <span :class="getStatusBadgeClass(transaction.status)" class="px-3 py-1 rounded-full text-sm font-semibold">
              {{ transaction.status }}
            </span>
          </div>
        </div>

        <!-- Description -->
        <div v-if="transaction.description">
          <p class="text-sm text-gray-600 mb-1">Description</p>
          <p class="text-gray-900">{{ transaction.description }}</p>
        </div>

        <!-- Amount -->
        <div>
          <p class="text-sm text-gray-600 mb-1">Total Amount</p>
          <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(transaction.amount) }}</p>
        </div>

        <!-- Line Items -->
        <div class="border-t pt-4">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Line Items</h3>

          <!-- Balance Check -->
          <div class="mb-4 p-3 bg-gray-50 rounded-md">
            <p class="text-sm text-gray-700">
              Total Debits: <strong class="text-gray-900">{{ formatCurrency(totalDebits) }}</strong> |
              Total Credits: <strong class="text-gray-900">{{ formatCurrency(totalCredits) }}</strong>
              <span
                v-if="isBalanced"
                class="ml-4 text-green-600 font-semibold"
              >
                ✓ Balanced
              </span>
            </p>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="bg-gray-100">
                <tr>
                  <th class="px-4 py-2 text-left text-gray-900 font-semibold">Account</th>
                  <th class="px-4 py-2 text-left text-gray-900 font-semibold">Type</th>
                  <th class="px-4 py-2 text-right text-gray-900 font-semibold">Amount</th>
                  <th class="px-4 py-2 text-left text-gray-900 font-semibold">Description</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr v-for="(item, idx) in transaction.items" :key="idx" class="hover:bg-gray-50">
                  <td class="px-4 py-3 text-gray-900">
                    <div>
                      <p class="font-semibold">{{ item.account?.code }}</p>
                      <p class="text-xs text-gray-600">{{ item.account?.name }}</p>
                    </div>
                  </td>
                  <td class="px-4 py-3">
                    <span
                      :class="{
                        'text-red-600 font-semibold': item.type === 'debit',
                        'text-green-600 font-semibold': item.type === 'credit',
                      }"
                    >
                      {{ item.type.toUpperCase() }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-right font-semibold text-gray-900">
                    {{ formatCurrency(item.amount) }}
                  </td>
                  <td class="px-4 py-3 text-gray-600">{{ item.description || '-' }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Notes -->
        <div v-if="transaction.notes" class="border-t pt-4">
          <p class="text-sm text-gray-600 mb-1">Notes</p>
          <p class="bg-gray-50 p-3 rounded-md text-gray-900 whitespace-pre-wrap">{{ transaction.notes }}</p>
        </div>

        <!-- User Info -->
        <div v-if="transaction.user" class="border-t pt-4 text-sm text-gray-600">
          <p>Created by: <strong>{{ transaction.user.name }}</strong></p>
          <p>Created on: <strong>{{ formatDateTime(transaction.created_at) }}</strong></p>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="mt-8 border-t pt-6 flex gap-3 justify-end">
        <!-- Edit Button (only for draft) -->
        <button
          v-if="transaction.status === 'draft'"
          @click="$emit('edit', transaction)"
          class="px-4 py-2 bg-[#06275c] text-white rounded-md hover:bg-[#051f47] transition"
        >
          Edit
        </button>

        <!-- Approve Button (only for pending) -->
        <button
          v-if="transaction.status === 'pending'"
          @click="$emit('approve', transaction.id)"
          class="px-4 py-2 bg-[#06275c] text-white rounded-md hover:bg-[#051f47] transition"
        >
          Approve
        </button>

        <!-- Reject Button (only for pending) -->
        <button
          v-if="transaction.status === 'pending'"
          @click="showRejectModal = true"
          class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition"
        >
          Reject
        </button>

        <!-- Delete Button (only for draft or rejected) -->
        <button
          v-if="transaction.status === 'draft' || transaction.status === 'rejected'"
          @click="$emit('delete', transaction.id)"
          class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition"
        >
          Delete
        </button>

        <!-- Close Button -->
        <button
          @click="$emit('close')"
          class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition"
        >
          Close
        </button>
      </div>

      <!-- Reject Reason Modal -->
      <div v-if="showRejectModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
          <h3 class="text-xl font-bold text-gray-900 mb-4">Reject Transaction</h3>
          <textarea
            v-model="rejectReason"
            placeholder="Enter rejection reason..."
            rows="4"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 mb-4"
          ></textarea>
          <div class="flex gap-3 justify-end">
            <button
              @click="showRejectModal = false"
              class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition"
            >
              Cancel
            </button>
            <button
              @click="submitReject"
              class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition"
            >
              Reject
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  transaction: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(['close', 'approve', 'reject', 'edit', 'delete']);

const showRejectModal = ref(false);
const rejectReason = ref('');

const totalDebits = computed(() => {
  return props.transaction.items
    .filter(item => item.type === 'debit')
    .reduce((sum, item) => sum + item.amount, 0);
});

const totalCredits = computed(() => {
  return props.transaction.items
    .filter(item => item.type === 'credit')
    .reduce((sum, item) => sum + item.amount, 0);
});

const isBalanced = computed(() => {
  return Math.abs(totalDebits.value - totalCredits.value) < 0.01;
});

function formatDate(date) {
  return new Intl.DateTimeFormat('en-US').format(new Date(date));
}

function formatDateTime(date) {
  return new Intl.DateTimeFormat('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  }).format(new Date(date));
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

function submitReject() {
  if (rejectReason.value.trim()) {
    emit('reject', props.transaction.id, rejectReason.value);
    rejectReason.value = '';
    showRejectModal.value = false;
  }
}
</script>
