<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md mx-4">
        <div class="flex items-center gap-3 mb-4">
          <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
            <span class="text-red-600 text-lg">⚠</span>
          </div>
          <h3 class="text-lg font-semibold text-gray-800">Confirmar reembolso</h3>
        </div>
        <p class="text-gray-500 text-sm mb-2">Estás a punto de reembolsar el pago:</p>
        <p class="font-mono text-sm font-medium text-gray-800 bg-gray-50 rounded px-3 py-2 mb-6">{{ paymentId }}</p>
        <p class="text-gray-400 text-xs mb-6">Esta acción no se puede deshacer.</p>
        <div class="flex justify-end gap-3">
          <button 
            @click="emit('cancel')"
            class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 border border-gray-200 rounded-md hover:bg-gray-50 transition"
          >
            Cancelar
          </button>
          <button 
            @click="emit('confirm')"
            :disabled="loading"
            class="px-4 py-2 text-sm bg-red-500 text-white rounded-md hover:bg-red-600 transition disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ loading ? 'Procesando...' : 'Confirmar reembolso' }}
          </button>
        </div>
      </div>
    </div>
</template>

<script setup lang="ts">
defineProps<{
  paymentId: string
  loading: boolean
}>()
const emit = defineEmits<{
  confirm: []
  cancel: []
}>()
</script>