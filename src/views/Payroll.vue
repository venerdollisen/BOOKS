<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h2 class="text-2xl font-bold text-gray-900">Payroll / Employees</h2>
        <p class="text-sm text-gray-500 mt-1">Manage employee salaries and payslips</p>
      </div>
      <button @click="showModal = true" class="btn btn-primary">
        <PlusIcon class="h-5 w-5 inline mr-2" />
        Add Employee
      </button>
    </div>

    <!-- Employees Table -->
    <div class="card">
      <div class="overflow-x-auto">
        <table class="table">
          <thead>
            <tr>
              <th>Employee ID</th>
              <th>Name</th>
              <th>Position</th>
              <th>Base Salary</th>
              <th>Deductions</th>
              <th>Net Salary</th>
              <th>Status</th>
              <th class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="employee in employees" :key="employee.id">
              <td class="font-mono text-sm">{{ employee.employee_id }}</td>
              <td class="font-medium">{{ employee.name }}</td>
              <td>{{ employee.position }}</td>
              <td>{{ formatCurrency(employee.base_salary) }}</td>
              <td>{{ formatCurrency(employee.total_deductions) }}</td>
              <td class="font-medium">{{ formatCurrency(employee.net_salary) }}</td>
              <td>
                <span
                  :class="[
                    'px-2 py-1 text-xs font-medium rounded-full',
                    employee.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800',
                  ]"
                >
                  {{ employee.status }}
                </span>
              </td>
              <td class="text-right">
                <button
                  @click="generatePayslip(employee)"
                  class="text-primary-600 hover:text-primary-700 mr-3"
                >
                  Payslip
                </button>
                <button
                  @click="editEmployee(employee)"
                  class="text-primary-600 hover:text-primary-700 mr-3"
                >
                  Edit
                </button>
              </td>
            </tr>
            <tr v-if="employees.length === 0">
              <td colspan="8" class="text-center py-8 text-gray-500">No employees found</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { payrollApi } from '@/services/api'
import { PlusIcon } from '@heroicons/vue/24/outline'

const employees = ref([])
const showModal = ref(false)

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(amount || 0)
}

const loadEmployees = async () => {
  try {
    const response = await payrollApi.getEmployees()
    employees.value = response.data || []
  } catch (error) {
    console.error('Error loading employees:', error)
    employees.value = []
  }
}

const editEmployee = (employee) => {
  showModal.value = true
}

const generatePayslip = async (employee) => {
  try {
    const period = prompt('Enter pay period (e.g., 2024-01):')
    if (period) {
      const response = await payrollApi.generatePayslip(employee.id, period)
      const blob = new Blob([response.data], { type: 'application/pdf' })
      const url = window.URL.createObjectURL(blob)
      const link = document.createElement('a')
      link.href = url
      link.download = `payslip-${employee.name}-${period}.pdf`
      link.click()
    }
  } catch (error) {
    console.error('Error generating payslip:', error)
  }
}

onMounted(() => {
  loadEmployees()
})
</script>
