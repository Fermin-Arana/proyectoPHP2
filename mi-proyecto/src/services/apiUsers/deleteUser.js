import api from '../api.js';

export const deleteUser = async(id) => {
    try{
        const response = await api.delete(`/user/${id}`);
        return response.data
    } catch (error){
        console.error('error en el deleteUser', error);
        throw error;
    }
}