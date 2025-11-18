import logo from '../../assets/iconopagina.svg';
import { useAuth } from '../../context/AuthContext.jsx'; 
import './generalStyle.css';

const HeaderComponent = () => {
    const { user, isAuthenticated } = useAuth();
    console.log("Usuario en Header:", user); 

    return(
        <>
            <div className="header">
                <a href="/" className="header-link">
                    <img src={logo} alt="Logo" className="header-logo" />
                    <h1>Tenis-Plus</h1>
                </a>
                {isAuthenticated && user && (
                    <h3>Buenos dias, {user.first_name}</h3>
                )}
            </div>
        </>
    )
}

export default HeaderComponent;