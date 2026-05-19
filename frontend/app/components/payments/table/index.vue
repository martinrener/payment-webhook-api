<template>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <TableHead :titles="titles" />
            <TableBody :payments="payments" :isAdmin="isAdmin" @select="$emit('select', $event)" @refund="$emit('refund', $event)" />
        </table>
        <TableFooter :total="total" :currentPage="currentPage" :lastPage="lastPage" @changePage="$emit('changePage', $event)" />
    </div>
</template>

<script setup>
import TableHead from './TableHead.vue'
import TableBody from './TableBody.vue'
import TableFooter from './TableFooter.vue'

defineProps({
    titles: Array,
    payments: Array,
    total: Number,
    currentPage: Number,
    lastPage: Number,
    isAdmin: Boolean
})

defineEmits(['select', 'changePage', 'refund'])
</script>