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
        $horas_a_sumar = intval($minutos_totales / 60); 
        $minutos_a_sumar = $minutos_totales % 60;
        
        $fecha_partes = explode(' ', $fecha_inicio); 
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
        
       
        if (count($participantes) < 2 || count($participantes) > 4) {
            return [
                'status' => 409,
                'message' => 'La reserva debe ser para 2 personas (single) o 4 personas (dobles)'
            ];
        }
        
        
        $validacion = $this->validarNuevaReserva($cancha_id, $fecha_inicio, $duracion_bloques, $participantes);
        if ($validacion['status'] !== 200) {
            return $validacion;
        }
        
        $db = (new Conexion())->getDb();
        
     
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

   public function modificarParticipantes(int $id_reserva, int $id_usuario_actual, array $companeros_nuevos): array
{
    $companeros_nuevos = array_values(array_unique(array_map('intval', $companeros_nuevos)));

    try {
        $bd = (new Conexion())->getDb();

        $sql_reserva = "
            SELECT id, court_id, created_by, booking_datetime, duration_blocks
            FROM bookings
            WHERE id = :id_reserva
            LIMIT 1
        ";
        $stmt_reserva = $bd->prepare($sql_reserva);
        $stmt_reserva->execute([':id_reserva' => $id_reserva]);
        $reserva = $stmt_reserva->fetch(PDO::FETCH_ASSOC);

        if (!$reserva) {
            return ['status' => 404, 'message' => 'Reserva no encontrada'];
        }

        $id_creador  = (int)$reserva['created_by'];
        $fecha_desde = $reserva['booking_datetime'];
        $minutos     = ((int)$reserva['duration_blocks']) * 30;
        $fecha_hasta = date('Y-m-d H:i:s', strtotime($fecha_desde . " + {$minutos} minutes"));

        if ($id_creador !== (int)$id_usuario_actual) {
            return ['status' => 403, 'message' => 'Solo el creador puede modificar los participantes'];
        }

        $companeros_nuevos = array_filter($companeros_nuevos, fn($id_u) => $id_u !== $id_creador);

        if (count($companeros_nuevos) > 3) {
            return ['status' => 400, 'message' => 'Máximo 3 compañeros'];
        }

        if (!empty($companeros_nuevos)) {
            $placeholders = implode(',', array_fill(0, count($companeros_nuevos), '?'));
            $sql_existencia = "SELECT id FROM users WHERE id IN ($placeholders)";
            $stmt_existencia = $bd->prepare($sql_existencia);
            $stmt_existencia->execute($companeros_nuevos);
            $ids_encontrados = array_map('intval', array_column($stmt_existencia->fetchAll(PDO::FETCH_ASSOC), 'id'));
            $ids_faltantes = array_values(array_diff($companeros_nuevos, $ids_encontrados));
            if (!empty($ids_faltantes)) {
                return ['status' => 400, 'message' => 'IDs de compañeros inexistentes: ' . implode(',', $ids_faltantes)];
            }
        }

        $sql_colision_creador = "
            SELECT 1
            FROM bookings b
            WHERE b.id <> :id_reserva
              AND b.created_by = :id_usuario
              AND b.booking_datetime < :fecha_hasta
              AND DATE_ADD(b.booking_datetime, INTERVAL b.duration_blocks*30 MINUTE) > :fecha_desde
            LIMIT 1
        ";

        $sql_colision_participante = "
            SELECT 1
            FROM bookings b
            JOIN booking_participants bp ON bp.booking_id = b.id
            WHERE b.id <> :id_reserva
              AND bp.user_id = :id_usuario
              AND b.booking_datetime < :fecha_hasta
              AND DATE_ADD(b.booking_datetime, INTERVAL b.duration_blocks*30 MINUTE) > :fecha_desde
            LIMIT 1
        ";

        $stmt_colision_creador = $bd->prepare($sql_colision_creador);
        $stmt_colision_participante = $bd->prepare($sql_colision_participante);

        foreach ($companeros_nuevos as $id_usuario_companero) {
            $stmt_colision_creador->execute([
                ':id_reserva'  => $id_reserva,
                ':id_usuario'  => $id_usuario_companero,
                ':fecha_desde' => $fecha_desde,
                ':fecha_hasta' => $fecha_hasta,
            ]);
            if ($stmt_colision_creador->fetchColumn()) {
                return ['status' => 409, 'message' => "El usuario {$id_usuario_companero} tiene una reserva solapada (como creador)"];
            }

            $stmt_colision_participante->execute([
                ':id_reserva'  => $id_reserva,
                ':id_usuario'  => $id_usuario_companero,
                ':fecha_desde' => $fecha_desde,
                ':fecha_hasta' => $fecha_hasta,
            ]);
            if ($stmt_colision_participante->fetchColumn()) {
                return ['status' => 409, 'message' => "El usuario {$id_usuario_companero} tiene una reserva solapada (como participante)"];
            }
        }

        $bd->beginTransaction();

        $sql_borrar = "
            DELETE FROM booking_participants
            WHERE booking_id = :id_reserva
              AND user_id <> :id_creador
        ";
        $stmt_borrar = $bd->prepare($sql_borrar);
        $stmt_borrar->execute([
            ':id_reserva' => $id_reserva,
            ':id_creador' => $id_creador
        ]);

        if (!empty($companeros_nuevos)) {
            $sql_insertar = "
                INSERT INTO booking_participants (booking_id, user_id)
                VALUES (:id_reserva, :id_usuario)
            ";
            $stmt_insertar = $bd->prepare($sql_insertar);

            foreach ($companeros_nuevos as $id_usuario_companero) {
                if ($id_usuario_companero === $id_creador) { continue; }
                $sql_existe = "
                    SELECT 1 FROM booking_participants
                    WHERE booking_id = :id_reserva AND user_id = :id_usuario
                    LIMIT 1
                ";
                $stmt_existe = $bd->prepare($sql_existe);
                $stmt_existe->execute([
                    ':id_reserva' => $id_reserva,
                    ':id_usuario' => $id_usuario_companero
                ]);
                if ($stmt_existe->fetchColumn()) {
                    continue;
                }
                $stmt_insertar->execute([
                    ':id_reserva' => $id_reserva,
                    ':id_usuario' => $id_usuario_companero
                ]);
            }
        }

        $bd->commit();

        return ['status' => 200, 'message' => 'Participantes actualizados correctamente'];

    } catch (Throwable $e) {
        if (isset($bd) && $bd->inTransaction()) {
            $bd->rollBack();
        }
        return ['status' => 500, 'message' => 'Error al modificar participantes: ' . $e->getMessage()];
    }
}




}