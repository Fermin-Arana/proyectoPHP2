import api from '../api.js';

export const logout = async() => {
    try{
        const response = await api.post('/logout');
        return response.data;
    } catch (error) {
        console.error('error en el logout', error);
        throw error;
    }
}