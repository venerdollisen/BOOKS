<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 overflow-y-auto py-8">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-3xl p-6">
      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-900">
          {{ isEditing ? 'Edit Transaction' : 'New Transaction' }}
        </h2>
        <button
          @click="$emit('close')"
          class="text-gray-400 hover:text-gray-600 transition"
        >
          <span class="text-2xl">&times;</span>
        </button>
      </div>

      <!-- Form -->
      <form @submit.prevent="submitForm" class="space-y-6">
        <!-- Reference & Type Row -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Reference <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.reference"
              type="text"
              :disabled="isEditing"
              placeholder="TXN-2025-001"
              class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              :class="{ 'bg-gray-100 cursor-not-allowed': isEditing }"
            />
            <span v-if="errors.reference" class="text-sm text-red-500 mt-1">{{ errors.reference[0] }}</span>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Type <span class="text-red-500">*</span>
            </label>
            <select
              v-model="form.type"
              class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">Select Type</option>
              <option value="receipt">Receipt (Income)</option>
              <option value="payment">Payment (Expense)</option>
              <option value="journal">Journal Entry</option>
              <option value="transfer">Transfer</option>
            </select>
            <span v-if="errors.type" class="text-sm text-red-500 mt-1">{{ errors.type[0] }}</span>
          </div>
        </div>

        <!-- Transaction Date & Amount Row -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Transaction Date <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.transaction_date"
              type="date"
              class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
            <span v-if="errors.transaction_date" class="text-sm text-red-500 mt-1">{{ errors.transaction_date[0] }}</span>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Total Amount <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.amount"
              type="number"
              step="0.01"
              min="0"
              placeholder="0.00"
              class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
            <span v-if="errors.amount" class="text-sm text-red-500 mt-1">{{ errors.amount[0] }}</span>
          </div>
        </div>

        <!-- Description -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
          <input
            v-model="form.description"
            type="text"
            placeholder="Enter transaction description"
            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
          <span v-if="errors.description" class="text-sm text-red-500 mt-1">{{ errors.description[0] }}</span>
        </div>

        <!-- Status -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Status <span class="text-red-500">*</span>
          </label>
          <select
            v-model="form.status"
            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="draft">Draft</option>
            <option value="pending">Pending Approval</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
          </select>
          <span v-if="errors.status" class="text-sm text-red-500 mt-1">{{ errors.status[0] }}</span>
        </div>

        <!-- Notes -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
          <textarea
            v-model="form.notes"
            rows="3"
            placeholder="Additional notes or comments"
            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          ></textarea>
        </div>

        <!-- Allocation Section -->
        <div class="border-t pt-6">
          <div class="mb-4 flex items-center gap-2">
            <h3 class="text-sm font-semibold text-gray-900">Allocations (Optional)</h3>
            <span
              v-if="isTransactionAllocationComplete"
              class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded"
            >
              ✓ Complete
            </span>
            <span
              v-else-if="isTransactionAllocationPartial"
              class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded"
            >
              ⚠ Incomplete
            </span>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Department -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Department</label>
              <select
                v-model="form.department_id"
                class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Select Department (optional)</option>
                <option
                  v-for="dept in departments"
                  :key="dept.id"
                  :value="dept.id"
                >
                  {{ dept.code }} - {{ dept.name }}
                </option>
              </select>
              <p class="text-xs text-gray-500 mt-1">Allocate to organizational department</p>
            </div>

            <!-- Project -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Project</label>
              <select
                v-model="form.project_id"
                class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Select Project (optional)</option>
                <option
                  v-for="proj in projects"
                  :key="proj.id"
                  :value="proj.id"
                >
                  {{ proj.name }}
                </option>
              </select>
              <p class="text-xs text-gray-500 mt-1">Allocate to specific project</p>
            </div>

            <!-- Subsidiary Account (Cost Center) -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Cost Center / Branch</label>
              <select
                v-model="form.subsidiary_account_id"
                class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Select Cost Center (optional)</option>
                <option
                  v-for="sub in subsidiaryAccounts"
                  :key="sub.id"
                  :value="sub.id"
                >
                  {{ sub.code }} - {{ sub.name }}
                </option>
              </select>
              <p class="text-xs text-gray-500 mt-1">Allocate to cost center or branch</p>
            </div>
          </div>
        </div>

        <!-- Line Items Section -->
        <div class="border-t pt-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Line Items</h3>
            <button
              type="button"
              @click="addLineItem"
              class="bg-[#06275c] text-white px-3 py-2 rounded-md hover:bg-[#051f47] transition text-sm"
            >
              + Add Line Item
            </button>
          </div>

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
              <span
                v-else
                class="ml-4 text-red-600 font-semibold"
              >
                ✗ Not Balanced (Difference: {{ formatCurrency(Math.abs(totalDebits - totalCredits)) }})
              </span>
            </p>
          </div>

          <!-- Line Items Table -->
          <div class="overflow-x-auto border border-gray-200 rounded-md">
            <table class="w-full text-sm">
              <thead class="bg-gray-200">
                <tr>
                  <th class="px-3 py-2 text-left font-semibold">Account</th>
                  <th class="px-3 py-2 text-left font-semibold">Subsidiary</th>
                  <th class="px-3 py-2 text-left font-semibold">Department</th>
                  <th class="px-3 py-2 text-left font-semibold">Project</th>
                  <th class="px-3 py-2 text-center font-semibold">Debit</th>
                  <th class="px-3 py-2 text-center font-semibold">Credit</th>
                  <th class="px-3 py-2 text-center font-semibold">Status</th>
                  <th class="px-3 py-2 text-center font-semibold">Action</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(item, index) in form.items"
                  :key="index"
                  class="border-t border-gray-200 hover:bg-gray-50"
                >
                  <td class="px-3 py-2">
                    <select
                      v-model="item.account_id"
                      class="w-full px-2 py-2 text-sm border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                      <option value="">Select Account</option>
                      <option
                        v-for="account in accounts"
                        :key="account.id"
                        :value="account.id"
                      >
                        {{ account.code }} - {{ account.name }}
                      </option>
                    </select>
                  </td>
                  <td class="px-3 py-2">
                    <select
                      v-model="item.subsidiary_account_id"
                      class="w-full px-2 py-1 text-xs border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                      <option value="">None</option>
                      <option
                        v-for="sub in subsidiaryAccounts"
                        :key="sub.id"
                        :value="sub.id"
                      >
                        {{ sub.code }}
                      </option>
                    </select>
                  </td>
                  <td class="px-3 py-2">
                    <select
                      v-model="item.department_id"
                      class="w-full px-2 py-1 text-xs border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                      <option value="">None</option>
                      <option
                        v-for="dept in departments"
                        :key="dept.id"
                        :value="dept.id"
                      >
                        {{ dept.code }}
                      </option>
                    </select>
                  </td>
                  <td class="px-3 py-2">
                    <select
                      v-model="item.project_id"
                      class="w-full px-2 py-1 text-xs border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                      <option value="">None</option>
                      <option
                        v-for="proj in projects"
                        :key="proj.id"
                        :value="proj.id"
                      >
                        {{ proj.name }}
                      </option>
                    </select>
                  </td>
                  <td class="px-3 py-2">
                    <input
                      :value="item.type === 'debit' ? item.amount : 0"
                      @input="item.type = 'debit'; item.amount = $event.target.value"
                      type="number"
                      step="0.01"
                      min="0"
                      class="w-full px-2 py-2 text-sm border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-right"
                    />
                  </td>
                  <td class="px-3 py-2">
                    <input
                      :value="item.type === 'credit' ? item.amount : 0"
                      @input="item.type = 'credit'; item.amount = $event.target.value"
                      type="number"
                      step="0.01"
                      min="0"
                      class="w-full px-2 py-2 text-sm border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-right"
                    />
                  </td>
                  <td class="px-3 py-2 text-center">
                    <span v-if="isLineItemAllocationComplete(item)" class="text-green-600 font-bold">✓</span>
                    <span v-else-if="isLineItemAllocationPartial(item)" class="text-red-600 font-bold">⚠</span>
                  </td>
                  <td class="px-3 py-2 text-center">
                    <button
                      type="button"
                      @click="removeLineItem(index)"
                      class="bg-[#06275c] text-white px-2 py-1 rounded hover:bg-[#051f47] transition text-xs"
                    >
                      Remove
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <span v-if="errors['items.0']" class="text-sm text-red-500">{{ errors['items.0'][0] }}</span>
        </div>

        <!-- Error Messages -->
        <div
          v-if="Object.keys(errors).length > 0 && !errors['items.0']"
          class="bg-red-50 border border-red-200 rounded-md p-3"
        >
          <p class="text-sm text-red-700">
            <strong>Validation Error:</strong> Please check all required fields and try again.
          </p>
        </div>

        <!-- Form Actions -->
        <div class="flex gap-3 justify-end border-t pt-6">
          <button
            type="button"
            @click="$emit('close')"
            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="loading || !isBalanced"
            class="px-4 py-2 bg-[#06275c] text-white rounded-md hover:bg-[#051f47] transition disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ loading ? 'Saving...' : (isEditing ? 'Update Transaction' : 'Create Transaction') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useTransactionStore } from '@/stores/transactions';
