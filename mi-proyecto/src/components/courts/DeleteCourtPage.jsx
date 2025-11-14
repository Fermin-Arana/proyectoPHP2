import { deleteCourt } from '../../services/apiCourts/deleteCourt'
import { useAuth } from '../../context/AuthContext'
import { useNavigate, useParams } from 'react-router-dom'

const DeleteCourtPage = () =>{
    const { isAdmin } = useAuth();
    const navigate = useNavigate();
    const { id } = useParams();

    const handleSubmit = async(e) => {
        e.preventdefault();
        try{
            const response = await deleteCourt(id);
            if(response.status === 200){
                console.log("Borrado con exito!");
                navigate('/courts');
            } else {
                console.log("No es posible eliminar esa cancha!");
            }
        }catch(error){
            console.error("ERROR",error);
            throw error;
        }
    };
}

export default DeleteCourtPage