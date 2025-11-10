import axios from 'axios';

const api = axios.create({
    baseURL: 'HTTPS://localhost:8000/slim',
    headers: {
        'Content-Type': 'application/json'
    }
});

api.interceptors.request.use( 
    (config) => {
        const token = localStorage.getItem('token');
        if (token) {
            config.headers['Authorization'] = `Bearer ${token}`;
        }
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

export default api;