import { useDepartmentStore } from '@/stores/departments';
import { useProjectStore } from '@/stores/projects';
import { useSubsidiaryAccountStore } from '@/stores/subsidiaryAccounts';
import { useToast } from '@/composables/useToast';
import api from '@/config/api';

const props = defineProps({
  transaction: {
    type: Object,
    default: null,
  },
});

const emit = defineEmits(['close', 'saved']);

const transactionStore = useTransactionStore();
const departmentStore = useDepartmentStore();
const projectStore = useProjectStore();
const subsidiaryAccountStore = useSubsidiaryAccountStore();
const { success, error: showError } = useToast();

const loading = ref(false);
const errors = ref({});
const accounts = ref([]);
const departments = ref([]);
const projects = ref([]);
const subsidiaryAccounts = ref([]);

const isEditing = computed(() => !!props.transaction?.id);

const form = reactive({
  reference: '',
  description: '',
  transaction_date: new Date().toISOString().split('T')[0],
  type: '',
  status: 'draft',
  amount: 0,
  notes: '',
  department_id: '',
  project_id: '',
  subsidiary_account_id: '',
  items: [
    { account_id: '', type: 'debit', amount: 0, description: '', department_id: '', project_id: '', subsidiary_account_id: '' },
    { account_id: '', type: 'credit', amount: 0, description: '', department_id: '', project_id: '', subsidiary_account_id: '' },
  ],
});

