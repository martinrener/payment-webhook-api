import { defineStore } from 'pinia'
import { paymentsApi } from '~/api/payments'

export const usePaymentsStore = defineStore('payments', {
  state: () => ({
    payments: [] as any[],
    currentPage: 1 as number,
    lastPage: 1 as number,
    total: 0 as number,
    currentPaymentEvents: [] as any[],
    activeEvent: null as string | null,
    activeUserId: null as string | null,
    activeCurrency: null as string | null,
    activeDateFrom: null as string | null,
    activeDateTo: null as string | null,
  }),

  actions: {
    async fetchPayments(page: number, perPage: number) {
      const { data } = await paymentsApi.getPayments(
        page, 
        perPage, 
        this.activeEvent,
        this.activeUserId, 
        this.activeCurrency,
        this.activeDateFrom,
        this.activeDateTo,
      )
      this.payments = data.data
      this.currentPage = data.current_page
      this.lastPage = data.last_page
      this.total = data.total
    },

    async applyFilters(event: string | null, userId: string | null, currency: string | null, dateFrom: string | null, dateTo: string | null) {
      this.activeEvent = event
      this.activeUserId = userId
      this.activeCurrency = currency
      this.activeDateFrom = dateFrom
      this.activeDateTo = dateTo
      await this.fetchPayments(1, 10)
    },

    async fetchPaymentEvents(paymentId: string) {
      const { data } = await paymentsApi.getPaymentEvents(paymentId)
      this.currentPaymentEvents = data
    },
  },
})