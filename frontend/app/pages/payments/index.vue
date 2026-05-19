<template>
  <div class="min-h-screen bg-gray-100">
    <nav class="bg-white shadow-sm px-6 py-4 flex justify-between items-center">
      <h1 class="text-xl font-bold text-gray-800">Payments Dashboard</h1>
      <div class="flex items-center gap-4">
        <NuxtLink 
          v-if="authStore.isAdmin" 
          to="/metrics" 
          class="text-gray-600 hover:text-gray-900 font-medium text-sm transition"
        >
          Metrics
        </NuxtLink>
        <div v-if="authStore.isAdmin" class="h-4 w-px bg-gray-300"></div>
        <button @click="handleLogout" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600 transition text-sm">
          Logout
        </button>
      </div>
    </nav>

    <div class="p-6">
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b">
          <h2 class="text-lg font-semibold text-gray-800 mb-3">Filters</h2>
          <div class="flex gap-3 items-center">
            <BaseInput v-model="filterEvent" placeholder="Event Type" />
            <BaseInput v-model="filterUserId" placeholder="User ID" />
            <BaseInput v-model="filterCurrency" placeholder="Currency" />
            <DateInput v-model="filterDateFrom" label="Date From" />
            <DateInput v-model="filterDateTo" label="Date To" />
            <button @click="applyFilters" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition text-sm font-medium">
              Filtros
            </button>
            <button @click="exportCsv" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition text-sm font-medium">
              Export CSV
            </button>
          </div>
        </div>
        <PaymentsTable 
          :titles="['ID', 'Evento', 'Monto', 'Moneda', 'Usuario', 'Último Evento']" 
          :payments="paymentsStore.payments" 
          :isAdmin="authStore.isAdmin"
          :total="paymentsStore.total" 
          :currentPage="paymentsStore.currentPage" 
          :lastPage="paymentsStore.lastPage" 
          @changePage="changePage" 
          @select="router.push(`/payments/${$event}`)"
          @refund="handleRefund"
        />
      </div>
    </div>

    <!-- Modal refund -->
    <div v-if="showRefundModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md mx-4">
        <div class="flex items-center gap-3 mb-4">
          <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
            <span class="text-red-600 text-lg">⚠</span>
          </div>
          <h3 class="text-lg font-semibold text-gray-800">Confirmar reembolso</h3>
        </div>
        <p class="text-gray-500 text-sm mb-2">Estás a punto de reembolsar el pago:</p>
        <p class="font-mono text-sm font-medium text-gray-800 bg-gray-50 rounded px-3 py-2 mb-6">{{ selectedPaymentId }}</p>
        <p class="text-gray-400 text-xs mb-6">Esta acción no se puede deshacer.</p>
        <div class="flex justify-end gap-3">
          <button 
            @click="showRefundModal = false" 
            class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 border border-gray-200 rounded-md hover:bg-gray-50 transition"
          >
            Cancelar
          </button>
          <button 
            @click="confirmRefund" 
            :disabled="refundLoading"
            class="px-4 py-2 text-sm bg-red-500 text-white rounded-md hover:bg-red-600 transition disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ refundLoading ? 'Procesando...' : 'Confirmar reembolso' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { usePaymentsStore } from '~/stores/payments'
import { useAuthStore } from '~/stores/auth'
import { setAuthToken, adminApi, api } from '~/api/payments'
import { onMounted, onUnmounted, ref } from 'vue'
import BaseInput from '~/components/common/BaseInput.vue'
import DateInput from '~/components/common/DateInput.vue'
import PaymentsTable from '~/components/payments/table/index.vue'

const filterEvent = ref(null)
const filterUserId = ref(null)
const filterCurrency = ref(null)
const filterDateFrom = ref(null)
const filterDateTo = ref(null)
const showRefundModal = ref(false)
const selectedPaymentId = ref(null)
const refundLoading = ref(false)

const paymentsStore = usePaymentsStore()
const authStore = useAuthStore()
const router = useRouter()

onMounted(async () => {
  if (authStore.token) {
    setAuthToken(authStore.token)
  }
  await paymentsStore.fetchPayments(1, 10)
  
})

async function changePage(page) {
  await paymentsStore.fetchPayments(page, 10)
}

async function handleLogout() {
  try {
    await authStore.logout()
  } catch(e) {
    console.error('Error during logout:', e)
  } finally {
    router.push('/login')
  }
}

async function applyFilters() {
  await paymentsStore.applyFilters(filterEvent.value, filterUserId.value, filterCurrency.value, filterDateFrom.value, filterDateTo.value)
}

function handleRefund(paymentId) {
  selectedPaymentId.value = paymentId
  showRefundModal.value = true
}

async function confirmRefund() {
  refundLoading.value = true
  try {
    await adminApi.refundPayment(selectedPaymentId.value)
    showRefundModal.value = false
    await paymentsStore.fetchPayments(paymentsStore.currentPage, 10)
  } catch (e) {
    console.error('Error al reembolsar:', e)
  } finally {
    refundLoading.value = false
  }
}

async function exportCsv() {
  const params = new URLSearchParams()
  if (filterEvent.value) params.append('event', filterEvent.value)
  if (filterUserId.value) params.append('user_id', filterUserId.value)
  if (filterCurrency.value) params.append('currency', filterCurrency.value)
  if (filterDateFrom.value) params.append('date_from', filterDateFrom.value)
  if (filterDateTo.value) params.append('date_to', filterDateTo.value)

  const { data } = await api.get(`/api/payments/export?${params.toString()}`, {
    responseType: 'blob'
  })

  const url = window.URL.createObjectURL(new Blob([data]))
  const link = document.createElement('a')
  link.href = url
  link.setAttribute('download', 'payments.csv')
  document.body.appendChild(link)
  link.click()
  link.remove()
}
</script>