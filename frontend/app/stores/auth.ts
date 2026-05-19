import { defineStore } from 'pinia'
import { ref } from 'vue'
import { authApi, setAuthToken } from '~/api/payments'
import { usePaymentsStore } from '~/stores/payments'

export const useAuthStore = defineStore('auth', () => {
  const token = ref<string | null>(null)

  const user = ref<{ name: string; email: string } | null>(null)
  const isAuthenticated = ref(false)

  const isAdmin = ref(false)
  const userName = ref('')

  async function login(email: string, password: string) {
    const response = await authApi.login(email, password)
    
    localStorage.setItem('authToken', response.data.token)

    token.value = response.data.token
    isAuthenticated.value = true
    isAdmin.value = response.data.is_admin
    userName.value = response.data.name

    setAuthToken(token.value)
    await fetchUser()
  }

  async function fetchUser() {
    const { data } = await authApi.getUser()
    user.value = data
    isAuthenticated.value = true
    isAdmin.value = data.is_admin
    userName.value = data.name  
  }

  async function logout() {
    await authApi.logout()
    
    token.value = null
    user.value = null
    isAuthenticated.value = false
    
    const paymentsStore = usePaymentsStore()
    paymentsStore.$reset()

    localStorage.removeItem('authToken')

    setAuthToken(null)
  }

  return { token, user,isAdmin,userName, isAuthenticated, login, fetchUser, logout }
})