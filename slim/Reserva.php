<?php
    Require_once __DIR__ . '/Conexion.php';
    Require_once __DIR__ . '/user.php';
    class Reserva{
        public function listarReservasPorDia(string $fecha): array {
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
                return ['status' => 400, 'message' => 'date inválido (YYYY-MM-DD)'];
            }

            $db = (new Conexion())->getDb();

            $sql = "
                SELECT 
                    b.id,
                    b.court_id,
                    c.name        AS cancha,
                    b.created_by,
                    b.booking_datetime AS inicio,
                    DATE_ADD(b.booking_datetime, INTERVAL (b.duration_blocks*30) MINUTE) AS fin,
                    b.duration_blocks AS bloques
                FROM bookings b
                INNER JOIN courts c ON c.id = b.court_id
                WHERE DATE(b.booking_datetime) = :fecha
                ORDER BY c.name ASC, b.booking_datetime ASC
            ";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':fecha', $fecha);
            $stmt->execute();
            $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return ['status' => 200, 'message' => $filas];
        }

        public function eliminarReserva(int $reserva_id, int $usuario_id, int $es_admin): array {
            $db = (new Conexion())->getDb();

            $stmt = $db->prepare('SELECT id, created_by FROM bookings WHERE id = :id');
            $stmt->execute([':id' => $reserva_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$result) return ['status' => 404, 'message' => 'La reserva no existe'];

            if (!$es_admin && (int)$result['created_by'] !== $usuario_id) {
                return ['status' => 401, 'message' => 'No tenés permisos para eliminar esta reserva'];
            }

            $stmt = $db->prepare('DELETE FROM booking_participants WHERE booking_id = :id');
            $stmt->execute([':id' => $reserva_id]);

            $stmt = $db->prepare('DELETE FROM bookings WHERE id = :id');
            $stmt->execute([':id' => $reserva_id]);

            return ['status' => 200, 'message' => 'Reserva eliminada correctamente'];
        }


    public function puedeHacerReserva(int $id_user, string $fecha_inicio, int $duracion_bloques): bool {
        $db = (new Conexion())->getDb();

        $minutos_totales = $duracion_bloques * 30;
        $horas_a_sumar = intval($minutos_totales / 60); //convierte texto a integer
        $minutos_a_sumar = $minutos_totales % 60;
        
        $fecha_partes = explode(' ', $fecha_inicio); //separa fecha y hora
        $fecha = $fecha_partes[0];
        $hora_partes = explode(':', $fecha_partes[1]);
        $hora = intval($hora_partes[0]);
        $minuto = intval($hora_partes[1]);
        
        $minuto_final = $minuto + $minutos_a_sumar;
        $hora_final = $hora + $horas_a_sumar;
        
        if ($minuto_final >= 60) {
            $hora_final = $hora_final + 1;
            $minuto_final = $minuto_final - 60;
        }
        
        $fecha_fin = $fecha . ' ' . $hora_final . ':' . str_pad($minuto_final, 2, '0', STR_PAD_LEFT) . ':00'; // agrega ceros si es necesario
        
        $query = "SELECT b.id, b.booking_datetime, b.duration_blocks 
                  FROM bookings b 
                  JOIN booking_participants bp ON b.id = bp.booking_id 
                  WHERE bp.user_id = :user_id";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $id_user);
        $stmt->execute();
        $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $res = true;
        
        for ($i = 0; $i < count($reservas); $i++) {
            $inicio_existente = $reservas[$i]['booking_datetime'];
            $bloques_existente = $reservas[$i]['duration_blocks'];
            
            // Calcular fin de reserva existente
            $minutos_existente = $bloques_existente * 30;
            $horas_existente = intval($minutos_existente / 60);
            $minutos_resto = $minutos_existente % 60;
            
            $fecha_existente_partes = explode(' ', $inicio_existente);
            $fecha_existente = $fecha_existente_partes[0];
            $hora_existente_partes = explode(':', $fecha_existente_partes[1]);
            $hora_existente = intval($hora_existente_partes[0]);
            $minuto_existente = intval($hora_existente_partes[1]);
            
            $minuto_existente_final = $minuto_existente + $minutos_resto;
            $hora_existente_final = $hora_existente + $horas_existente;
            
            if ($minuto_existente_final >= 60) {
                $hora_existente_final = $hora_existente_final + 1;
                $minuto_existente_final = $minuto_existente_final - 60;
            }
            
            $fin_existente = $fecha_existente . ' ' . $hora_existente_final . ':' . str_pad($minuto_existente_final, 2, '0', STR_PAD_LEFT) . ':00';

            if ($fecha_inicio < $fin_existente && $inicio_existente < $fecha_fin) {
                $res = false; 
            }
        }
        
        return $res;
    }

    public function validarNuevaReserva(int $cancha_id, string $fecha_inicio, int $duracion_bloques, array $participantes): array {
        if ($duracion_bloques > 6) {
            return [
                'status' => 409,
                'message' => 'La reserva no puede exceder 6 bloques (3 horas)'
            ];
        }
        
        $minutos_totales = $duracion_bloques * 30;
        $horas_a_sumar = intval($minutos_totales / 60);
        $minutos_a_sumar = $minutos_totales % 60;
        
        $fecha_partes = explode(' ', $fecha_inicio);
        $hora_partes = explode(':', $fecha_partes[1]);
        $hora = intval($hora_partes[0]);
        $minuto = intval($hora_partes[1]);
        
        $minuto_final = $minuto + $minutos_a_sumar;
        $hora_final = $hora + $horas_a_sumar;
        
        if ($minuto_final >= 60) {
            $hora_final = $hora_final + 1;
            $minuto_final = $minuto_final - 60;
        }
        
        if ($hora_final > 22) {
            return [
                'status' => 409,
                'message' => 'La reserva no puede exceder las 22:00 horas'
            ];
        }

        if (!$this->estaLibreCancha($cancha_id, $fecha_inicio, $duracion_bloques)) {
            return [
                'status' => 409,
                'message' => 'El horario solicitado no está disponible en la cancha seleccionada'
            ];
        }
        
        for ($i = 0; $i < count($participantes); $i++) {
            $user_id = $participantes[$i];
            if (!$this->puedeHacerReserva($user_id, $fecha_inicio, $duracion_bloques)) {
                return [
                    'status' => 409,
                    'message' => "El usuario con ID $user_id ya tiene una reserva que se solapa"
                ];
            }
        }
        
        return [
            'status' => 200,
            'message' => 'La reserva puede ser creada'
        ];
    }

    private function estaLibreCancha(int $cancha_id, string $fecha_inicio, int $duracion_bloques): bool {
        $db = (new Conexion())->getDb();
        
        $query = "SELECT id, booking_datetime, duration_blocks FROM bookings WHERE court_id = :cancha_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':cancha_id', $cancha_id);
        $stmt->execute();
        $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $minutos_totales = $duracion_bloques * 30;
        $horas_a_sumar = intval($minutos_totales / 60);
        $minutos_a_sumar = $minutos_totales % 60;
        
        $fecha_partes = explode(' ', $fecha_inicio);
        $hora_partes = explode(':', $fecha_partes[1]);
        $hora = intval($hora_partes[0]);
        $minuto = intval($hora_partes[1]);
        
        $minuto_final = $minuto + $minutos_a_sumar;
        $hora_final = $hora + $horas_a_sumar;
        
        if ($minuto_final >= 60) {
            $hora_final = $hora_final + 1;
            $minuto_final = $minuto_final - 60;
        }
        
        $fecha_fin = $fecha_partes[0] . ' ' . $hora_final . ':' . str_pad($minuto_final, 2, '0', STR_PAD_LEFT) . ':00';

        $res = true;
        
        for ($i = 0; $i < count($reservas); $i++) {
            $inicio_existente = $reservas[$i]['booking_datetime'];
            $bloques_existente = $reservas[$i]['duration_blocks'];
            
            $minutos_existente = $bloques_existente * 30;
            $horas_existente = intval($minutos_existente / 60);
            $minutos_resto = $minutos_existente % 60;
            
            $fecha_existente_partes = explode(' ', $inicio_existente);
            $hora_existente_partes = explode(':', $fecha_existente_partes[1]);
            $hora_existente = intval($hora_existente_partes[0]);
            $minuto_existente = intval($hora_existente_partes[1]);
            
            $minuto_existente_final = $minuto_existente + $minutos_resto;
            $hora_existente_final = $hora_existente + $horas_existente;
            
            if ($minuto_existente_final >= 60) {
                $hora_existente_final = $hora_existente_final + 1;
                $minuto_existente_final = $minuto_existente_final - 60;
            }
            
            $fin_existente = $fecha_existente_partes[0] . ' ' . $hora_existente_final . ':' . str_pad($minuto_existente_final, 2, '0', STR_PAD_LEFT) . ':00';
            
            if ($fecha_inicio < $fin_existente && $inicio_existente < $fecha_fin) {
                $res = false;
            }
        }

        return $res;
    }

    public function crearReserva(int $cancha_id, int $usuario_creador, string $fecha_inicio, int $duracion_bloques, array $participantes): array {

        $usuario_ya_incluido = false;
        for ($i = 0; $i < count($participantes); $i++) {
            if ($participantes[$i] == $usuario_creador) {
                $usuario_ya_incluido = true;
                break;
            }
        }
        
        if (!$usuario_ya_incluido) {
            $participantes[] = $usuario_creador;
        }
        
        // Validar que sea single (2 personas) o dobles (4 personas)
        if (count($participantes) < 2 || count($participantes) > 4) {
            return [
                'status' => 409,
                'message' => 'La reserva debe ser para 2 personas (single) o 4 personas (dobles)'
            ];
        }
        
        // Validar todas las condiciones
        $validacion = $this->validarNuevaReserva($cancha_id, $fecha_inicio, $duracion_bloques, $participantes);
        if ($validacion['status'] !== 200) {
            return $validacion;
        }
        
        $db = (new Conexion())->getDb();
        
        // Crear la reserva
        $query = "INSERT INTO bookings (court_id, created_by, booking_datetime, duration_blocks) VALUES (:court_id, :created_by, :booking_datetime, :duration_blocks)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':court_id', $cancha_id);
        $stmt->bindParam(':created_by', $usuario_creador);
        $stmt->bindParam(':booking_datetime', $fecha_inicio);
        $stmt->bindParam(':duration_blocks', $duracion_bloques);
        
        $resultado = $stmt->execute();
        
        if (!$resultado) {
            return [
                'status' => 409,
                'message' => 'Error al crear la reserva'
            ];
        }
        
        $booking_id = $db->lastInsertId();
        
        // Agregar participantes
        $query2 = "INSERT INTO booking_participants (booking_id, user_id) VALUES (:booking_id, :user_id)";
        $stmt2 = $db->prepare($query2);
        
        for ($i = 0; $i < count($participantes); $i++) {
            $user_id = $participantes[$i];
            $stmt2->bindParam(':booking_id', $booking_id);
            $stmt2->bindParam(':user_id', $user_id);
            $stmt2->execute();
        }
        
        return [
            'status' => 200,
            'message' => 'Reserva creada correctamente',
            'booking_id' => $booking_id
        ];
    }
}