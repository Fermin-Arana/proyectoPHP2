import api from '../api.js';

export const edit = async(id, userData) => {
    try{
        const apiData = {
            first_name: userData.first_name,
            last_name: userData.last_name
        };
        const response = await api.patch(`/user/${id}`, apiData);
        return response.data;
    } catch (error){
        console.error('error en el editUser', error);
        throw error;
    }

    
}