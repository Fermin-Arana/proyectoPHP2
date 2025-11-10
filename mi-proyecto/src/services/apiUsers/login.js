import api from '../api.js';

export const login = async(email, password) => {
    try{
        const response = await api.post('/login', { email, password });
        return response.data;
    } catch (error) {
        console.error('error en el login', error);
        throw error;
    }
};