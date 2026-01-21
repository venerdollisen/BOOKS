<template>
  <div class="space-y-6">
    <div>
      <h2 class="text-2xl font-bold text-gray-900">Settings</h2>
      <p class="text-sm text-gray-500 mt-1">Configure system preferences and company information</p>
    </div>

    <!-- Settings Tabs -->
    <div class="card">
      <nav class="flex space-x-1 border-b border-gray-200 mb-6" aria-label="Tabs">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          @click="activeTab = tab.id"
          :class="[
            'px-4 py-2 text-sm font-medium border-b-2 transition-colors duration-200',
            activeTab === tab.id
              ? 'border-primary-600 text-primary-600'
              : 'border-transparent text-gray-600 hover:text-gray-800',
          ]"
        >
          {{ tab.label }}
        </button>
      </nav>

      <!-- Company Profile -->
      <div v-if="activeTab === 'company'" class="space-y-4">
        <h3 class="text-lg font-semibold text-gray-900">Company Profile</h3>
        <form @submit.prevent="saveCompanyProfile" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="label">Company Name *</label>
              <input v-model="companyForm.name" type="text" required class="input" />
            </div>
            <div>
              <label class="label">Email</label>
              <input v-model="companyForm.email" type="email" class="input" />
            </div>
          </div>
          <div>
            <label class="label">Address</label>
            <textarea v-model="companyForm.address" rows="3" class="input"></textarea>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="label">Phone</label>
              <input v-model="companyForm.phone" type="tel" class="input" />
            </div>
            <div>
              <label class="label">Tax ID</label>
              <input v-model="companyForm.tax_id" type="text" class="input" />
            </div>
          </div>
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
      </div>

      <!-- Tax Rates -->
      <div v-if="activeTab === 'tax'" class="space-y-4">
        <h3 class="text-lg font-semibold text-gray-900">Tax Rates</h3>
        <div class="space-y-3">
          <div v-for="rate in taxRates" :key="rate.id" class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
            <div>
              <p class="font-medium">{{ rate.name }}</p>
              <p class="text-sm text-gray-500">{{ rate.description }}</p>
            </div>
            <div class="flex items-center space-x-3">
              <input v-model.number="rate.rate" type="number" step="0.01" class="input w-24" />
              <span class="text-gray-600">%</span>
            </div>
          </div>
          <button @click="saveTaxRates" class="btn btn-primary">Save Tax Rates</button>
        </div>
      </div>

      <!-- Users & Roles -->
      <div v-if="activeTab === 'users'" class="space-y-4">
        <div class="flex justify-between items-center">
          <h3 class="text-lg font-semibold text-gray-900">Users & Roles</h3>
          <button @click="showUserModal = true" class="btn btn-primary">
            <PlusIcon class="h-5 w-5 inline mr-2" />
            Add User
          </button>
        </div>
        <div class="overflow-x-auto">
          <table class="table">
            <thead>
              <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th class="text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="user in users" :key="user.id">
                <td class="font-medium">{{ user.name }}</td>
                <td>{{ user.email }}</td>
                <td>
                  <span
                    :class="[
                      'px-2 py-1 text-xs font-medium rounded-full',
                      getRoleClass(user.role),
                    ]"
                  >
                    {{ user.role }}
                  </span>
                </td>
                <td>
                  <span
                    :class="[
                      'px-2 py-1 text-xs font-medium rounded-full',
                      user.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800',
                    ]"
                  >
                    {{ user.status }}
                  </span>
                </td>
                <td class="text-right">
                  <button class="text-primary-600 hover:text-primary-700 mr-3">Edit</button>
                  <button class="text-red-600 hover:text-red-700">Delete</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- GL Accounts -->
      <div v-if="activeTab === 'gl-accounts'" class="space-y-4">
        <GLAccountSettings />
      </div>

      <!-- Preferences -->
      <div v-if="activeTab === 'preferences'" class="space-y-4">
        <h3 class="text-lg font-semibold text-gray-900">System Preferences</h3>
        <form @submit.prevent="savePreferences" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="label">Date Format</label>
              <select v-model="preferencesForm.date_format" class="input">
                <option value="Y-m-d">YYYY-MM-DD</option>
                <option value="m/d/Y">MM/DD/YYYY</option>
                <option value="d/m/Y">DD/MM/YYYY</option>
              </select>
            </div>
            <div>
              <label class="label">Default Currency</label>
              <select v-model="preferencesForm.currency" class="input">
                <option value="USD">USD - US Dollar</option>
                <option value="EUR">EUR - Euro</option>
                <option value="GBP">GBP - British Pound</option>
              </select>
            </div>
          </div>
          <button type="submit" class="btn btn-primary">Save Preferences</button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import GLAccountSettings from '@/components/Settings/GLAccountSettings.vue'
import { PlusIcon } from '@heroicons/vue/24/outline'

const activeTab = ref('company')
const tabs = [
  { id: 'company', label: 'Company Profile' },
  { id: 'gl-accounts', label: 'GL Accounts' },
  { id: 'tax', label: 'Tax Rates' },
  { id: 'users', label: 'Users & Roles' },
  { id: 'preferences', label: 'Preferences' },
]

const companyForm = ref({
  name: '',
  email: '',
  address: '',
  phone: '',
  tax_id: '',
})

const taxRates = ref([])
const users = ref([])
const showUserModal = ref(false)

const preferencesForm = ref({
  date_format: 'Y-m-d',
  currency: 'USD',
})

const getRoleClass = (role) => {
  const classes = {
    admin: 'bg-red-100 text-red-800',
    accountant: 'bg-blue-100 text-blue-800',
    viewer: 'bg-gray-100 text-gray-800',
  }
  return classes[role] || 'bg-gray-100 text-gray-800'
}

const loadSettings = async () => {
  try {
    const [company, tax, usersData, preferences] = await Promise.all([
      settingsApi.getCompanyProfile(),
      settingsApi.getTaxRates(),
      settingsApi.getUsers(),
      settingsApi.getPreferences(),
    ])

    companyForm.value = company.data || companyForm.value
    taxRates.value = tax.data || []
    users.value = usersData.data || []
    preferencesForm.value = preferences.data || preferencesForm.value
  } catch (error) {
    console.error('Error loading settings:', error)
  }
}

const saveCompanyProfile = async () => {
  try {
    // TODO: Implement company profile endpoint
    alert('Company profile update not yet implemented')
  } catch (error) {
    console.error('Error saving company profile:', error)
  }
}

const saveTaxRates = async () => {
  try {
    // TODO: Implement tax rates endpoint
    alert('Tax rates update not yet implemented')
  } catch (error) {
    console.error('Error saving tax rates:', error)
  }
}

const savePreferences = async () => {
  try {
    // TODO: Implement preferences endpoint
    alert('Preferences update not yet implemented')
  } catch (error) {
    console.error('Error saving preferences:', error)
  }
}

onMounted(() => {
  loadSettings()
})
</script>
