import api from '../api.js';
export const updateBookingParticipants = async (bookingId, companions) => {
    try {
        const apiData = {
            companeros: companions
        };
        const response = await api.put(`/booking_participant/${bookingId}`, apiData);
        
        return response.data;

    } catch (error) {
        console.error('Error en updateBookingParticipants:', error);
        throw error;
    }
}