
import { useAuth } from '../../context/AuthContext.jsx'
import { Link, useLocation } from 'react-router-dom'
import './generalStyle.css'

const NavBarComponent = () =>{
    const { isAdmin, isAuthenticated, user } = useAuth();
    const location = useLocation(); //lo uso para que si ya estoy en una pagina, no me la muestre en la barra de navegacion

    return (
        <div className="nav-bar-container">
            <ul className="nav-bar-links">
                {location.pathname !== '/' && (
                    <li className="nav-bar-link">
                        <Link to="/"> Home </Link>
                    </li>
                )}
                {location.pathname !== '/courts' && (
                    <li className="nav-bar-link">
                        <Link to="/courts"> Canchas </Link>
                    </li>
                )}
                {!isAuthenticated && (
                    <>
                        {location.pathname !== '/login' && (
                            <li className="nav-bar-link">
                                <Link to="/login"> Iniciar Sesion </Link>
                            </li>
                        )}
                        {location.pathname !== '/register' && (
                            <li className="nav-bar-link">
                                <Link to="/register"> Registrarse </Link>
                            </li>
                        )}
                    </>
                )}
                {isAuthenticated && (
                    <>
                        {location.pathname !== `/edituser/${user.id}` && (
                            <li className="nav-bar-link">
                                <Link to={`/edituser/${user.id}`}> Editar usuario </Link>
                            </li>
                        )}
                        {location.pathname !== '/editpassword' && (
                            <li className="nav-bar-link">
                                <Link to="/editpassword"> Editar contraseña </Link>
                            </li>
                        )}
                        {location.pathname !== '/create-booking' && (
                            <li className="nav-bar-link">
                                <Link to="/create-booking"> Crear reserva </Link>
                            </li>
                        )}
                        {isAdmin && (
                            <>
                                {location.pathname !== '/userlist' && (
                                    <li className="nav-bar-link">
                                        <Link to="/userlist"> Lista de usuarios </Link>
                                    </li>
                                )}
                            </>
                        )}
                        {location.pathname !== '/logout' && (
                            <li className="nav-bar-link">
                                <Link to="/logout"> Cerrar sesion </Link>
                            </li>
                        )}
                    </>
                )}
            </ul>
        </div>
    )
}

export default NavBarComponent