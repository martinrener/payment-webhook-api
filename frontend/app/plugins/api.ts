import api from '~/api/apiInstance'

export default defineNuxtPlugin(() => {
  const config = useRuntimeConfig()
  api.defaults.baseURL = config.public.apiBase
})
