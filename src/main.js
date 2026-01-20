import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import { useAuthStore } from './stores/auth'
import './style.css'

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(router)

// Initialize auth store before mounting
const authStore = useAuthStore()

// Wait for auth initialization before starting the app
authStore.initAuth().then(() => {
  app.mount('#app')
}).catch(() => {
  // Even if init fails, still mount the app (user will be redirected to login)
  app.mount('#app')
})
