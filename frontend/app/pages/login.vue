<template>
  <div class="min-h-screen bg-gray-100 flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
      <h1 class="text-2xl font-bold text-gray-800 mb-6">Login</h1>
      <form @submit.prevent="handleLogin" class="space-y-4">
        <fieldset :disabled="!mounted" class="contents">
          <BaseInput
            v-model="email"
            type="email"
            label="Email"
            placeholder="martin@test.com"></BaseInput>
          <BaseInput
            v-model="password"
            type="password"
            label="Password"
            placeholder="••••••••"></BaseInput>
        </fieldset>
        <p v-if="error" class="text-red-500 text-sm">{{ error }}</p>
        <button
          type="submit"
          :disabled="!mounted"
          class="w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700 transition disabled:opacity-50"
        >
          Entrar
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import BaseInput from '~/components/common/BaseInput.vue'
import { ref, onMounted } from 'vue'
import { useAuthStore } from '~/stores/auth'

const authStore = useAuthStore()
const router = useRouter()

const email = ref('')
const password = ref('')
const error = ref('')
const mounted = ref(false)

onMounted(() => {
  mounted.value = true
})

async function handleLogin() {
  try {
    await authStore.login(email.value, password.value)
    router.push('/payments')
  } catch (e) {
    error.value = 'Credenciales incorrectas'
  }
}
</script>