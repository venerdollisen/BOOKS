<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Show layout only for authenticated routes -->
    <template v-if="showLayout">
      <Sidebar :is-open="sidebarOpen" @close="sidebarOpen = false" />
      <div :class="['lg:pl-64 transition-all duration-300', sidebarOpen && 'pl-64']">
        <Navbar @toggle-sidebar="sidebarOpen = !sidebarOpen" />
        <main class="p-6">
          <router-view v-slot="{ Component }">
            <transition name="fade" mode="out-in">
              <component :is="Component" />
            </transition>
          </router-view>
        </main>
      </div>
    </template>

    <!-- Show only router view for login page -->
    <template v-else>
      <router-view v-slot="{ Component }">
        <transition name="fade" mode="out-in">
          <component :is="Component" />
        </transition>
      </router-view>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import Sidebar from './components/Layout/Sidebar.vue'
import Navbar from './components/Layout/Navbar.vue'

const route = useRoute()
const authStore = useAuthStore()
const sidebarOpen = ref(false)

// Show layout for all routes except login
const showLayout = computed(() => {
  return route.name !== 'login'
})

// Initialize auth on app mount
onMounted(async () => {
  await authStore.initAuth()
})
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
