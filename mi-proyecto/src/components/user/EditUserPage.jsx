import { edit }  from '../../services/apiUsers/editUser.js'
import { useAuth } from '../../context/AuthContext.jsx'
import { useState, useEffect } from "react"
import { useNavigate, Link, useParams } from 'react-router-dom'
import Button from '../button/Button.jsx'
import './userStyle.css'

const EditUserPage = () => {
    const { user, isAuthenticated, isAdmin } = useAuth();
    const { id } = useParams();
    const [first_name, setFirstName] = useState('');
    const [last_name, setLastName] = useState('');
    const navigate = useNavigate();

    useEffect(() =>{
        if (user && user.id === Number(id)) {
            setFirstName(user.first_name || '');
            setLastName(user.last_name || '');
        }
    },[user,id])

    const handleSubmit = async(e) =>{
        e.preventDefault();
        try{
            const response = await edit(id,{
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
            {(isAuthenticated || isAdmin) && (
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
            {(!isAuthenticated && !isAdmin)  && (
                <div className="not-autenticated-container">
                    <p className="not-autenticated-message"> Inicia sesion para poder editar tu usuario </p>
                    <Link to="/login"> Iniciar sesion </Link>
                </div>
            )}
        </div>
    )




}

export default EditUserPage