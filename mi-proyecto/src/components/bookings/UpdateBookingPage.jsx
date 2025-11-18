import Button from '../button/Button'
import { useNavigate, useParams } from 'react-router-dom' 
import { useAuth } from '../../context/AuthContext'
import { useState, useEffect } from 'react';
import { updateBookingParticipants } from '../../services/apiBooking/updateBookingParticipant'
import { searchUsers } from '../../services/apiUsers/searchUser'
import { bookingParticipant } from '../../services/apiBooking/bookingParticipant'
import './bookingStyle.css';


const UpdateBookingPage = () =>{

    const { user } = useAuth();
    const { id } = useParams();
    const navigate = useNavigate();
    const [ participants, setParticipants ] = useState([]);
    const [ userList, setUserlist ] = useState([]);
    const [ error, setError] = useState();

    useEffect(() => {
        const loadingData = async() =>{
            try{
                const allparticipants = await searchUsers('');
                if(allparticipants.status === 200){
                    setUserlist(allparticipants.message);
                }else {
                    console.error("error de la api en searchUsers");
                }
                const ogparticipants = await bookingParticipant(id);
                if (ogparticipants.status === 200){
                    const participantsId = ogparticipants.message.map(p => p.id).filter(pId => pId !== user.id);
                    setParticipants(participantsId);
                } else {
                    console.error("Error de la api en bookingParticipants");
                }
            } catch(error){
                console.error("ERROR", error.message);
                throw error;
            }
        };
        loadingData();
    },[id, user.id]); //se vuelve a ejecutar solo si cambia el id de la reserva o el id del usuario.

    const handleParticipantChange = (id) => {
        setParticipants(prevParticipants => {
            if (prevParticipants.includes(id)) {
                return prevParticipants.filter(pId => pId !== id);
            } else {
                return [...prevParticipants, id];
            }
        });
    };

    const handleSubmit = async(e) => {
        e.preventDefault();
        setError(null);
        if(participants.length  !== 1  && participants.length !== 3){
            setError("Debes seleccionar 1 participante (para 2 jugadores) o 3 participantes (para 4 jugadores).");
            return;
        }
        try{
            const response = await updateBookingParticipants(id, participants);
            if (response.status === 200){
                console.log("se actualizo correctamente");
                navigate("/");
            } else {
                setError(response.message);
            }
        }catch (error) {
            setError(error.message);
        }
    }

    return (
        <div className="update-booking-container">
            <form onSubmit={handleSubmit}>
                <h2>Modificar participantes</h2>
                {error && <p className="error-message">{error}</p>}
                <div className="form-container">
                    <label>Selecciona a tus nuevos compañeros: </label>
                    <div className="participants-checkbox-list">
                        {userList.map(u =>(
                            <div key={u.id} className="participant-checkbox">
                                <input
                                    type="checkbox"
                                    id={`user-${u.id}`}
                                    value={u.id}
                                    checked={participants.includes(u.id)}
                                    onChange={() => handleParticipantChange(u.id)}
                                />
                                <label htmlFor={`user-${u.id}`}> 
                                    {u.first_name} {u.last_name} ({u.email}) 
                                </label>
                            </div>
                        ))}
                    </div>
                </div>
                <div className="update-booking-actions">
                    <Button type="submit">Guardar cambios</Button>
                    <Button type="button" onClick={()=>navigate(`/delete-booking/${id}`)} className="btn-danger">Borrar reserva</Button>
                </div>
            </form>
        </div>
    )
}

export default UpdateBookingPage