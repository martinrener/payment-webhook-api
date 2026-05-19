import { defineStore } from 'pinia'
import { metricsApi } from '~/api/payments'

export const useMetricsStore = defineStore('metrics', {
    state: () => ({
        payments_by_event: [] as any[],
        unique_users_count: 0 as number,
        payments_by_currency: [] as any[],
        volume_by_day: [] as any[],
    }),

    actions: {
        async fetchMetrics() {
            try {
                const response = await metricsApi.getMetrics()
                const data = response.data

                this.payments_by_event = data.payments_by_event
                this.unique_users_count = data.unique_users_count
                this.payments_by_currency = data.payments_by_currency
                this.volume_by_day = data.volume_by_day
            } catch (error) {
                console.error('Error fetching metrics:', error)
            }
        }
    }

})