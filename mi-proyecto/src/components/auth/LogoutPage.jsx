import {useAuth} from '../../context/AuthContext.jsx';
import { useNavigate } from 'react-router-dom'

const LogoutPage = () => {
    
    const { logout, isAuthenticated } = useAuth();
    const navigate = useNavigate();
    
    const handlerLogout = async(e) =>{
        e.preventDefault();
        try{
            const response = await logout();
            if (response.status === 200){
                navigate('/');
            }
        } catch(error){
            console.log("No se pudo cerrar sesion.", error);
        }
    }

    return (
        <div className="logout-container">
            {!isAuthenticated && (
            <div className="not-authenticated-container">
                <p className="not-authenticated-message"> No estas logueado! Inicia sesion aqui</p>
                <button onClick= { () => navigate('/login') } className="login-btn"> Iniciar sesion </button>
            </div>
            )}
            {isAuthenticated && (
                <div className="authenticated-container">
                    <p className="authenticated-message"> Estas seguro que quieres cerrar sesion?</p>
                    <button onClick= {handlerLogout} className="logout-btn"> Cerrar sesion </button>
                </div>
            )}
        </div>
    )

}

export default LogoutPage