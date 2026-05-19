import Echo from 'laravel-echo'
import Pusher from 'pusher-js'
import { usePaymentsStore } from '~/stores/payments'
import { useMetricsStore } from '~/stores/metrics'

export default defineNuxtPlugin(() => {
  (window as any).Pusher = Pusher
  const config = useRuntimeConfig()
  const echo = new Echo({
    broadcaster: 'reverb',
    key: config.public.reverbKey,
    wsHost: config.public.reverbHost,
    wsPort: Number(config.public.reverbPort),
    forceTLS: false,
    enabledTransports: ['ws', 'wss'],
  })

  const initEcho = () => {
    const paymentsStore = usePaymentsStore()
    const metricsStore = useMetricsStore()

    echo.channel('payments').listen('PaymentReceived', async (data: any) => {
      await paymentsStore.fetchPayments(paymentsStore.currentPage, 10)
      await metricsStore.fetchMetrics()
      const route = useRoute()

      if (route.path === `/payments/${data.payments.payment_id}`){
        await paymentsStore.fetchPaymentEvents(data.payments.payment_id)
      }
    })
  }

  return {
    provide: {
      echo,
      initEcho
    }
  }
})