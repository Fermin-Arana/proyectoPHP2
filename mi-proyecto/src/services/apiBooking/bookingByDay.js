import api from '../api.js';

export const bookingsByDay = async (date) => {
    try {
        const response = await api.get('/booking', {
            params: {
                date: date 
            }
        });
        
        return response.data;
        
    } catch (error) {
        console.error('Error en bookingsByDay:', error);
        throw error;
    }
}