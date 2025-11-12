import { createContext, useState, useContext, useEffect, useMemo } from "react";
import { login as loginService } from '../services/apiUsers/login.js';
import { registerUser as registerService } from '../services/apiUsers/registerUser.js';
import { logout as logoutService } from '../services/apiUsers/logout.js';

const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
    const [user, setUser] = useState(null);
    const [token, setToken] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const storedToken = localStorage.getItem("token");
        const storedUser = localStorage.getItem("user");

        if (storedToken && storedUser) {
            try {
                setToken(storedToken);
                setUser(JSON.parse(storedUser));
            } catch (error) {
                console.error("Error al restaurar sesión:", error);
                localStorage.removeItem("token");
                localStorage.removeItem("user");
            }
        }
        setLoading(false);
    }, []);

    const login = async (email, password) => {
        try {
            const responseData = await loginService(email, password);
            console.log("Respuesta de loginService (en el TRY):", responseData);
            const message = responseData?.message;
            if (responseData.status !== 200 || !message?.token) {
                console.error("Status no es 200 o no se encontró 'message.token'", responseData);
                throw new Error('Respuesta inválida del servidor (sin token o status incorrecto)');
            }
            const token = message.token;
            const user = {
                id: message.id,
                email: message.email,
                is_admin: message.is_admin,
                first_name: message.first_name,
                last_name: message.last_name
            };
            localStorage.setItem('token', token);
            localStorage.setItem('user', JSON.stringify(user));
            setToken(token);
            setUser(user);
            return responseData;

        } catch (error) {
            console.error("Error COMPLETO del CATCH:", error);
            let errorMessage = 'Error durante el login';
            if (error.response && error.response.data) {
                errorMessage = error.response.data.message || error.response.data.error || JSON.stringify(error.response.data);
            } 
            else if (error.message) {
                errorMessage = error.message;
            }

            localStorage.removeItem('token');
            localStorage.removeItem('user');
            setToken(null);
            setUser(null);
            
            throw new Error(errorMessage);
        }
    };

    const register = async (email, password, firstName, lastName) => {
        try {
            const apiData = {
                email,
                password,
                first_name: firstName,
                last_name: lastName
            };
            const response = await registerService(apiData);
            return response;
        } catch (error) {
            const mensajeDelBackend = error.response?.data?.message;
            throw new Error(mensajeDelBackend || error.message || 'Error durante el registro');
        }
    };

    const logout = async () => {
        try {
            await logoutService();
        } catch (error) {
            console.error("Error al hacer logout en servidor:", error);
        } finally {
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            setToken(null);
            setUser(null);
        }
    };

    const value = useMemo(() => ({
        user,
        token,
        loading,
        login,
        register,
        logout,
        isAuthenticated: !!token,
        isAdmin: !!user?.is_admin
    }), [user, token, loading]);

    return (
        <AuthContext.Provider value={value}>
            {!loading && children}
        </AuthContext.Provider>
    );
};

export const useAuth = () => {
    const context = useContext(AuthContext);
    if (!context) {
        throw new Error('useAuth debe usarse dentro de un AuthProvider');
    }
    return context;
};