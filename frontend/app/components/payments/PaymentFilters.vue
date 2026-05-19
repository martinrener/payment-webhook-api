<template>
    <div class="px-6 py-4 border-b">
        <h2 class="text-lg font-semibold text-gray-800 mb-3">Filters</h2>
        <div class="flex gap-3 items-center">
            <BaseInput v-model="filterEvent" placeholder="Event Type" />
            <BaseInput v-model="filterUserId" placeholder="User ID" />
            <BaseInput v-model="filterCurrency" placeholder="Currency" />
            <DateInput v-model="filterDateFrom" label="Date From" />
            <DateInput v-model="filterDateTo" label="Date To" />
            <button
                @click="applyFilters"
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition text-sm font-medium"
            >
                Filtros
            </button>
            <button
                @click="handleExport"
                class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition text-sm font-medium"
            >
                Export CSV
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import BaseInput from '../common/BaseInput.vue'
import DateInput from '../common/DateInput.vue'

const filterEvent = ref<string | undefined>(undefined)
const filterUserId = ref<string | undefined>(undefined)
const filterCurrency = ref<string | undefined>(undefined)
const filterDateFrom = ref<string | undefined>(undefined)
const filterDateTo = ref<string | undefined>(undefined)

const emit = defineEmits<{
  apply: [event: string | null, userId: string | null, currency: string | null, dateFrom: string | null, dateTo: string | null]
  export: [event: string | null, userId: string | null, currency: string | null, dateFrom: string | null, dateTo: string | null]
}>()

function applyFilters() {
  emit('apply', filterEvent.value ?? null, filterUserId.value ?? null, filterCurrency.value ?? null, filterDateFrom.value ?? null, filterDateTo.value ?? null)
}

function handleExport() {
  emit('export', filterEvent.value ?? null, filterUserId.value ?? null, filterCurrency.value ?? null, filterDateFrom.value ?? null, filterDateTo.value ?? null)
}
</script>
