import { defineStore } from "pinia";
import { metricsApi } from "~/api/payments";
import type { PaymentsByEvent, PaymentsByCurrency, VolumeByDay } from "~/types";
import { ref } from "vue";

export const useMetricsStore = defineStore("metrics", () => {
    const payments_by_event = ref<PaymentsByEvent[]>([]);
    const unique_users_count = ref(0);
    const payments_by_currency = ref<PaymentsByCurrency[]>([]);
    const volume_by_day = ref<VolumeByDay[]>([]);

    async function fetchMetrics() {
        try {
            const response = await metricsApi.getMetrics();
            const data = response.data;

            payments_by_event.value = data.payments_by_event;
            unique_users_count.value = data.unique_users_count;
            payments_by_currency.value = data.payments_by_currency;
            volume_by_day.value = data.volume_by_day;
        } catch (error) {
            console.error("Error fetching metrics:", error);
        }
    }
    return {
        payments_by_event,
        unique_users_count,
        payments_by_currency,
        volume_by_day,
        fetchMetrics,
    };
});
