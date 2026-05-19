<template>
  <div class="min-h-screen bg-gray-100">
    <nav class="bg-white shadow-sm px-6 py-4 flex justify-between items-center">
      <h1 class="text-xl font-bold text-gray-800">Metrics</h1>
      <NuxtLink to="/payments" class="text-gray-600 hover:text-gray-900 font-medium text-sm transition">
        ← Back to Dashboard
      </NuxtLink>
    </nav>

    <div class="p-6 space-y-6">
      <div class="bg-white rounded-lg shadow p-6 text-center">
        <p class="text-gray-500 text-sm mb-1">Unique Users</p>
        <p class="text-5xl font-bold text-gray-800">{{ metricsStore.unique_users_count }}</p>
      </div>

      <div class="grid grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
          <h2 class="text-gray-700 font-semibold mb-4">Payments by Event</h2>
          <Bar :data="eventChartData" />
        </div>
        <div class="bg-white rounded-lg shadow p-6">
          <h2 class="text-gray-700 font-semibold mb-4">Payments by Currency</h2>
          <Bar :data="currencyChartData" />
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-gray-700 font-semibold mb-4">Volume by Day</h2>
        <Bar :data="volumeChartData" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, computed } from 'vue'
import { useMetricsStore } from '~/stores/metrics'
import { Bar } from 'vue-chartjs'
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend } from 'chart.js'

definePageMeta({ middleware: 'admin' })

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend)

const metricsStore = useMetricsStore()
let refreshInterval = null

const getColor = (index) => {
  const colors = ['#8B5CF6', '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#EC4899', '#14B8A6', '#F97316']
  return colors[index % colors.length]
}

const eventChartData = computed(() => ({
  labels: metricsStore.payments_by_event.map(e => e.event),
  datasets: [{
    label: 'Payments',
    data: metricsStore.payments_by_event.map(e => e.total),
    backgroundColor: '#3B82F6'
  }]
}))

const currencyChartData = computed(() => ({
  labels: metricsStore.payments_by_currency.map(e => e.currency),
  datasets: [{
    label: 'Payments',
    data: metricsStore.payments_by_currency.map(e => e.total),
    backgroundColor: '#10B981'
  }]
}))

const volumeChartData = computed(() => {
  const days = [...new Set(metricsStore.volume_by_day.map(e => e.date))]
  const currencies = [...new Set(metricsStore.volume_by_day.map(e => e.currency))]

  const datasets = currencies.map((currency, index) => ({
    label: currency,
    data: days.map(day => {
      const found = metricsStore.volume_by_day.find(e => e.date === day && e.currency === currency)
      return found ? found.total : 0
    }),
    backgroundColor: getColor(index)
  }))

  return { labels: days, datasets }
})

onMounted(async () => {
  await metricsStore.fetchMetrics()

  refreshInterval = setInterval(async () => {
    await metricsStore.fetchMetrics()
  }, 5000)
})
</script>