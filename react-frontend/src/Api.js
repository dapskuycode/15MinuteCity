// src/api.js
import axios from 'axios';

// Buat instance axios
const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL, // URL backend dari .env React
  headers: {
    'Content-Type': 'application/json',
    // Authorization bisa ditambahkan jika pakai token nanti
  },
});

// Contoh interceptor error (optional)
api.interceptors.response.use(
  response => response,
  error => {
    console.error('API Error:', error?.response || error);
    return Promise.reject(error);
  }
);

export default api;
