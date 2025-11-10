import api from '../api.js';

export const createBooking = async (bookingData) => {
    try {
        const apiData = {
            court_id: bookingData.courtId,
            booking_datetime: bookingData.bookingDatetime,
            duration_blocks: bookingData.durationBlocks,
            participants: bookingData.participants
        };

        const response = await api.post('/booking', apiData);
        return response.data;
        
    } catch (error) {
        console.error('Error en createBooking:', error);
        throw error;
    }
}