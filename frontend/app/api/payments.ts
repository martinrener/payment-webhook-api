import api from './apiInstance'


// Función para setear el token en el header
// Almacenar el token el localstorage para persistencia
// Trabajar en logica para obtener el token desde el localstorage al iniciar la app y setearlo en el header
// Recomendado para establecer el token: utilizar plugins de Nuxt
export const setAuthToken = (token: string | null) => {
  if (token) {
    api.defaults.headers.common['Authorization'] = `Bearer ${token}`
  } else {
    delete api.defaults.headers.common['Authorization']
  }
}

export const authApi = {
  login: (email: string, password: string) =>
    api.post('/api/login', { email, password }),

  logout: () =>
    api.post('/api/logout'),

  getUser: () =>
    api.get('/api/user'),
}

export const paymentsApi = {
  getPayments: (page: number, perPage: number, event: string | null = null, user_id: string | null = null, currency: string | null = null, dateFrom: string | null = null, dateTo: string | null = null) =>
    api.get('/api/payments', { 
      params: { 
        page, 
        per_page: perPage, 
        ...(event && { event }),
        ...(user_id && { user_id }),
        ...(currency && { currency }),
        ...(dateFrom && { date_from: dateFrom }),
        ...(dateTo && { date_to: dateTo }),
      } 
    }),
  getPaymentEvents: (paymentId: string) =>
    api.get(`/api/payments/${paymentId}/events`),
}

export const metricsApi = {
  getMetrics: () =>
    api.get('/api/metrics')
}

export const adminApi = {
  refundPayment: (paymentId: string) =>
    api.post('/api/admin/refund', { payment_id: paymentId })
}

export { api }