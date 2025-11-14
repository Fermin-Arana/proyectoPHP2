import {useAuth} from '../../context/AuthContext.jsx';
import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import Button from '../button/Button.jsx'

const LoginPage = () =>{
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [errors, setErrors] = useState([]);
    const [success, setSuccess] = useState('');
    const { login } = useAuth();
    const navigate = useNavigate();

    const handleSubmit = async (e) => {
        e.preventDefault(); // Evita el comportamiento por defecto del formulario (como que se recargue la pagina)
        setErrors([]); // Resetea los errores antes de intentar el login
        try {
            const response = await login(email,password);
            console.log("Respuesta del login:", response);
            if(response.status === 200){
                setSuccess("Logueado con exito");
                navigate('/');
            }
        } catch (error){
            console.log("tu status no es 200.", error)
            if (error.response && error.response.data) {
                setErrors(error.response.data); 
            } else {
                setErrors([error.message]); 
            }
        }
    };

    return(
        <div className="login-container">
            <div className="login-form">
                <h2 className ="login-tittle"> Iniciar Sesion </h2>
                {errors.length > 0 && ( 
                    <div className="error-container"> 
                        <ul>
                            {errors.map((error, index) => ( 
                                <li key={index}>
                                    {error} 
                                </li>
                            ))}
                        </ul>
                    </div>
                )}
                {success && (
                    <div className="success-container">
                        <p className="success-message"> {success} </p>
                    </div>
                )}
                <form onSubmit={handleSubmit}>
                    <input
                        type="text"
                        placeholder="email"
                        value={email}
                        onChange={(e)=> setEmail(e.target.value)}
                        className="form-input"
                        autoComplete="email"
                    />

                    <input
                        type="password"
                        placeholder="password"
                        value={password}
                        onChange={(e)=>setPassword(e.target.value)}
                        className="form-input"
                        autoComplete="password"
                    />

                    <Button type="submit" className="login-btn"> Iniciar sesion </Button>
                </form>
            </div>
        </div>
    )


}

export default LoginPage;