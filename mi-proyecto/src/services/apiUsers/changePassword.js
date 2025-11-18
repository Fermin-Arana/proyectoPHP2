import api from '../api.js';

export const changePassword = async (id, newPassword) => {
    try {
        const apiData = {
            password: newPassword
        };
        const response = await api.patch(`/user/${id}`, apiData);
        return response.data;
    } catch (error) {
        console.error('Error en changePassword', error);
        throw error;
    }
}