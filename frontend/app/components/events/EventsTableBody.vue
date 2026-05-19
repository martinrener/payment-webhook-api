<template>
    <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="event in events" :key="event.event_id" class="hover:bg-gray-50">
              <td class="px-6 py-4 text-sm font-mono text-gray-900">{{ event.event_id }}</td>
              <td class="px-6 py-4 text-sm">
                <span class="px-2 py-1 rounded-full text-xs font-medium" :class="{'bg-green-100 text-green-800': event.event === 'payment.completed', 'bg-yellow-100 text-yellow-800': event.event === 'payment.pending', 'bg-red-100 text-red-800': event.event === 'payment.refunded', 'bg-gray-100 text-gray-800': !['payment.completed','payment.pending','payment.refunded'].includes(event.event)}">{{ event.event }}</span>
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">{{ event.amount }}</td>
              <td class="px-6 py-4 text-sm text-gray-900">{{ event.currency }}</td>
              <td class="px-6 py-4 text-sm text-gray-900">{{ event.timestamp }}</td>
              <td class="px-6 py-4 text-sm font-mono text-gray-500">{{ event.received_at }}</td>
            </tr>
          </tbody>
</template>

<script setup>
    defineProps({
        events: Array
    })

</script>