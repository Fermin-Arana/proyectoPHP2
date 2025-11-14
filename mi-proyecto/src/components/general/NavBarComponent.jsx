
import { useAuth } from '../../context/AuthContext.jsx'
import { Link } from 'react-router-dom'
const NavBarComponent = () =>{
    const { isAdmin, isAuthenticated, user } = useAuth();

    return (
        <div className="nav-bar-container">
            <ul className="nav-bar-links">
                <li className="nav-bar-link">
                    <Link to="/"> Home </Link>
                </li>
                <li className="nav-bar-link">
                    <Link to="/courts"> Canchas </Link>
                </li>
                {!isAuthenticated && (
                    <>
                        <li className="nav-bar-link">
                            <Link to="/login"> Iniciar Sesion </Link>
                        </li>
                        <li className="nav-bar-link">
                            <Link to="/register"> Registrarse </Link>
                        </li>
                    </>
                )}
                {isAuthenticated &&(
                    <>
                        <li className="nav-bar-link">
                            <Link to="/edituser"> Editar usuario </Link>
                        </li>
                        <li className="nav-bar-link">
                            <Link to="/editpassword"> Editar contraseña </Link>
                        </li>
                        <li className="nav-bar-link">
                            <Link to="/logout"> Cerrar sesion </Link>
                        </li>
                        {isAdmin && (
                            <>
                                <li className="nav-bar-link">
                                    <Link to="/userlist"> Lista de usuarios </Link>
                                </li>
                            </>
                        )}
                    </>
                )}
            </ul>
        </div>
    )


}

export default NavBarComponent