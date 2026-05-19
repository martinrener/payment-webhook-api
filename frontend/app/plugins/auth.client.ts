import { setAuthToken } from '~/api/payments'
import { useAuthStore } from '~/stores/auth'

export default defineNuxtPlugin(async () => {
  const token = localStorage.getItem('authToken')
  if (token) {
    const authStore = useAuthStore()
    authStore.token = token
    setAuthToken(token)
    await authStore.fetchUser()
  }
})