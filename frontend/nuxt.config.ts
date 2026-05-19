export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },
  modules: [
    '@pinia/nuxt',
    'nuxt-auth-sanctum',
    '@nuxtjs/tailwindcss',
  ],
  runtimeConfig: {
    public: {
      reverbKey: process.env.REVERB_APP_KEY,
      apiBase: 'http://localhost:8000', // overridden at runtime by NUXT_PUBLIC_API_BASE env var
      reverbHost: process.env.REVERB_HOST || 'localhost',
      reverbPort: process.env.REVERB_PORT || '8080',
    },
  },
  sanctum: {
    baseUrl: process.env.NUXT_PUBLIC_API_BASE || 'http://localhost:8000',
  },
})