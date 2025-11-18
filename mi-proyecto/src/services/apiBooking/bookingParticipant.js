import api from '../api.js';
export const bookingParticipant = async (bookingId) => {
    try {
        const response = await api.get(`/booking/${bookingId}/participants`);
        
        return response.data;

    } catch (error) {
        console.error('Error en bookingParticipant:', error);
        throw error;
    }
}