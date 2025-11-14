import { updateCourt } from '../../services/apiCourts/updateCourt'
import { infoCourt } from '../../services/apiCourts/infoCourt'
import { useAuth } from '../../context/AuthContext'
import { useState, useEffect } from 'react'
import { useNavigate, useParams } from 'react-router-dom' //useParams es para usar los parametros que se pasan en la url
import Button from '../button/Button.jsx'

const UpdateCourtPage = () => {
    const { isAdmin } = useAuth();
    const [ name, setName ] = useState('');
    const [ description, setDescription ] = useState('');
    const navigate = useNavigate();
    const { id } = useParams();

    useEffect(() =>{
        const infoCourtData = async() => {
            try{
                const response2 = await infoCourt(id);
                if(response2.status === 200){
                    setName(response2.message.name);
                    setDescription(response2.message.description);
                } else {
                    console.error("ERROR DE LA API AL RECOLECTAR DATOS", response2.message);
                }
            }catch(error){
                console.error("ERROR",error);
                throw error;
            }
        }
        infoCourtData();
    },[id,isAdmin])

    const handleSubmit = async(e) =>{
        e.preventDefault();
        try{
            const response = await updateCourt(id,{
                name: name,
                description: description
            });
            if(response.status === 200){
                console.log("Editado con exito!");
                navigate('/courts')
            } else {
                console.error("ERROR DE LA API", response.message);
            };
        }catch(error){
            console.error("ERROR", error);
            throw error;
        }
    };
    
    return ( 

        <div className="update-court-container">
            <div className="update-court-forms">
                {isAdmin && (
                <>
                <h2 className="update-court-tittle"> Actualizar cancha </h2>
                <form onSubmit={handleSubmit}>
                    <input
                        type="text"
                        placeholder="nombre"
                        value={name}
                        onChange={(e)=>setName(e.target.value)}
                        className="form-input"
                        autoComplete="nombre cancha"
                    />

                    <input
                        type="text"
                        placeholder="descripcion"
                        value={description}
                        onChange={(e)=>setDescription(e.target.value)}
                        className="form-input"
                        autoComplete="descripcion cancha"
                    />
                    <Button type="submit" className="update-btn">
                        Actualizar cancha
                    </Button>
                </form>
                </>
                )}
                {!isAdmin && (
                    <>
                        <p className="no-admin-message"> No tienes permisos para entrar aqui </p>
                    </>
                )}
            </div>
        </div>
    )
}

export default UpdateCourtPage;