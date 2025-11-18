
import { useEffect, useState } from 'react'
import { user } from '../../services/apiUsers/user'
import { useParams } from 'react-router-dom'
import './userStyle.css'

const InfoUserPage = () =>{
    const { id } = useParams();
    const [ realUser, setRealuser ] = useState(null);

    useEffect(() =>{
        const cargarUsuario = async() =>{
            try{
                const response = await user(id);
                if (response.status === 200){
                    console.log("get user exitoso");
                    setRealuser(response.message);
                } else {
                    console.error("error en el get user");
                }
            } catch(error){
                console.error("ERROR", error.message);
                throw error;
            }
        }
        cargarUsuario();
    },[id])

    if(!realUser){
        return <div className="loading-message">Cargando informacion...</div>
    }

    return (
        <div className="info-user-container">
            <h2 className="info-user-tittle">Informacion sobre el usuario con ID: {id}</h2>
            <ul className="info-user-list">
                <li>Nombre completo: {realUser.first_name} {realUser.last_name}</li>
                <li>Email: {realUser.email}</li>
                {realUser.admin === 1 && (
                    <li className="admin-badge">Es admin</li>
                )}
            </ul>
        </div>
    )
}

export default InfoUserPage;