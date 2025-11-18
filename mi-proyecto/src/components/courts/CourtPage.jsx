import { useState, useEffect } from 'react';
import { allCourts } from '../../services/apiCourts/allCourts.js';
import { useAuth } from '../../context/AuthContext.jsx'
import { useNavigate } from 'react-router-dom'
import Button from '../button/Button.jsx'
import './courtStyle.css';

const CourtPage = () => {

    const [courts, setCourts] = useState([]);
    const { isAdmin } = useAuth();
    const navigate = useNavigate()

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
                                <div className="court-actions">
                                    <Button onClick= {() => navigate(`/update-court/${court.id}`)}>
                                        Actualizar
                                    </Button>
                                    <Button onClick= {() => navigate(`/delete-court/${court.id}`)}> 
                                        Eliminar
                                    </Button>
                                </div>
                            </>
                        )}
                    </div>
                ))}
            </div>
        </div>
    )

}

export default CourtPage;