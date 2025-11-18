import { deleteBooking } from '../../services/apiBooking/deleteBooking'
import { useNavigate, useParams } from 'react-router-dom'
import { useState } from 'react'
import Button from '../button/Button'

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
            {!error && (
                <>
                    <h2 className="delete-booking-tittle">Estas seguro que queres eliminar tu reserva?</h2>
                    <Button onClick={handleSubmit} className="delete-btn">Si, eliminar</Button>
                    <Button onClick={()=> navigate("/")} className="delete-btn">No, volver</Button>  
                </>       
            )}
            {error && <p className="error-message">{error}</p>}
        </div>
    )
}

export default DeleteBookingPage;