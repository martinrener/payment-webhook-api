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

const filterEvent = ref<string | null>(null)
const filterUserId = ref<string | null>(null)
const filterCurrency = ref<string | null>(null)
const filterDateFrom = ref<string | null>(null)
const filterDateTo = ref<string | null>(null)

const emit = defineEmits<{
  apply: [event: string | null, userId: string | null, currency: string | null, dateFrom: string | null, dateTo: string | null]
  export: [event: string | null, userId: string | null, currency: string | null, dateFrom: string | null, dateTo: string | null]
}>()

function applyFilters() {
  emit('apply', filterEvent.value, filterUserId.value, filterCurrency.value, filterDateFrom.value, filterDateTo.value)
}

function handleExport() {
  emit('export', filterEvent.value, filterUserId.value, filterCurrency.value, filterDateFrom.value, filterDateTo.value)
}
</script>
