import api from '../api.js';

export const user = async(id) => {
    try{
        const response = await api.get(`/user/${id}`);
        return response.data;
    } catch (error){
        console.error('error en el user', error);
        throw error;
    }
}