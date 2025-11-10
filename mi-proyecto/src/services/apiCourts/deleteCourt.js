import api from '../api.js';

export const deleteCourt = async (id) => {
    try{
        const response = await api.delete(`/court/${id}`);
        return response.data;
    } catch (error) {
        console.error('error en el deleteCourt', error);
        throw error;
    }
}