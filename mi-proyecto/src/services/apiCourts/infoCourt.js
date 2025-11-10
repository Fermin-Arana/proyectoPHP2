import api from '../api.js';

export const infoCourt = async (id) => {
    try {
        const response = await api.get(`/court/${id}`);
        return response.data;
    } catch (error) {
        console.error('error en el infoCourt', error);
        throw error;
    }
}