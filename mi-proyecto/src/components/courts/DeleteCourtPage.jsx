import { deleteCourt } from '../../services/apiCourts/deleteCourt'
import { useAuth } from '../../context/AuthContext'
import { useNavigate, useParams } from 'react-router-dom'
import Button from '../button/Button.jsx'
import './courtStyle.css'
import { useState } from 'react'

const DeleteCourtPage = () =>{
    const { isAdmin } = useAuth();
    const navigate = useNavigate();
    const { id } = useParams();
    const [ error, setError ] = useState('');


    const handleSubmit = async(e) => {
        e.preventDefault();
        try{
            const response = await deleteCourt(id);
            if(response.status === 200){
                console.log("Borrado con exito!");
                navigate('/courts');
            } else {
                console.error("No es posible eliminar esa cancha!");
                setError(response.message);
            }
        }catch(error){
            setError(error.message);
            throw error;
        }
    };

    return (
        <div className="delete-court-container">
            <div className="delete-court-card">
            {isAdmin && (
                <>
                    <h2 className="delete-court-tittle">Eliminar cancha</h2>
                    <p className="delete-warning-text">Estas seguro que queres borrar la cancha?</p>
                    <div className="delete-court-actions">
                        <Button onClick={handleSubmit} className="btn-danger">Si, borrar</Button>
                        <Button onClick={()=> navigate("/courts")} className="btn-secondary">No, volver</Button>
                    </div>
                    {error && (
                        <p className="error-text">No se puede eliminar esta cancha debido a que tiene reservas</p>
                    )}
                </>
            )}
            {!isAdmin && (
                <p>No tienes permiso para estar en esta seccion.</p>
            )}
            </div>
        </div>
    )
}

export default DeleteCourtPage