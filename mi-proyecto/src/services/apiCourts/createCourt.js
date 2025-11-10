import api from '../api.js';

export const createCourt = async (name,description) => {
    try{
        const response = await api.post('/court', {name,description});
        return response.data;
    } catch (error){
        console.error('error en el createCourt', error);
        throw error;
    }
}