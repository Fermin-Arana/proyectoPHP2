import api from '../api.js';

export const registerUser = async(userData) =>{
    try{
        const response = await api.post('/user', userData);
        return response.data;
    } catch (error){
        console.error('error en el register', error);
        throw error;
    }
}