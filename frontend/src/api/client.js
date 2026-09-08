import axios from 'axios';

// Point this at your Laravel API root (e.g. http://localhost:8000/api)
const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
});

// Attach the Sanctum token stored after login to every admin request
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token');
  if (token) config.headers.Authorization = `Bearer ${token}`;
  return config;
});

export default api;