const totalDebits = computed(() => {
  return form.items
    .filter(item => item.type === 'debit')
    .reduce((sum, item) => sum + (parseFloat(item.amount) || 0), 0);
});

const totalCredits = computed(() => {
  return form.items
    .filter(item => item.type === 'credit')
    .reduce((sum, item) => sum + (parseFloat(item.amount) || 0), 0);
});

const isBalanced = computed(() => {
  return form.items.length > 0 && Math.abs(totalDebits.value - totalCredits.value) < 0.01;
});

// Transaction-level allocation status
const isTransactionAllocationComplete = computed(() => {
  return form.department_id && form.project_id && form.subsidiary_account_id;
});

const isTransactionAllocationPartial = computed(() => {
  const hasAny = form.department_id || form.project_id || form.subsidiary_account_id;
  return hasAny && !isTransactionAllocationComplete.value;
});

function formatCurrency(value) {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(value);
}

function addLineItem() {
  form.items.push({
    account_id: '',
    type: 'debit',
    amount: 0,
    description: '',
    department_id: '',
    project_id: '',
    subsidiary_account_id: '',
  });
}

function removeLineItem(index) {
  if (form.items.length > 1) {
    form.items.splice(index, 1);
  }
}

async function fetchAccounts() {
  try {
    const response = await api.get('/accounts', { params: { per_page: 100 } });
    if (response.data.success) {
      accounts.value = response.data.data;
    }
  } catch (err) {
    console.error('Error fetching accounts:', err);
  }
}

async function fetchAllocations() {
  try {
    // Fetch departments
    if (departmentStore.departments.length === 0) {
      await departmentStore.fetchDepartments();
    }
    departments.value = departmentStore.departments;

    // Fetch projects
    if (projectStore.projects.length === 0) {
      await projectStore.fetchProjects();
    }
    projects.value = projectStore.projects;

    // Fetch subsidiary accounts
    if (subsidiaryAccountStore.subsidiaryAccounts.length === 0) {
      await subsidiaryAccountStore.fetchSubsidiaryAccounts();
    }
    subsidiaryAccounts.value = subsidiaryAccountStore.subsidiaryAccounts;
  } catch (err) {
    console.error('Error fetching allocations:', err);
    showError('Failed to load allocation options');
  }
}

