<template>
    <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="payment in payments" :key="payment.payment_id" class="hover:bg-gray-50 cursor-pointer" @click="$emit('select', payment.payment_id)">
              <td class="px-6 py-4 text-sm font-mono text-gray-900">{{ payment.payment_id }}</td>
              <td class="px-6 py-4 text-sm">
                <span class="px-2 py-1 rounded-full text-xs font-medium" :class="{'bg-green-100 text-green-800': payment.event === 'payment.completed', 'bg-yellow-100 text-yellow-800': payment.event === 'payment.pending', 'bg-red-100 text-red-800': payment.event === 'payment.refunded', 'bg-gray-100 text-gray-800': !['payment.completed','payment.pending','payment.refunded'].includes(payment.event)}">{{ payment.event }}</span>
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">{{ payment.amount }}</td>
              <td class="px-6 py-4 text-sm text-gray-900">{{ payment.currency }}</td>
              <td class="px-6 py-4 text-sm text-gray-900">{{ payment.user_id }}</td>
              <td class="px-6 py-4 text-sm font-mono text-gray-500">{{ payment.last_event_id }}</td>
              <td v-if="isAdmin" class="px-6 py-4 text-sm font-mono text-gray-500"><button class="text-red-500 hover:text-blue-700" @click.stop="$emit('refund', payment.payment_id)">Refund</button></td>
            </tr>
          </tbody>
</template>

<script setup>
    defineProps({
        payments: Array,
        isAdmin: Boolean
    })

    defineEmits(['select', 'refund'])
</script>