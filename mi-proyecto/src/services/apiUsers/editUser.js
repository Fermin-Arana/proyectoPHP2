import api from '../api.js';

export const edit = async(id, userData) => {
    try{
        const apiData = {
            firstName: userData.firstName,
            lastName: userData.lastName,
            password: userData.password
        };
        const response = await api.patch(`/user/${id}`, apiData);
        return response.data;
    } catch (error){
        console.error('error en el editUser', error);
        throw error;
    }

    
}