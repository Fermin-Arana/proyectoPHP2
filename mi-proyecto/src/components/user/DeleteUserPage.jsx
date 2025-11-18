import { deleteUser } from '../../services/apiUsers/deleteUser'
import { useAuth } from '../../context/AuthContext'
import { useState } from 'react'
import { useNavigate, useParams } from 'react-router-dom'
import Button from '../button/Button'
import './userStyle.css'

const DeleteUserPage = () =>{
    const [ error,setError ] = useState(null);
    const { isAdmin } = useAuth();
    const navigate = useNavigate();
    const { id } = useParams();

    const handleSubmit = async(e) => {
        e.preventDefault();
        setError(null);
        try{
            const response = await deleteUser(id);
            if (response.status === 200){
                console.log("usuario borrado con exito");
                navigate("/userlist");
            } else {
                console.error("error de la api en deleteUser");
                setError(response.message);
            }
        } catch(error){
            setError(error.message);
            throw error;
        }
    }
return (
        <div className="delete-user-container">
            <h2 className="delete-user-tittle">Borrar usuario</h2>
            <p className="delete-user-message">Estas seguro que quieres borrar este usuario?</p>
            <div className="delete-user-actions">
                <Button onClick={handleSubmit} className="delete-confirm-btn">
                    Si, borrar usuario
                </Button>
                <Button onClick={()=>navigate("/userlist")} className="delete-cancel-btn">
                    No, volver
                </Button>
            </div>
             {error && <p className="error-message">Este usuario tiene reservas, no se puede eliminar.</p>}
        </div>
    )
}

export default DeleteUserPage