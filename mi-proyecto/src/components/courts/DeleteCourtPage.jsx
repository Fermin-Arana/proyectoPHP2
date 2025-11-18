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

    return (
        <div className="delete-container">
            {isAdmin && (
                <>
                    <h2 className="delete-tittle">Eliminar cancha</h2>
                    <p>Estas seguro que queres borrar la cancha?</p>
                    <Button onClick={handleSubmit} className="delete-btn">Si, borrar</Button>
                    <Button onClick={()=> navigate("/courts")} className="delete-btn">No, volver</Button>
                </>
            )}
            {!isAdmin && (
                <p>No tienes permiso para estar en esta seccion.</p>
            )}
        </div>
    )
}

export default DeleteCourtPage