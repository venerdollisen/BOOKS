<template>
  <nav class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-40">
    <div class="px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between h-16">
        <div class="flex items-center">
          <button
            @click="$emit('toggle-sidebar')"
            class="lg:hidden p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary-500"
          >
            <Bars3Icon class="h-6 w-6" />
          </button>
          <h1 class="ml-4 lg:ml-0 text-xl font-semibold text-gray-900">
            {{ $route.meta.title || 'Bookkeeping System' }}
          </h1>
        </div>
        <div class="flex items-center space-x-4">
          <button class="p-2 text-gray-400 hover:text-gray-500 relative">
            <BellIcon class="h-6 w-6" />
            <span
              v-if="notificationsCount > 0"
              class="absolute top-1 right-1 h-2 w-2 bg-red-500 rounded-full"
            ></span>
          </button>
          <div class="flex items-center space-x-3">
            <div class="text-right hidden sm:block">
              <p class="text-sm font-medium text-gray-900">{{ user?.name || 'Admin User' }}</p>
              <p class="text-xs text-gray-500">{{ user?.email || 'admin@example.com' }}</p>
            </div>
            <div class="relative group">
              <div class="h-10 w-10 rounded-full bg-primary-600 flex items-center justify-center text-white font-medium cursor-pointer">
                {{ userInitials }}
              </div>
              <!-- Dropdown Menu -->
              <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile</a>
                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Settings</a>
                <hr class="my-1" />
                <button
                  @click="handleLogout"
                  class="w-full text-left block px-4 py-2 text-sm text-red-600 hover:bg-gray-100"
                >
                  Sign Out
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { Bars3Icon, BellIcon } from '@heroicons/vue/24/outline'

defineEmits(['toggle-sidebar'])

const authStore = useAuthStore()
const notificationsCount = ref(3) // This would come from API/store

const user = computed(() => authStore.user)

const userInitials = computed(() => {
  if (user.value?.name) {
    const names = user.value.name.split(' ')
    if (names.length >= 2) {
      return (names[0][0] + names[1][0]).toUpperCase()
    }
    return names[0][0].toUpperCase()
  }
  return 'AU'
})

const handleLogout = () => {
  authStore.logout()
}
</script>
