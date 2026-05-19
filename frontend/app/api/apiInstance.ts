import axios from 'axios'

const api = axios.create({
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
})

api.interceptors.response.use(
  response => response,
  error => {
    if (error.response?.status === 401) {
      const hadToken = localStorage.getItem('authToken')
      localStorage.removeItem('authToken')
      if (hadToken) {
        window.location.href = '/login'
      }
    }
    return Promise.reject(error)
  }
)

export default api;