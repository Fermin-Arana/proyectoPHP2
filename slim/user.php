<?php

    class user{
        public function login($email, $password):array{
            $db = (new Conexion()->getDb()); //conecto la base de datos

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

                $expired = date("Y-m-d H:i:s", strtotime("+5 minutes")); //ponemos que expire en 5 minutos

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
            $db = (new Conexion()->getDb());

            $query = "UPDATE users SET token = NULL, expired = NULL WHERE id = :id";

            $stmt = $db ->prepare($query);

            $stmt->bindParam(':id', $id);

            $stmt->execute();

            $result= $stmt->fetch(PDO::FETCH_ASSOC);

            if($result && isset($result['id'])){
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

    }


?>