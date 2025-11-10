import api from '../api.js';

export const deleteBooking = async(id) => {
    try {
        const response = await api.delete(`/booking/${id}`);
        return response.data;
    } catch (error) {
        console.error('Error en deleteBooking:', error);
        throw error;
    }
}