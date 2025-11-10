import api from '../api.js';

export const registerUser = async(email, password,firstName, lastName) =>{
    try{
        const response = await api.post('/user', { email, password, firstName, lastName });
        return response.data;
    } catch (error){
        console.error('error en el register', error);
        throw error;
    }
}