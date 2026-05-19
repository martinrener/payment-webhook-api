<template>
  <div class="min-h-screen bg-gray-100">
    <nav class="bg-white shadow-sm px-6 py-4 flex items-center gap-4">
      <button
        @click="router.push('/payments')"
        class="text-blue-600 hover:text-blue-800 text-sm"
      >
        ← Volver
      </button>
      <h1 class="text-xl font-bold text-gray-800">Payment: {{ route.params.id }}</h1>
    </nav>

    <div class="p-6">
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <TableHead :titles="['Event ID', 'Event', 'Amount', 'Currency', 'Timestamp', 'Received At']" />
          <EventsTableBody :events="paymentsStore.currentPaymentEvents" />
        </table>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { usePaymentsStore } from '~/stores/payments'
import TableHead from '~/components/payments/table/TableHead.vue'
import EventsTableBody from '~/components/events/EventsTableBody.vue'

const paymentsStore = usePaymentsStore()
const route = useRoute()
const router = useRouter()

onMounted(async () => {
  await paymentsStore.fetchPaymentEvents(route.params.id as string)
})
</script>