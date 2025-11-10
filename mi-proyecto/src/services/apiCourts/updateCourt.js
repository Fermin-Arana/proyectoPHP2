import api from '../api.js';

export const updateCourt = async (id, courtData) => {
    try {
        const apiData = {
            name: courtData.name,
            description: courtData.description
        };
        const response = await api.put(`/court/${id}`, apiData);
        return response.data;
    } catch (error) {
        console.error('error en el updateCourt', error);
        throw error;
    }
}