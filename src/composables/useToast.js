import { ref } from 'vue'

// Global toast state
const toasts = ref([])
let toastId = 0

export const useToast = () => {
  const showToast = (message, type = 'success', duration = 3000) => {
    const id = toastId++
    const toast = { id, message, type }
    toasts.value.push(toast)

    if (duration > 0) {
      setTimeout(() => {
        toasts.value = toasts.value.filter(t => t.id !== id)
      }, duration)
    }

    return id
  }

  const success = (message, duration = 3000) => {
    return showToast(message, 'success', duration)
  }

  const error = (message, duration = 5000) => {
    return showToast(message, 'error', duration)
  }

  const warning = (message, duration = 4000) => {
    return showToast(message, 'warning', duration)
  }

  const info = (message, duration = 3000) => {
    return showToast(message, 'info', duration)
  }

  const remove = (id) => {
    toasts.value = toasts.value.filter(t => t.id !== id)
  }

  const clear = () => {
    toasts.value = []
  }

  return {
    toasts,
    showToast,
    success,
    error,
    warning,
    info,
    remove,
    clear,
  }
}
