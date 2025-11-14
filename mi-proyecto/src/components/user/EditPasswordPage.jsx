import { edit }  from '../../services/apiUsers/editUser.js'
import { useAuth } from '../../context/AuthContext.jsx'
import { useState } from 'react'
import { useNavigate, Link } from 'react-router-dom'
import Button from '../button/Button.jsx'

const EditPasswordPage = () =>{
    const { user, isAuthenticated } = useAuth();
    const [password, setPassword] = useState(user.password);
    const [errors, setErrors] = useState([]);
    const navigate = useNavigate();

    const validar = () => {
        const errores = [];

        if(password.length < 8){
            errores.push("La contraseña debe tener al menos 8 caracteres.");
        }
        
        if(!/[A-Z]/.test(password)){
            errores.push("La contraseña debe tener al menos una letra mayuscula.");
        }

        if(!/[a-z]/.test(password)){
            errores.push("La contraseña debe tener al menos una letra minuscula.");
        }

        if(!/[0-9]/.test(password)){
            errores.push("La contraseña debe tener al menos un numero.");
        }

        if(!/[!@#$%^&*]/.test(password)){
            errores.push("La contraseña debe tener al menos un caracter especial.");
        }

        return errores;
    }

    const handleSubmit = async(e) => {
        e.preventdefault();
        setErrors([]);
        const erroresValidacion = validar();
        if(erroresValidacion.length > 0){
            setErrors(erroresValidacion);
            return;
        }
        try{
            const response = await edit(password);
            if(response.status === 200){
                console.log("Contraseña cambiada con exito!");
                navigate("/login");
            } else {
                console.error("Error de la API", response.message);
            }
        } catch(error){
            console.error("ERROR", error);
            throw error;
        }
    }

    return (
        <div className="edit-password-container">
            {isAuthenticated && (
                <div className="edit-password-form">
                    <h2> Cambiar contraseña </h2>
                    {errors.length>0 &&(
                        <ul className="error-messages">
                            {errors.map(error => (
                                <li key={error}>
                                    {error}
                                </li>
                            ))}
                        </ul>
                    )}
                    <form onSubmit={handleSubmit}>
                        <input
                        type="text"
                        placeholder="Contraseña"
                        value={password}
                        onChange={() => setPassword(e.target.value)}
                        className="form-input"
                        autoComplete="Contraseña">
                        </input>
                        <Button type="submit" className="edit-password-button"> Confirmar </Button>
                    </form>
                </div>
            )}
            {!isAuthenticated && (
                <div className="not-autenticated-container">
                    <p className="not-autenticated-message"> Inicia sesion para poder editar tu usuario </p>
                    <Link to="/login"> Iniciar sesion </Link>
                </div>
            )}
        </div>
    )
}

export default EditPasswordPage