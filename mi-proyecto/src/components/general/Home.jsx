import { useState, useEffect } from "react";
import { bookingsByDay} from '../../services/apiBooking/bookingByDay.js';

const HORARIOS_POR_DIA = ["08:00", "08:30", "09:00", "09:30", "10:00", "10:30", "11:00", "11:30", "12:00", "12:30", "13:00", "13:30", "14:00", "14:30", "15:00", "15:30", "16:00", "16:30", "17:00", "17:30", "18:00", "18:30", "19:00", "19:30", "20:00", "20:30", "21:00", "21:30"];

const Home = () =>{
    const [bookings, setBookings] = useState([]);
    const [fechaHoy, setFechaHoy] = useState(() => new Date().toISOString().split('T')[0]);

useEffect(() => {
        const cargarReservas = async () => {
            try {
                const response = await bookingsByDay(fechaHoy); 
                if (response.status === 200) {
                    setBookings(response.message);
                    console.log("Reservas actualizadas:", response.message);
                } else {
                    console.error("Error de API:", response.message);
                }
            } catch (error) {
                console.error("No se pudieron cargar las reservas:", error);
            }
        };
        cargarReservas();
        const intervalId = setInterval(cargarReservas, 30000); 

        return () => {
            clearInterval(intervalId);
        };
    }, [fechaHoy]); 

    return (
        <div className="grilla-horarios-container">
            {HORARIOS_POR_DIA.map((horario) => {
                const horarioDate = new Date(`${fechaHoy}T${horario}:00`);
                const reservaEncontrada = bookings.find(booking => {
                    if (!booking || !booking.inicio || !booking.fin) {
                        return false; 
                    }
                    const inicioDate = new Date(booking.inicio.replace(' ', 'T'));
                    const finDate = new Date(booking.fin.replace(' ', 'T'));
                    return horarioDate >= inicioDate && horarioDate < finDate;
                });

                return (
                    <div className="horario-slot" key={horario}>
                        <span className="horario-texto">{horario}</span>
                        {reservaEncontrada ? (
                            <div className="reserva-ocupada">
                                Ocupado ({reservaEncontrada.cancha})
                            </div>
                        ) : (
                            <div className="reserva-libre">
                                Libre
                            </div>
                        )}
                    </div>
                );
            })}
        </div>
    );
}


export default Home;