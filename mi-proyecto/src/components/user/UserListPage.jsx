import { searchUsers } from '../../services/apiUsers/searchUser.js'
import { useState, useEffect } from "react"
import {useAuth} from '../../context/AuthContext.jsx';

const UserList = () => {
    const [users, setUsers] = useState([]);
    const { isAdmin } = useAuth();

    useEffect(()=>{
        const cargarUsuarios = async () => {
            try{
                const response = await searchUsers('');
                if (response.status === 200){
                    setUsers(response.message);
                    console.log("usuarios cargados", response.message);
                } else{
                    console.error("error de la API", response.message);
                }
            }catch(error){
                console.error("no se pudieron cargar los usuarios", error)
                throw error;
            }
        };
        cargarUsuarios();
        const intervalId = setInterval(cargarUsuarios, 30000);
        return () =>{
            clearInterval(intervalId);
        }
    }, []);

    return (
        <div className="users-list-container">
            <h1 className="users-list-tittle"> Lista de usuarios </h1>
            {isAdmin && (
                <div className="users-list"> 
                {users.map((user,index) => (
                    <li key={index}>
                        ID: 
                        {user.id}
                        {' '}
                        EMAIL: 
                        {user.email}
                    </li>
                ))}
                </div>
            )}
            {!isAdmin && (
                <div className="no-admin-container">
                    <p>
                        No tienes permisos para acceder a esta pagina.
                    </p>
                </div>
            )}
        </div>
    )

}

export default UserList