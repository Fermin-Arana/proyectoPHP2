import { useState, useEffect } from 'react';
import { deleteCourt } from '../../services/apiCourts/deleteCourt.js'
import { allCourts } from '../../services/apiCourts/allCourts.js';
import { useAuth } from '../../context/AuthContext.jsx'
import { useNavigate } from 'react-router-dom'
import Button from '../button/Button.jsx'

const CourtPage = () => {

    const [courts, setCourts] = useState([]);
    const { isAdmin } = useAuth();
    const [ errors, setErrors] = useState([]);
    const navigate = useNavigate()

    const handleDelete = async (id) =>{
        if(!window.confirm("Estas seguro que queres borrar esta cancha? (ES PERMANENTE)"))
            return;
        try{
            const response2 = await deleteCourt(id);
            if (response2.status === 200){
                console.log("Eliminado con exito!");
                setCourts(currentCourts => currentCourts.filter(court => court.id !== id));
            } else {
                setErrors(prevErrors => ({...prevErrors, [id]: response.message || "No es posible eliminar esa cancha." }));
            }
        } catch(error){
            console.error("ERROOOOR", error);
            setErrors(prevErrors => ({...prevErrors, [id]: error.message || "Error al conectar con la API."}));
        }
    }

    useEffect(() => {
        const cargarCanchas = async () => {
            try {
                const response = await allCourts();
                if(response.status === 200){
                    setCourts(response.message);
                    console.log("Canchas cargadas:", response.message);
                } else {
                    console.error("Error al cargar canchas:", response.message);
                }
            } catch(error) {
                console.error("No se pudieron cargar las canchas, error de la API:", error);
            }
        }
        cargarCanchas();
    }, []);

    return (
        <div className="court-page-container">
            <h1>Canchas Disponibles</h1>
            <div className="courts-list">
                {courts.map((court) => (
                    <div className="one-court" key={court.id}>
                        <h2>{court.name}</h2>
                        <p>{court.description}</p>
                        {isAdmin && (
                            <>
                                <Button onClick= {() => navigate(`/update-court/${court.id}`)}>
                                    Actualizar
                                </Button>
                                <Button onClick= {() => handleDelete(court.id)}> 
                                    Eliminar
                                </Button>
                                {errors[court.id] &&(
                                    <p className="error-message">
                                        No se puede eliminar esta cancha porque ya tiene reservas!
                                    </p>
                                )}
                            </>
                        )}
                    </div>
                ))}
            </div>
        </div>
    )

}

export default CourtPage;