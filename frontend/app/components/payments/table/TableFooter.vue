<template>
    <div class="flex justify-between items-center px-6 py-4 border-t">
        <span class="text-sm text-gray-500">Total: {{ total }} pagos</span>
        <div class="flex gap-2">
            <button @click="$emit('changePage', currentPage - 1)" :disabled="currentPage === 1" class="px-3 py-1 text-sm border rounded disabled:opacity-50">Anterior</button>
            <span class="px-3 py-1 text-sm">{{ currentPage }} / {{ lastPage }}</span>
            <button @click="$emit('changePage', currentPage + 1)" :disabled="currentPage === lastPage" class="px-3 py-1 text-sm border rounded disabled:opacity-50">Siguiente</button>
        </div>
    </div>
</template>

<script setup>
    defineProps({
        total: Number,
        currentPage: Number,
        lastPage: Number
    })

    defineEmits(['changePage'])
</script>