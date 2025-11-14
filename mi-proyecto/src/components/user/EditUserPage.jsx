import { edit }  from '../../services/apiUsers/editUser.js'
import { useAuth } from '../../context/AuthContext.jsx'
import { useState } from "react"
import { useNavigate, Link } from 'react-router-dom'
import Button from '../button/Button.jsx'

const EditUserPage = () => {
    const { user, isAutenticated } = useAuth();
    const [first_name, setFirstName] = useState(user.first_name || '');
    const [last_name, setLastName] = useState(user.last_name || '');
    const navigate = useNavigate();

    const handleSubmit = async(e) =>{
        e.preventDefault();
        try{
            const response = await edit(user.id,{
                first_name: first_name,
                last_name: last_name
            });
            if(response.status === 200){
                console.log("Editado con exito", response.message)
                navigate('/');
            } else {
                console.error("error de la API", response.message)
            }
        }catch(error){
            console.error("No se pudo editar", error);
            throw error;
        }

    };

    return(
        <div className="edit-user-container">
            {isAutenticated && (
            <div className="edit-user-form">
                <h2 className="edit-user-tittle">
                    Editar usuario
                </h2>
                <form onSubmit={handleSubmit}>
                    <input
                        type="text"
                        placeholder="nombre"
                        value={first_name}
                        onChange={(e)=>setFirstName(e.target.value)}
                        className="form-input"
                        autoComplete="Nombre"
                    />
                    <input
                        type="text"
                        placeholder="apellido"
                        value={last_name}
                        onChange={(e)=>setLastName(e.target.value)}
                        className="form-input"
                        autoComplete="Apellido"
                    />
                    <Button type="submit" className="edit-btn">Editar</Button>              
                </form>
            </div>
            )}
            {!isAutenticated && (
                <div className="not-autenticated-container">
                    <p className="not-autenticated-message"> Inicia sesion para poder editar tu usuario </p>
                    <Link to="/login"> Iniciar sesion </Link>
                </div>
            )}
        </div>
    )




}

export default EditUserPage