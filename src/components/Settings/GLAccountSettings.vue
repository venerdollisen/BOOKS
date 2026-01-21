<template>
  <div class="space-y-6">
    <div>
      <h2 class="text-2xl font-bold text-gray-900">GL Account Settings</h2>
      <p class="text-sm text-gray-500 mt-1">Configure which accounts to use for Accounts Receivable and Payable</p>
    </div>

    <div class="card">
      <form @submit.prevent="saveSettings" class="space-y-6">
        <!-- Error Message -->
        <div v-if="errorMessage" class="bg-red-50 border border-red-200 rounded-lg p-4">
          <p class="text-sm text-red-700">{{ errorMessage }}</p>
        </div>

        <!-- Success Message -->
        <div v-if="successMessage" class="bg-green-50 border border-green-200 rounded-lg p-4">
          <p class="text-sm text-green-700">{{ successMessage }}</p>
        </div>

        <!-- Loading Indicator -->
        <div v-if="loading && !accounts.length" class="flex justify-center py-8">
          <div class="animate-spin">⏳</div>
          <span class="ml-2">Loading accounts...</span>
        </div>

        <!-- AR Account Selection -->
        <div>
          <label class="label">Accounts Receivable Account *</label>
          <p class="text-sm text-gray-500 mb-2">
            This account will be debited when invoices are finalized
          </p>
          <select v-model="form.ar_account_id" class="input" required :disabled="loading">
            <option value="">Select an account</option>
            <option v-for="acc in assetAccounts" :key="acc.id" :value="acc.id">
              {{ acc.name }} ({{ acc.code }})
            </option>
          </select>
          <p v-if="form.ar_account_id && selectedArAccount" class="text-xs text-gray-600 mt-2">
            Selected: {{ selectedArAccount?.name }}
          </p>
        </div>

        <!-- AP Account Selection -->
        <div>
          <label class="label">Accounts Payable Account *</label>
          <p class="text-sm text-gray-500 mb-2">
            This account will be credited when bills are finalized
          </p>
          <select v-model="form.ap_account_id" class="input" required :disabled="loading">
            <option value="">Select an account</option>
            <option v-for="acc in liabilityAccounts" :key="acc.id" :value="acc.id">
              {{ acc.name }} ({{ acc.code }})
            </option>
          </select>
          <p v-if="form.ap_account_id && selectedApAccount" class="text-xs text-gray-600 mt-2">
            Selected: {{ selectedApAccount?.name }}
          </p>
        </div>

        <!-- Actions -->
        <div class="flex gap-2">
          <button
            type="submit"
            :disabled="loading || !accounts.length"
            class="btn btn-primary flex items-center gap-2"
          >
            <span v-if="!loading">💾</span>
            <span v-if="loading" class="inline-block animate-spin">⏳</span>
            {{ loading ? 'Saving...' : 'Save Settings' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useToast } from '@/composables/useToast'

const accounts = ref([])
const loading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const form = ref({
  ar_account_id: '',
  ap_account_id: '',
})

const { success, error: showError } = useToast()

const assetAccounts = computed(() => {
  return accounts.value.filter(acc => {
    const type = acc.type || acc.account_type || ''
    return type.toLowerCase() === 'asset'
  })
})

const liabilityAccounts = computed(() => {
  return accounts.value.filter(acc => {
    const type = acc.type || acc.account_type || ''
    return type.toLowerCase() === 'liability'
  })
})

const selectedArAccount = computed(() => {
  return accounts.value.find(acc => acc.id == form.value.ar_account_id)
})

const selectedApAccount = computed(() => {
  return accounts.value.find(acc => acc.id == form.value.ap_account_id)
})

const loadSettings = async () => {
  loading.value = true
  errorMessage.value = ''
  
  try {
    const token = localStorage.getItem('auth_token')
    if (!token) {
      throw new Error('No authentication token found')
    }

    // Load accounts - get all accounts without pagination
    const accountsResponse = await fetch('/api/accounts?per_page=1000', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
      }
    })

    if (!accountsResponse.ok) {
      throw new Error('Failed to load accounts')
    }

    const accountsData = await accountsResponse.json()
    accounts.value = accountsData.data || []

    if (accounts.value.length === 0) {
      errorMessage.value = 'No accounts found. Please create accounts in the Chart of Accounts first.'
      return
    }

    // Load current settings
    const settingsResponse = await fetch('/api/settings/gl-accounts', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
      }
    })

    if (settingsResponse.ok) {
      const data = await settingsResponse.json()
      if (data.data?.ar_account_id) {
        form.value.ar_account_id = String(data.data.ar_account_id)
      }
      if (data.data?.ap_account_id) {
        form.value.ap_account_id = String(data.data.ap_account_id)
      }
    }
  } catch (err) {
    console.error('Error loading settings:', err)
    errorMessage.value = 'Error loading settings: ' + err.message
  } finally {
    loading.value = false
  }
}

const saveSettings = async () => {
  errorMessage.value = ''
  successMessage.value = ''

  if (!form.value.ar_account_id || !form.value.ap_account_id) {
    errorMessage.value = 'Both AR and AP accounts must be selected'
    return
  }

  loading.value = true

  try {
    const token = localStorage.getItem('auth_token')
    if (!token) {
      throw new Error('No authentication token found')
    }

    const response = await fetch('/api/settings/gl-accounts', {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        ar_account_id: parseInt(form.value.ar_account_id),
        ap_account_id: parseInt(form.value.ap_account_id),
      })
    })

    const data = await response.json()

    if (!response.ok) {
      throw new Error(data.message || data.error || 'Failed to save settings')
    }

    successMessage.value = 'GL account settings saved successfully!'
    success('Settings saved')
  } catch (err) {
    console.error('Error saving settings:', err)
    errorMessage.value = err.message || 'Error saving settings'
    showError(errorMessage.value)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadSettings()
})
</script>
