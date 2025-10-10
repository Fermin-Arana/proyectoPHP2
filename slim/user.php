<?php
    Require_once __DIR__ . '/Conexion.php';
    date_default_timezone_set('America/Argentina/Buenos_Aires'); // Cambia a tu zona horaria
    class user{
        public function login($email, $password):array{
            $db = (new Conexion())->getDb(); //conecto la base de datos

            $query = "SELECT id FROM users WHERE email = :email AND password = :password"; //hago la consulta

            $stmt = $db->prepare($query); //preparo la consulta

            $stmt->bindParam(':email', $email); //asigno $email a :email para la consulta
            $stmt->bindParam(':password', $password);
            
            $stmt->execute(); //hago la consulta

            $result = $stmt->fetch(PDO::FETCH_ASSOC); //asigno lo que devuelve a result, fetch hace que agarre la primera linea y fetch_assoc que devuelva un array asociativo, donde las claves son los nombres de las columnas de la bd. Si no hay nada devuelve "false" en result

            if($result && isset($result['id'])){
                $id = $result['id'];
                $token = $this->nuevoToken($email,$id);
                if($token){
                    return[
                        'status' => 200,
                        'message' =>[
                            'token' => $token,
                            'email' => $email,
                            'id' => $id
                        ]
                    ];
                } 
                else {
                    return [
                        'status' => 404,
                        'message' => "El usuario existe pero no se pudo generar un token"
                    ];
                }
            }
        
            return[
                'status' => 404,
                'message' => "El mail o la contraseña son incorrectos"
            ];

        }
        private function nuevoToken($email, $id): string {
            try {
                $db = (new Conexion())->getDb();

                $token = bin2hex(random_bytes(32)); // 64 caracteres hexadecimales

                $expired = date("Y-m-d H:i:s", strtotime("+5 minutes")); // Establece que expire en 5 minutos

                $query = "UPDATE users SET token = :token, expired = :expired WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->execute([
                    ':token'   => $token,
                    ':expired' => $expired,
                    ':id'      => $id
                ]);

                return $token;

            } catch (\Exception $e) {
                return 'Fallo la creacion del token';
            }
        }

        public function logout($id):array{
            $db = (new Conexion())->getDb();

            $query = "UPDATE users SET token = NULL, expired = NULL WHERE id = :id";

            $stmt = $db ->prepare($query);

            $stmt->bindParam(':id', $id);

            $result = $stmt->execute();

            if($result && $stmt->rowCount() > 0){
                return[
                    'status'=> 200,
                    'message'=> "Se cerro la sesion correctamente"
                ];
            }

            return [
                'status'=> 404,
                'message'=> "El usuario no existe"
            ];
        }

        public function createUser($email, $password, $first_name, $last_name):array{
            if (strpos($email, '@') === false) {
                return [
                    'status' => 400,
                    'message' => "El email debe contener el carácter '@'"
                ];
            }
            if(!$this->verificarExistenciaUser($email)){
                return[
                    'status'=> 409,
                    'message'=> "Ya existe un usuario con ese email"
                ];
            }

            if(strlen($password) < 8){
                return[
                    'status'=> 404,
                    'message'=> "La contraseña debe tener al menos 8 caracteres"
                ];
            }

            if(!preg_match('/[A-Z]/', $password)){
                return[
                    'status'=> 404,
                    'message'=> "La contraseña debe tener al menos una letra mayúscula"
                ];
            }

            if(!preg_match('/[0-9]/', $password)){
                return[
                    'status'=> 404,
                    'message'=> "La contraseña debe tener al menos un número"
                ];
            }

            if(!preg_match('/[^a-zA-Z0-9]/', $password)){
                return[
                    'status'=> 404,
                    'message'=> "La contraseña debe tener al menos un caracter especial"
                ];  
            }

            $db = (new Conexion())->getDb();

            $query = "INSERT INTO users (email, password, first_name, last_name) VALUES (:email, :password, :first_name, :last_name)";

            $stmt = $db->prepare($query);

            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':first_name', $first_name);
            $stmt->bindParam(':last_name', $last_name);

            $result = $stmt->execute();

            if($result && $stmt->rowCount() > 0){
                return[
                    'status'=> 200,
                    'message'=> "Usuario creado correctamente"
                ];
            } else {
                return [
                    'status'=> 404,
                    'message'=> "Error al crear el usuario"
                ];
            }
        }

        public function editarUsuario($id, $nombre = null, $apellido = null, $password = null): array {
            $email = $this->getEmailById($id);

            if (!$email) {
                return [
                    'status' => 404,
                    'message' => "El usuario no existe"
                ];
            }

            if (!$this->esElMismoUsuario($id, $email) && !$this->esAdmin($id)) {
                return [
                    'status' => 401,
                    'message' => "No tiene permisos para modificar este usuario"
                ];
            }

            if (!$this->isLoggedIn($id)) {
                return [
                    'status' => 403,
                    'message' => "No estás logueado o tu sesión ha expirado"
                ];
            }

            $db = (new Conexion())->getDb();
            $camposActualizar = [];
            $valores = [];

            if ($nombre !== null) {
                $camposActualizar[] = "first_name = :first_name";
                $valores[':first_name'] = $nombre;
            }
            if ($apellido !== null) {
                $camposActualizar[] = "last_name = :last_name";
                $valores[':last_name'] = $apellido;
            }
            if ($password !== null) {
                if (strlen($password) < 8) {
                    return [
                        'status' => 404,
                        'message' => "La contraseña debe tener al menos 8 caracteres"
                    ];
                }
                if (!preg_match('/[A-Z]/', $password)) {
                    return [
                        'status' => 404,
                        'message' => "La contraseña debe tener al menos una letra mayúscula"
                    ];
                }
                if (!preg_match('/[0-9]/', $password)) {
                    return [
                        'status' => 404,
                        'message' => "La contraseña debe tener al menos un número"
                    ];
                }
                if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
                    return [
                        'status' => 404,
                        'message' => "La contraseña debe tener al menos un carácter especial"
                    ];  
                }
                $camposActualizar[] = "password = :password";
                $valores[':password'] = $password;
            }

            if (empty($camposActualizar)) {
                return [
                    'status' => 400,
                    'message' => 'No se enviaron datos para modificar'
                ];
            }

            $query = "UPDATE users SET " . implode(', ', $camposActualizar) . " WHERE id = :id";
            $valores[':id'] = $id;

            try {
                $stmt = $db->prepare($query);
                $resultado = $stmt->execute($valores);

                if ($resultado) {
                    if ($stmt->rowCount() > 0) {
                        return [
                            'status' => 200,
                            'message' => 'Usuario actualizado correctamente'
                        ];
                    } else {
                        return [
                            'status' => 204,
                            'message' => 'No hubo cambios en la actualización'
                        ];
                    }
                } else {
                    return [
                        'status' => 404,
                        'message' => 'No se pudo actualizar el usuario'
                    ];
                }
            } catch (\Exception $e) {
                return [
                    'status' => 404,
                    'message' => 'Error en la actualización: ' . $e->getMessage()
                ];
            }
        }

        public function deleteUser($id, $currentId): array {

            $email = $this->getEmailById($id); 
            if (!$email) { 
                return [
                    'status' => 404,
                    'message' => "El usuario no existe"
                ];
            }

            if ($this->esAdmin($id)){
                return [
                    'status'=> 401,
                    'message'=> "No tiene permisos para eliminar este usuario"
                ];
            }

            if($id != $currentId && !$this->esAdmin($currentId)){
                return [
                    'status'=> 403,
                    'message'=> "Solo el propio usuario o un administrador puede eliminar"
                ];
            }

            if($this->tieneReservasActuales($id)){
                return [
                    'status'=> 400,
                    'message'=> "No se puede eliminar un usuario con reservas activas"
                ];
            }

            $db = (new Conexion())->getDb();

            $query = "DELETE FROM users WHERE id = :id";

            $stmt = $db->prepare($query);

            $stmt->bindParam(':id', $id);

            $resultado = $stmt->execute();

            if($resultado && $stmt->rowCount() > 0){
                return [
                    'status'=> 200,
                    'message'=> "Usuario eliminado correctamente"
                ];
            }

            return [
                'status'=> 404,
                'message'=> "No se pudo eliminar el usuario"
            ];
        }

        private function tieneReservasActuales($id): bool {
            $db = (new Conexion())->getDb();
            $ahora = date('Y-m-d H:i:s');

            $query = "SELECT id FROM bookings WHERE created_by = :id AND booking_datetime > :ahora LIMIT 1";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':ahora', $ahora);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result && isset($result['id'])) {
                return true;
            }

            $query = "SELECT bp.id FROM booking_participants bp JOIN bookings b ON bp.booking_id = b.id WHERE bp.user_id = :id AND b.booking_datetime > :ahora LIMIT 1";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':ahora', $ahora);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result && isset($result['id'])) {
                return true;
            }

            return false;
        }


        private function getEmailById($id): ?string {
            $db = (new Conexion())->getDb();

            $query = "SELECT email FROM users WHERE id = :id";

            $stmt = $db->prepare($query);

            $stmt->bindParam(':id', $id);

            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if($result && isset($result['email'])){
                return $result['email'];
            }else{
                return null;
            }
        }

        private function esElMismoUsuario($id,$email):bool{
            $db = (new Conexion())->getDb();

            $query = "SELECT id FROM users WHERE email = :email";

            $stmt = $db->prepare($query);

            $stmt->bindParam(':email', $email);

            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            error_log("Resultado de esElMismoUsuario: " . json_encode($result)); // Log de depuración

            if($result && isset($result['id']) && $result['id'] == $id){
                return true;
            }
            return false;         
        }

       private function esAdmin($id): bool {
        $db = (new Conexion())->getDb();
        $stmt = $db->prepare("SELECT is_admin FROM users WHERE id = :id");
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmt->execute();
        $val = $stmt->fetchColumn();
        return (int)$val === 1;
       }

        private function verificarExistenciaUser($email):bool{
            $db = (new Conexion())->getDb();

            $query = "SELECT id FROM users WHERE email = :email";

            $stmt = $db->prepare($query);

            $stmt->bindParam(':email', $email);

            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if($result && isset($result['id'])){
                return false;
            }
            return true;

        }

        public function getUserById($id, $currentId): array {
            $email = $this->getEmailById($id);

            if (!$email) {
                return [
                    'status' => 404,
                    'message' => "El usuario no existe"
                ];
            }

            if (!$this->isLoggedIn($currentId)) {
                return [
                    'status' => 403,
                    'message' => "No estás logueado o tu sesión ha expirado"
                ];
            }

            if(!$this->esAdmin($currentId)){
                if(!$this->esElMismoUsuario($currentId, $email)){
                    return [
                        'status' => 403,
                        'message' => "No tienes permiso para acceder a esta información"
                    ];
                }
            }


            $db = (new Conexion())->getDb();

            $query = "SELECT email, first_name, last_name, is_admin FROM users WHERE id = :id";

            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            error_log("Resultado de la consulta: " . json_encode($result)); // Log de depuración

            if ($result) {
                return [
                    'status' => 200,
                    'message' => $result
                ];
            } else {
                return [
                    'status' => 404,
                    'message' => "Usuario no encontrado"
                ];
            }
        }

        public function getAllUsers($search = ''): array {  
            $db = (new Conexion())->getDb();

            if ($search != '') {
                $search = "%$search%";
                $query = "SELECT email, first_name, last_name, is_admin FROM users WHERE is_admin = 0 AND (email LIKE :search OR first_name LIKE :search OR last_name LIKE :search)";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':search', $search);
            } else {
                $query = "SELECT email, first_name, last_name, is_admin FROM users WHERE is_admin = 0";
                $stmt = $db->prepare($query);
            }

            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($results) {
                return [
                    'status' => 200,
                    'message' => $results
                ];
            } else {
                return [
                    'status' => 404,
                    'message' => "No se encontraron usuarios"
                ];
            }
        }

        
        public function isLoggedIn($id): bool {
            $db = (new Conexion())->getDb();

            // Consulta para verificar el token y su expiración
            $query = "SELECT id FROM users WHERE id = :id AND expired > NOW()";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                return true; 
            } else {
                return false; 
            }

        }

        
        

    }

?>