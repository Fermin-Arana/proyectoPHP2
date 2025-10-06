 <?php
    Require_once __DIR__ . '/Conexion.php';
    Require_once __DIR__ . '/user.php';
    class Reserva{





        public function listarReservasPorDia(string $fecha): array
{
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

    public function eliminarReserva(int $reserva_id, int $usuario_id, int $es_admin): array
{
    $db = (new Conexion())->getDb();

    $stmt = $db->prepare('SELECT id, created_by FROM bookings WHERE id = :id');
    $stmt->execute([':id' => $reserva_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$result) return ['status' => 404, 'message' => 'La reserva no existe'];

    if (!$es_admin && (int)$result['created_by'] !== $usuario_id) {
        return ['status' => 403, 'message' => 'No tenés permisos para eliminar esta reserva'];
    }

    $stmt = $db->prepare('DELETE FROM booking_participants WHERE booking_id = :id');
    $stmt->execute([':id' => $reserva_id]);

    $stmt = $db->prepare('DELETE FROM bookings WHERE id = :id');
    $stmt->execute([':id' => $reserva_id]);

    return ['status' => 200, 'message' => 'Reserva eliminada correctamente'];
}


    public function puedeHacerReserva(int $id_user, DateTime $fecha):bool{
        
        $db = (new Conexion())->getDb();
        $stmt = $db->prepare('SELECT u.id, b.duration_blocks, b.booking_datetime
                      FROM users u 
                      JOIN booking_participants bp ON u.id = bp.user_id
                      JOIN bookings b ON bp.booking_id = b.id
                      WHERE (b.booking_datetime >= :day_start AND b.booking_datetime < :day_end)
                        AND u.id = :id');

        $day_start = $fecha . " 00:00:00"; 
        $day_end   = date('Y-m-d H:i:s', strtotime($fecha . ' +1 day'));

        $stmt->bindParam(':day_start', $day_start);
        $stmt->bindParam(':day_end', $day_end);
        $stmt->bindParam(':id', $id_user);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result){

            $bookingDateTime= $result['duration_blocks']*30;

            

        }



    }




}