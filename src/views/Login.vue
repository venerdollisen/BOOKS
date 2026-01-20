<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-50 via-gray-50 to-blue-50 px-4 relative overflow-hidden">
    <!-- Subtle background decorative circles -->
    <div class="absolute top-0 right-0 w-72 h-72 bg-blue-100 rounded-full opacity-10 -mr-36 -mt-36"></div>
    <div class="absolute bottom-0 left-0 w-72 h-72 bg-blue-100 rounded-full opacity-10 -ml-36 -mb-36"></div>
    
    <div class="max-w-md w-full relative z-10">
      <!-- Logo and Title -->
      <div class="text-center mb-10">
        <div class="inline-flex items-center justify-center h-20 w-20 bg-[#06275c] rounded-2xl shadow-xl mb-6 transform hover:scale-105 transition-transform duration-300">
          <span class="text-white font-bold text-4xl">B</span>
        </div>
        <h1 class="text-4xl font-bold text-gray-900 mb-2">Bookkeeping</h1>
        <p class="text-gray-600 text-lg">Professional Financial Management</p>
      </div>

      <!-- Login Card -->
      <div class="bg-white rounded-3xl shadow-2xl p-10 border border-gray-100">
        <form @submit.prevent="handleLogin" class="space-y-6">
          <!-- Email -->
          <div>
            <label for="email" class="block text-sm font-semibold text-gray-800 mb-2">Email Address</label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              autocomplete="email"
              placeholder="admin@example.com"
              class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-[#06275c] focus:outline-none bg-gray-50 focus:bg-white transition-all duration-200 text-gray-900 placeholder-gray-400"
              :class="{ 'border-red-500 bg-red-50': errors.email }"
            />
            <p v-if="errors.email" class="mt-2 text-sm text-red-600 font-medium">{{ errors.email }}</p>
          </div>

          <!-- Password -->
          <div>
            <label for="password" class="block text-sm font-semibold text-gray-800 mb-2">Password</label>
            <div class="relative">
              <input
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                required
                autocomplete="current-password"
                placeholder="Enter your password"
                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-[#06275c] focus:outline-none bg-gray-50 focus:bg-white transition-all duration-200 text-gray-900 placeholder-gray-400 pr-10"
                :class="{ 'border-red-500 bg-red-50': errors.password }"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-600 hover:text-[#06275c] transition-colors"
              >
                <EyeIcon v-if="!showPassword" class="h-5 w-5" />
                <EyeSlashIcon v-else class="h-5 w-5" />
              </button>
            </div>
            <p v-if="errors.password" class="mt-2 text-sm text-red-600 font-medium">{{ errors.password }}</p>
          </div>

          <!-- Remember Me & Forgot Password -->
          <div class="flex items-center justify-between pt-2">
            <label class="flex items-center cursor-pointer">
              <input
                v-model="form.remember"
                type="checkbox"
                class="h-4 w-4 text-[#06275c] bg-gray-100 border-gray-300 rounded focus:ring-2 focus:ring-[#06275c]"
              />
              <span class="ml-2 text-sm text-gray-700 font-medium">Remember me</span>
            </label>
            <a href="#" class="text-sm text-[#06275c] hover:text-[#051f47] font-semibold transition-colors">
              Forgot password?
            </a>
          </div>

          <!-- Error Message -->
          <div v-if="errorMessage" class="bg-red-50 border-l-4 border-red-500 rounded-lg p-4">
            <p class="text-sm text-red-800 font-medium">{{ errorMessage }}</p>
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="loading"
            class="w-full bg-gradient-to-r from-[#06275c] to-[#0a3a7f] text-white py-3 rounded-lg text-base font-bold shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 mt-8"
            :class="{ 'opacity-50 cursor-not-allowed transform scale-100': loading }"
          >
            <span v-if="loading" class="flex items-center justify-center">
              <svg
                class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
              >
                <circle
                  class="opacity-25"
                  cx="12"
                  cy="12"
                  r="10"
                  stroke="currentColor"
                  stroke-width="4"
                ></circle>
                <path
                  class="opacity-75"
                  fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                ></path>
              </svg>
              Signing in...
            </span>
            <span v-else>Sign In</span>
          </button>
        </form>

        <!-- Demo Credentials (for development) -->
        <div class="mt-8 pt-6 border-t border-gray-200">
          <p class="text-xs text-center text-gray-600 mb-2 font-semibold">Demo Credentials</p>
          <div class="bg-blue-50 rounded-lg p-3 space-y-1">
            <p class="text-xs text-gray-700"><span class="font-bold">Email:</span> admin@example.com</p>
            <p class="text-xs text-gray-700"><span class="font-bold">Password:</span> 12345678</p>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <p class="mt-8 text-center text-sm text-gray-600">
        Having trouble?
        <a href="#" class="text-[#06275c] hover:text-[#051f47] font-semibold transition-colors">Contact Support</a>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { EyeIcon, EyeSlashIcon } from '@heroicons/vue/24/outline'

const router = useRouter()
const authStore = useAuthStore()

const form = reactive({
  email: '',
  password: '',
  remember: false,
})

const errors = reactive({
  email: '',
  password: '',
})

const errorMessage = ref('')
const loading = ref(false)
const showPassword = ref(false)

const validateForm = () => {
  errors.email = ''
  errors.password = ''
  let isValid = true

  if (!form.email) {
    errors.email = 'Email is required'
    isValid = false
  } else if (!/\S+@\S+\.\S+/.test(form.email)) {
    errors.email = 'Email is invalid'
    isValid = false
  }

  if (!form.password) {
    errors.password = 'Password is required'
    isValid = false
  } else if (form.password.length < 6) {
    errors.password = 'Password must be at least 6 characters'
    isValid = false
  }

  return isValid
}

const handleLogin = async () => {
  errorMessage.value = ''
  authStore.clearError()
  
  if (!validateForm()) {
    return
  }

  loading.value = true

  try {
    await authStore.login(form.email, form.password, form.remember)
    
    // Redirect to dashboard on success
    router.push('/')
  } catch (error) {
    console.error('Login error:', error)
    errorMessage.value = authStore.error || error.response?.data?.message || error.message || 'Login failed. Please try again.'
  } finally {
    loading.value = false
  }
}
</script>
