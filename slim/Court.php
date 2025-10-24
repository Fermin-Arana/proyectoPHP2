 <?php
    class Court{
        
        public function crearCancha(string $nombreCancha, ?string $desc): array
{
    try {
        $db = (new Conexion())->getDb();

       
        $q = $db->prepare("SELECT 1 FROM courts WHERE name = :name LIMIT 1");
        $q->execute([':name' => $nombreCancha]);
        $existe = $q->fetch(PDO::FETCH_NUM);

        if ($existe) {
            return [
                'status'  => 409,
                'message' => 'Ya hay una cancha con ese nombre'
            ];
        }

      
        $ins = $db->prepare("INSERT INTO courts (name, description) VALUES (:name, :description)");
        $ins->execute([
            ':name'        => $nombreCancha,
            ':description' => $desc,
        ]);

        return [
            'status'  => 201, 
            'message' => [
                'id'          => (int)$db->lastInsertId(),
                'name'        => $nombreCancha,
                'description' => $desc
            ]
        ];
    } catch (PDOException $e) {
        
        if ($e->getCode() === '23000') {
            return ['status' => 409, 'message' => 'Ya existe una cancha con ese nombre (índice único)'];
        }
        return ['status' => 500, 'message' => 'Error al crear la cancha: ' . $e->getMessage()];
    }
}

        public function actualizarCancha(int $id, ?string $name, ?string $desc): array {
    $db = (new Conexion())->getDb();

    
    $q = $db->prepare("SELECT id FROM courts WHERE id = :id");
    $q->execute([':id' => $id]);
    if (!$q->fetch()) {
        return ['status' => 404, 'message' => 'La cancha no existe'];
    }

    
    if ($name !== null) {
        $name = trim($name);
        if ($name === '' || mb_strlen($name) > 100) {
            return ['status' => 400, 'message' => 'El nombre es inválido (vacío o >100)'];
        }

        
        $dup = $db->prepare("SELECT 1 FROM courts WHERE name = :name AND id <> :id");
        $dup->execute([':name' => $name, ':id' => $id]);
        if ($dup->fetch()) {
            return ['status' => 409, 'message' => 'Ya existe una cancha con ese nombre'];
        }
    }

    
    $sql = "UPDATE courts 
            SET name = COALESCE(:name, name), 
                description = COALESCE(:desc, description) 
            WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':name' => $name,
        ':desc' => $desc,
        ':id'   => $id
    ]);

    return [
        'status'  => 200,
        'message' => 'Cancha actualizada correctamente'
    ];
}

    public function getInfoCancha(int $id): array {
    $db = (new Conexion())->getDb();

    $sql = "SELECT id, name, description 
            FROM courts 
            WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    $court = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$court) {
        return [
            'status'  => 404,
            'message' => "No se encontró ninguna cancha con id = $id"
        ];
    }

    return [
        'status'  => 200,
        'message' => $court
    ];
}

    public function eliminarCancha(int $id): array
{
    try {
        $db = (new Conexion())->getDb();

       
        $stmt = $db->prepare("SELECT id FROM courts WHERE id = :id");
        $stmt->execute([':id' => $id]);
        if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
            return ['status' => 404, 'message' => 'La cancha no existe'];
        }

        
        $stmt = $db->prepare("SELECT 1 FROM bookings WHERE court_id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        if ($stmt->fetchColumn()) {
            return [
                'status'  => 409,
                'message' => 'No se puede eliminar la cancha: existen reservas asociadas'
            ];
        }

        
        $stmt = $db->prepare("DELETE FROM courts WHERE id = :id");
        $stmt->execute([':id' => $id]);

        if ($stmt->rowCount() > 0) {
            return ['status' => 200, 'message' => 'Cancha eliminada correctamente'];
        }

        return ['status' => 404, 'message' => 'La cancha no pudo ser eliminada'];

    } catch (Throwable $e) {
        return ['status' => 500, 'message' => 'Error al eliminar cancha: ' . $e->getMessage()];
    }
}



}

    
    

