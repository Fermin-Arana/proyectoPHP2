import { useState, useEffect, useMemo } from "react";
import { bookingsByDay } from '../../services/apiBooking/bookingByDay.js';
import { bookingParticipant } from '../../services/apiBooking/bookingParticipant.js';
import { allCourts } from '../../services/apiCourts/allCourts.js';
import { useNavigate } from 'react-router-dom'
import DatePicker from 'react-datepicker';
import "react-datepicker/dist/react-datepicker.css";

const HORARIOS_POR_DIA = ["08:00", "08:30", "09:00", "09:30", "10:00", "10:30", "11:00", "11:30", "12:00", "12:30", "13:00", "13:30", "14:00", "14:30", "15:00", "15:30", "16:00", "16:30", "17:00", "17:30", "18:00", "18:30", "19:00", "19:30", "20:00", "20:30", "21:00", "21:30"];

const Home = () =>{
    const [bookings, setBookings] = useState([]);
    const [fechaHoy, setFechaHoy] = useState(new Date());
    const [participants, setParticipants] = useState([]);
    const [courts, setCourts] = useState([]);
    const navigate = useNavigate();

    useEffect(() => {
        const cargarDatos = async() => {
            try{
                const courtsResponse = await allCourts();
                if (courtsResponse.status ===  200){
                    setCourts(courtsResponse.message);
                } else {
                    console.error("error de la api cargando canchas");
                }
                const fechaString = fechaHoy.toISOString().split('T')[0];
                const bookingsResponse = await bookingsByDay(fechaString);
                if (bookingsResponse.status === 200){
                    setBookings(bookingsResponse.message);
                    const participantsPromises = await bookingsResponse.message.map(booking => bookingParticipant(booking.id));
                    const participantsResponse = await Promise.all(participantsPromises);
                    const participantsMap ={};
                    participantsResponse.forEach((res,index) =>{
                        if(res.status === 200){
                            const bookingId = bookingsResponse.message[index].id;
                            participantsMap[bookingId] = res.message;
                        }
                    });
                    setParticipants(participantsMap);
                } else {
                    console.error("error de la api cargando reservas");
                }
            } catch (error){
                console.error("ERROR", error.message);
                throw error;
            }
        };
        cargarDatos();
    },[fechaHoy]);

    const getBookingForSlot = useMemo(() =>{
        return(horario,courtId) =>{
            const fechaString = fechaHoy.toISOString().split('T')[0];
            const horarioDate = new Date(`${fechaString}T${horario}:00`);
            return bookings.find(booking =>{
                if(!booking || !booking.inicio || !booking.fin || booking.court_id !== courtId){
                    return false;
                }
                const inicioDate = new Date(booking.inicio.replace(' ','T'));
                const finDate = new Date(booking.fin.replace(' ', 'T'));
                return horarioDate >= inicioDate && horarioDate < finDate;
            });
        };
    }, [bookings, fechaHoy]);

    const formatParticipants = (bookingId) => {
        const participantsList = participants[bookingId];
        if(!participantsList || participantsList.length === 0) {
            return "Ocupado"
        }
        return participantsList.map(p => p.first_name).join(' / ');
    };

    const handleClick = (booking) =>{
        if(!booking){
            return;
        }
        navigate(`/update-booking/${booking.id}`);
    }

    return (
        <div className="grilla-horarios-container">
            <h1>
                Reservas del dia - {fechaHoy.toISOString().split('T')[0]}
            </h1>
            <div className="date-picker-container">
                <label>Cambiar fecha: </label>
                <DatePicker
                    selected={fechaHoy}
                    onChange={(date) => setFechaHoy(date)}
                    minDate={new Date()} //no permite seleccionar dias pasados
                />
            </div>
            <table className="tabla">
                <thead>
                    <tr>
                        <th>
                            Hora
                        </th>
                        {courts.map (court => (
                            <th key={court.id}>
                                {court.name}
                            </th>
                        ))}
                    </tr>
                </thead>
                <tbody>
                    {HORARIOS_POR_DIA.map(horario => (
                        <tr key={horario}>
                            <td className="horario-texto">
                                {horario}
                            </td>
                            {courts.map(court =>{
                                const reservaEncontrada = getBookingForSlot(horario,court.id);
                                return(
                                    <td 
                                    key={court.id}
                                    className={reservaEncontrada ? 'reserva-ocupada' : 'reserva-libre'}
                                    onClick={() => handleClick(reservaEncontrada)}>
                                        {reservaEncontrada ? formatParticipants(reservaEncontrada.id) : ''}
                                    </td>
                                )
                            })}
                        </tr>
                    ))}
                </tbody> 
            </table>
        </div>
    )
}


export default Home;