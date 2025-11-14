import { useState, useEffect, useMemo } from 'react'
import { useAuth } from '../../context/AuthContext' 
import { useNavigate } from 'react-router-dom'
import { bookingsByDay} from '../../services/apiBooking/bookingByDay.js';
import { allCourts} from '../../services/apiCourts/allCourts.js';
import { searchUsers } from '../../services/apiUsers/searchUser.js';
import { createBooking } from '../../services/apiBooking/createBooking.js';
import DatePicker from "react-datepicker";
import "react-datepicker/dist/react-datepicker.css";
import Button from '../button/Button.jsx'

const HORARIOS_POR_DIA = ["08:00", "08:30", "09:00", "09:30", "10:00", "10:30", "11:00", "11:30", "12:00", "12:30", "13:00", "13:30", "14:00", "14:30", "15:00", "15:30", "16:00", "16:30", "17:00", "17:30", "18:00", "18:30", "19:00", "19:30", "20:00", "20:30", "21:00", "21:30"];

const CreateBookingPage = () => {
    const { user, isAuthenticated } = useAuth();
    const navigate = useNavigate();
    const [allCourtsList, setAllCourtsList] = useState([]);
    const [allUsersList, setAllUsersList] = useState([]);
    const [bookings, setBookings] = useState([]);
    const [fechaSeleccionada, setFechaSeleccionada] = useState(new Date());
    const [courtId, setCourtId] = useState(''); 
    const [horarioInicio, setHorarioInicio] = useState(null); 
    const [duracion, setDuracion] = useState(1); 
    const [participants, setParticipants] = useState([]); 
    const [loadingBookings, setLoadingBookings] = useState(false);
    const [error, setError] = useState(null);
    const [success, setSuccess] = useState(null);

    useEffect(() =>{ //cargar datos como canchas y usuarios que no cambian.
        const loadInitialData = async() => {
            try{
                const allcourts = await allCourts();
                if (allcourts.status === 200){
                    setAllCourtsList(allcourts.message);
                }
                const allusers = await searchUsers('');
                if(allusers.status === 200){
                    setAllUsersList(allusers.message.filter(u => u.id !== user.id))
                }
            } catch(error) {
                setError("Error al cargar los datos iniciales" + error.message)
                throw error;
            }
        };

        if (isAuthenticated){
            loadInitialData();
        }
    },[isAuthenticated,user.id]);

    useEffect(() =>{
        const cargarReservasPorDia = async () => {
            setLoadingBookings(true);
            setError(null);
            setHorarioInicio(null);
            try{
                const fechaString = fechaSeleccionada.toISOString().split('T')[0];
                const response = await bookingsByDay(fechaString);
                if (response.status === 200){
                    setBookings(response.message);
                } else {
                    setError(response.message);
                }
            } catch(error){
                setError(error.message);
            } finally {
                setLoadingBookings(false);
            }
        };
        cargarReservasPorDia();
    }, [fechaSeleccionada]);

    const timeSlots = useMemo(() => {
        const fechaString = fechaSeleccionada.toISOString().split('T')[0];
        return HORARIOS_POR_DIA.map(horario => {
        const horarioDate = new Date(`${fechaString}T${horario}:00`);
        const reservaEncontrada = bookings.find(booking => {
                        if (!booking || !booking.inicio || !booking.fin) {
                            return false;
                        }
                        const inicioDate = new Date(booking.inicio.replace(' ', 'T'));
                        const finDate = new Date(booking.fin.replace(' ', 'T'));
                        return horarioDate >= inicioDate && horarioDate < finDate;
                    });

                    // 3. Devolvemos el objeto correcto
                    return {
                        time: horario,
                        isOccupied: !!reservaEncontrada // true si se encontró, false si no
                    };
                });
            }, [bookings, fechaSeleccionada]);

    const handleSubmit = async(e) =>{
        e.preventDefault();
        setError(null);
        setSuccess(null);

        if(!horarioInicio){
            setError("No seleccionaste hora de inicio");
            return;
        }

        if(!courtId){
            setError("No seleccionaste cancha");
            return;
        }

        const fechaISO = fechaSeleccionada.toISOString().split('T')[0];
        const bookingDateTime = `${fechaISO} ${horarioInicio}:00`;
        
        try{
            const response = await createBooking({
                court_id: Number(courtId),
                booking_datetime: bookingDateTime,
                duration_blocks: Number(duracion),
                participants: participants
            })
            if (response.status === 200){
                setSuccess("Reserva creada con exito");
                setHorarioInicio(null);
                setParticipants([]);
                const fechaString = fechaSeleccionada.toISOString().split('T')[0];
                const res = await bookingsByDay(fechaString);
                setBookings(res.message);
            } else {
                setError(response.message);
            }
        } catch(error){
            setError(error.message);
            throw error;
        }
    };

    return (
        <div className="create-booking-container">
            {!isAuthenticated && (
                <>
                    <p className="not-authenticated-message">
                        Debes iniciar sesion para realizar una reserva
                    </p>
                    <Link to="/login"> Iniciar Sesion </Link>
                </>
            )}
            {isAuthenticated && (
                <>
                    <form onSubmit={handleSubmit}>
                        <h2>Realizar una reserva</h2>
                        <div className="form-container">
                            <label>Cancha: </label>
                            <select //te deja elegir dentro de sus opciones, option seria cada opcion
                                value={courtId} 
                                onChange={(e) => setCourtId(e.target.value)} required>
                                <option value="" disabled> Selecciona una cancha </option>
                                {allCourtsList.map(court =>(
                                    <option key={court.id} value={court.id}>
                                        {court.name}
                                    </option>
                                ))}
                            </select>
                        </div>
                        <div className="form-container">
                            <label>Fecha: </label>
                            <DatePicker 
                                selected={fechaSeleccionada}
                                onChange={(date) => setFechaSeleccionada(date)}
                                dateFormat="yyyy-MM-dd"
                                minDate={new Date()}
                            />
                        </div>
                        <div className="form-container">
                            <label>Hora inicio:</label>
                            {loadingBookings && (
                                <p>Cargando horarios...</p>
                            )}
                            {!loadingBookings && (
                                <div className="time-slots-container">
                                    {timeSlots.map(slot => (
                                        <Button
                                            key={slot.time}
                                            type="button"
                                            onClick={() => setHorarioInicio(slot.time)}
                                            disabled={slot.isOccupied}
                                            className={slot.time === horarioInicio ? 'selected' : ''}
                                        > 
                                            {slot.time}
                                        </Button>
                                    ))}
                                </div>
                            )}
                        </div>
                        <div className="form-container">
                            <label>Duracion: </label>
                            <select value={duracion} onChange={(e) => setDuracion(Number(e.target.value))}>
                                <option value={1}>30 min (1 bloque)</option>
                                <option value={2}>1 hr (2 bloque)</option>
                                <option value={3}>1.5 hr (3 bloque)</option>
                                <option value={4}>2 hr (4 bloque)</option>
                                <option value={5}>2.5 hr (5 bloque)</option>
                                <option value={6}>3 hr (6 bloque)</option>
                            </select>
                            {console.log(duracion)}
                        </div>
                        <div className="form-container">
                            <label>Participantes (sin contarte)</label>
                            <select
                                multiple={true}
                                value={participants}
                                onChange={(e) => {
                                    const selectedIds = Array.from(e.target.selectedOptions, option => Number(option.value));
                                    setParticipants(selectedIds);
                                }}
                            >
                                {allUsersList.map(u =>(
                                    <option key={u.id} value={u.id}>
                                        {u.first_name} {u.last_name} ({u.email})
                                    </option>
                                ))}
                            </select>
                            {console.log(participants)}
                        </div>
                        <Button type="submit" variant="primary">
                            Confirmar reserva
                        </Button>
                        {error && <p className="error-message">{error}</p>}
                        {success && <p className="success-message">{success} </p>}
                    </form>
                </>
            )}
        </div>
    )



}

export default CreateBookingPage;