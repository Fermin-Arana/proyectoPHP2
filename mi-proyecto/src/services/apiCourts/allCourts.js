import api from '../api.js';

export const allCourts = async() => {
    try{
        const response = await api.get('/courts');
        return response.data;
    } catch (error){
        console.error('error en allCourts', error);
        throw error;
    }

}