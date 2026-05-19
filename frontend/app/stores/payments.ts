import { defineStore } from "pinia";
import { paymentsApi } from "~/api/payments";
import type { Payment, EventLog } from "~/types";
import { ref } from "vue";

export const usePaymentsStore = defineStore("payments", () => {
    const payments = ref<Payment[]>([]);
    const currentPage = ref(1);
    const lastPage = ref(1);
    const total = ref(0);
    const currentPaymentEvents = ref<EventLog[]>([]);
    const activeEvent = ref<string | null>(null);
    const activeUserId = ref<string | null>(null);
    const activeCurrency = ref<string | null>(null);
    const activeDateFrom = ref<string | null>(null);
    const activeDateTo = ref<string | null>(null);

    async function fetchPayments(page: number, perPage: number) {
        const { data } = await paymentsApi.getPayments(
            page,
            perPage,
            activeEvent.value,
            activeUserId.value,
            activeCurrency.value,
            activeDateFrom.value,
            activeDateTo.value,
        );
        payments.value = data.data;
        currentPage.value = data.current_page;
        lastPage.value = data.last_page;
        total.value = data.total;
    }

    async function applyFilters(
        event: string | null,
        userId: string | null,
        currency: string | null,
        dateFrom: string | null,
        dateTo: string | null,
    ) {
        activeEvent.value = event;
        activeUserId.value = userId;
        activeCurrency.value = currency;
        activeDateFrom.value = dateFrom;
        activeDateTo.value = dateTo;
        await fetchPayments(1, 10);
    }

    async function fetchPaymentEvents(paymentId: string) {
        const { data } = await paymentsApi.getPaymentEvents(paymentId);
        currentPaymentEvents.value = data;
    }
    return {
        payments,
        currentPage,
        lastPage,
        total,
        currentPaymentEvents,
        activeEvent,
        activeUserId,
        activeCurrency,
        activeDateFrom,
        activeDateTo,
        fetchPayments,
        applyFilters,
        fetchPaymentEvents,
    };
});
