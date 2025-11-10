import api from '../api.js';

export const searchUsers = async(query) => {
    try{
        const response = await api.get('/users', { query });
        return response.data;
    } catch (error){
        console.error('error en el searchUsers', error);
        throw error;
    }

}