function populateForm() {
  if (props.transaction) {
    form.reference = props.transaction.reference;
    form.description = props.transaction.description || '';
    form.transaction_date = props.transaction.transaction_date;
    form.type = props.transaction.type;
    form.status = props.transaction.status;
    form.amount = props.transaction.amount;
    form.notes = props.transaction.notes || '';
    form.department_id = props.transaction.department_id || '';
    form.project_id = props.transaction.project_id || '';
    form.subsidiary_account_id = props.transaction.subsidiary_account_id || '';

    // Populate items
    form.items = props.transaction.items.map(item => ({
      account_id: item.account_id,
      type: item.type,
      amount: item.amount,
      description: item.description || '',
      department_id: item.department_id || '',
      project_id: item.project_id || '',
      subsidiary_account_id: item.subsidiary_account_id || '',
    }));
  }
}

/**
 * Validate allocations: if any allocation field is filled, all must be filled
 * Returns true if valid, false if invalid
 */
function validateAllocations() {
  // Check transaction-level allocations
  const transHasAnyAllocation = form.department_id || form.project_id || form.subsidiary_account_id;
  const transAllocationComplete = form.department_id && form.project_id && form.subsidiary_account_id;
  
  if (transHasAnyAllocation && !transAllocationComplete) {
    showError('Transaction allocation incomplete: Please fill all three fields (Department, Project, Cost Center) or leave them all empty');
    return false;
  }

  // Check line-item allocations
  for (let i = 0; i < form.items.length; i++) {
    const item = form.items[i];
    const hasAnyAllocation = item.department_id || item.project_id || item.subsidiary_account_id;
    const allocationComplete = item.department_id && item.project_id && item.subsidiary_account_id;
    
    if (hasAnyAllocation && !allocationComplete) {
      showError(`Line ${i + 1} allocation incomplete: Please fill all three fields (Department, Project, Cost Center) or leave them all empty`);
      return false;
    }
  }

  return true;
}

function isLineItemAllocationComplete(item) {
  return item.department_id && item.project_id && item.subsidiary_account_id;
}

function isLineItemAllocationPartial(item) {
  const hasAny = item.department_id || item.project_id || item.subsidiary_account_id;
  return hasAny && !isLineItemAllocationComplete(item);
}

async function submitForm() {
  loading.value = true;
  errors.value = {};

  // Validate allocations first
  if (!validateAllocations()) {
    loading.value = false;
    return;
  }

  try {
    const data = {
      reference: form.reference,
      description: form.description,
      transaction_date: form.transaction_date,
      type: form.type,
      status: form.status,
      amount: parseFloat(form.amount),
      notes: form.notes,
      department_id: form.department_id ? parseInt(form.department_id) : null,
      project_id: form.project_id ? parseInt(form.project_id) : null,
      subsidiary_account_id: form.subsidiary_account_id ? parseInt(form.subsidiary_account_id) : null,
      items: form.items.map(item => ({
        account_id: parseInt(item.account_id),
        type: item.type,
        amount: parseFloat(item.amount),
        description: item.description,
        department_id: item.department_id ? parseInt(item.department_id) : null,
        project_id: item.project_id ? parseInt(item.project_id) : null,
        subsidiary_account_id: item.subsidiary_account_id ? parseInt(item.subsidiary_account_id) : null,
      })),
    };

    let result;
    if (isEditing.value) {
      result = await transactionStore.updateTransaction(props.transaction.id, data);
      if (result) {
        success('Transaction updated successfully!');
      }
    } else {
      result = await transactionStore.createTransaction(data);
      if (result) {
        success('Transaction created successfully!');
      }
    }

    if (result) {
      emit('saved', result);
      emit('close');
    } else if (transactionStore.error) {
      // Check if it's a validation error
      const errorMsg = transactionStore.error;
      if (errorMsg.includes('Debits must equal credits')) {
        errors.value.items = [errorMsg];
      } else {
        errors.value.general = [errorMsg];
        showError(errorMsg);
      }
    }
  } catch (err) {
    if (err.response?.data?.errors) {
      errors.value = err.response.data.errors;
      showError('Please check the form for errors');
    } else {
      errors.value.general = [err.message];
      showError(err.message || 'An error occurred');
    }
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  fetchAccounts();
  fetchAllocations();
  populateForm();
});
</script>

<style scoped>
input:disabled,
textarea:disabled {
  background-color: #f3f4f6;
  cursor: not-allowed;
}
</style>
