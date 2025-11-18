import { deleteBooking } from '../../services/apiBooking/deleteBooking'
import { useNavigate, useParams } from 'react-router-dom'
import { useState } from 'react'
import Button from '../button/Button'
import './bookingStyle.css';

const DeleteBookingPage = () =>{
    const { id } = useParams();
    const navigate = useNavigate();
    const [error, setError] = useState(null);

    const handleSubmit = async(e) => {
        e.preventDefault();
        try{
            const response = await deleteBooking(id);
            if (response.status === 200){
                console.log("Borrado con exito!");
                navigate("/");
            } else {
                console.error("error de la api en deleteBooking");
                setError(response.message);
            }
        } catch(error){ 
            console.error("ERROR", error.message);
            setError(error.message);
            throw error;
        }
    }
    return (
        <div className="delete-booking-container">
            <div className="delete-booking-card">
                {!error && (
                    <>
                        <h2 className="delete-booking-tittle">Estas seguro que queres eliminar tu reserva?</h2>
                        <p className="warning-text">Esta acción liberará la cancha para otros usuarios.</p>
                        <div className="delete-booking-actions">
                            <Button onClick={handleSubmit} className="btn-danger">Si, eliminar</Button>
                            <Button onClick={()=> navigate("/")} className="btn-secondary">No, volver</Button>  
                        </div>
                    </>       
                )}
                {error && <p className="error-message">{error}</p>}
            </div>
        </div>
    )
}

export default DeleteBookingPage;