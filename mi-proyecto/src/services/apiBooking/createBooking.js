import api from '../api.js';

export const createBooking = async (bookingData) => {
    try {
        const response = await api.post('/booking', bookingData);
        return response.data;
        
    } catch (error) {
        console.error('Error en createBooking:', error);
        throw error;
    }
}