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
        <PaymentFilters 
          @apply="applyFilters"
          @export="handleExport"
        />
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
    <RefundModal v-if="showRefundModal"
      :paymentId="selectedPaymentId"
      :loading="refundLoading"
      @confirm="confirmRefund"
      @cancel="showRefundModal = false"
    />
  </div>
</template>

<script setup>
import { usePaymentsStore } from '~/stores/payments'
import { useAuthStore } from '~/stores/auth'
import { adminApi, api } from '~/api/payments'
import { onMounted, ref } from 'vue'
import PaymentsTable from '~/components/payments/table/index.vue'
import RefundModal from '~/components/payments/RefundModal.vue'
import PaymentFilters from '~/components/payments/PaymentFilters.vue'

const showRefundModal = ref(false)
const selectedPaymentId = ref(null)
const refundLoading = ref(false)

const paymentsStore = usePaymentsStore()
const authStore = useAuthStore()
const router = useRouter()

onMounted(async () => {
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

async function applyFilters(event, userId, currency, dateFrom, dateTo) {
  await paymentsStore.applyFilters(event, userId, currency, dateFrom, dateTo)
}

async function handleExport(event, userId, currency, dateFrom, dateTo) {
  const params = new URLSearchParams()
  if (event) params.append('event', event)
  if (userId) params.append('user_id', userId)
  if (currency) params.append('currency', currency)
  if (dateFrom) params.append('date_from', dateFrom)
  if (dateTo) params.append('date_to', dateTo)

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