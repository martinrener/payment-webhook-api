<template>
  <div>
    <NuxtRouteAnnouncer />
    <NuxtPage />
  </div>
</template>

<script setup>
import { onMounted, onUnmounted } from 'vue'

const { $initEcho, $echo } = useNuxtApp()

onMounted(() => {
  const token = localStorage.getItem('authToken')
  if (token) {
    $initEcho()
  }
})

onUnmounted(() => {
  $echo.leaveChannel('payments')
})
</script>