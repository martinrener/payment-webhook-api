<template>
    <thead class="bg-gray-50">
        <tr >
            <th v-for="title in titles " :key="title" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                {{ title }}
            </th>
        </tr>
    </thead>
</template>

<script setup>
    defineProps({
        titles: Array
    })
</script>