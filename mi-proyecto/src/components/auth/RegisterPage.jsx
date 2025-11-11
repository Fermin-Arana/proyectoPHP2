import { useState } from 'react';
import { useAuth } from '../../context/AuthContext.jsx';
import { useNavigate } from 'react-router-dom';

const RegisterPage = () => {
    const [email,setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [firstName, setFirstName] = useState('');
    const [lastName, setLastName] = useState('');
    const [errors, setErrors] = useState([]); //para poner los errores
    const [success, setSuccess] = useState(null); //para poner el mensaje de exito
    const { register } = useAuth(); //uso el contexto para hacer el registrar
    const navigate = useNavigate(); //para redirigir despues de registrar

    const validar = () =>{
        const errores = [];
        if(!email.includes('@') || !email.includes('.')){
            errores.push("El email debe ser valido.");
        }

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

        if(firstName.trim()=== ''){
            errores.push("El nombre no puede estar vacio.");
        }

        if(lastName.trim()=== ''){
            errores.push("El apellido no puede estar vacio.");
        }

        if(password.trim()=== ''){
            errores.push("La contraseña no puede estar vacia.");
        }

        if(email.trim()=== ''){
            errores.push("El email no puede estar vacio.");
        }

        return errores;
    }

    const handleSubmit = async (e) => {
        e.preventDefault();
        setErrors([]);
        setSuccess(null);
        const erroresValidacion = validar();
            if(erroresValidacion.length > 0){
                setErrors(erroresValidacion);
                return;
            }
            try {
            const response = await register(email, password, firstName, lastName);
                if(response.status === 200){
                    setSuccess("Registro exitoso.");
                    navigate('/login');
                }

            } catch (error) {
                setErrors([error.message]);
            }
        };

    return (
        <div className="register-container">
            <div className="register-form">
                <h2 className="register-title">Registrarse</h2>
                {errors.length > 0 && (
                    <ul className="error-messages">
                        {errors.map(error => (
                            <li key={error}>
                                {error}
                            </li>
                        ))}
                    </ul>
                )}

                {success && (
                    <div className="success-container">
                        <p className="success-message">{success}</p>
                    </div>
                )}
                <form onSubmit={handleSubmit}>
                    <input
                        type="text"
                        placeholder="Nombre"
                        value={firstName}
                        onChange={(e) => setFirstName(e.target.value)}
                        className="form-input"
                        autoComplete="given-name"
                    />

                    <input
                        type="text"
                        placeholder="Apellido"
                        value={lastName}
                        onChange={(e) => setLastName(e.target.value)}
                        className="form-input"
                        autoComplete="family-name"
                    />

                    <input
                        type="email"
                        placeholder="Email"
                        value={email}
                        onChange={(e) => setEmail(e.target.value)}
                        className="form-input"
                        autoComplete="email"
                    />

                    <input
                        type="password"
                        placeholder="Contraseña"
                        value={password}
                        onChange={(e) => setPassword(e.target.value)}
                        className="form-input"
                        autoComplete="new-password"
                    />
                    
                    <button type="submit" className="register-btn">Registrarse </button>
                </form>
            </div>
        </div>
    )
}

export default RegisterPage;