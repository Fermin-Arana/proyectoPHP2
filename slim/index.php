<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/Conexion.php';
require_once __DIR__ . '/user.php';
require_once __DIR__ . '/Court.php';
require_once __DIR__ . '/Reserva.php';
require_once __DIR__ . '/AuthMiddleware.php';
require_once __DIR__ . '/AdminMiddleware.php';

$app = AppFactory::create();

$app->setBasePath('/slim');
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);
$app->addBodyParsingMiddleware(); 


$app->options('/{routes:.+}', function (Request $request, Response $response) {
    return $response;
});
$app->add(function (Request $request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'OPTIONS, GET, POST, PUT, PATCH, DELETE')
        ->withHeader('Content-Type', 'application/json');
});


$auth  = new \AuthMiddleware($app->getResponseFactory());
$admin = new \AdminMiddleware($app->getResponseFactory());

$app->get('/', function ($req, $res) {
    $res->getBody()->write(json_encode(['ok' => true]));
    return $res->withHeader('Content-Type', 'application/json');
});



/* ================== AUTH ================== */
$app->post('/login', function (Request $request, Response $response) {
    $data = $request->getParsedBody() ?: [];

    $email = trim((string)($data['email'] ?? ''));
    $password = (string)($data['password'] ?? '');

    if ($email === '' || $password === '') {
        $response->getBody()->write(json_encode([
            'status'  => 400,
            'message' => 'Los campos email y password son obligatorios'
        ]));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    if (strpos($email, '@') === false) {
        $response->getBody()->write(json_encode([
            'status'  => 400,
            'message' => 'El email no tiene un formato válido'
        ]));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    $usr = new user();
    $result = $usr->login($email, $password);

    $response->getBody()->write(json_encode([
        'status'  => $result['status'],
        'message' => $result['message']
    ]));
    return $response
        ->withStatus((int)$result['status'])
        ->withHeader('Content-Type', 'application/json');
}); //REVISADOOOOOOOOOOO




$app->post('/logout', function (Request $request, Response $response) {
    $user = $request->getAttribute('user'); 
    if (!$user) {
        $response->getBody()->write(json_encode(['error' => 'No autenticado']));
        return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
    }

    $usr = new user();
    $result = $usr->logout((int)$user['id']);

    $response->getBody()->write(json_encode([
        'status'  => $result['status'],
        'message' => $result['message']
    ]));
    return $response
        ->withStatus((int)$result['status'])
        ->withHeader('Content-Type', 'application/json');
})->add($auth);



/* ================ USUARIOS ==================== */
$app->post('/user', function (Request $request, Response $response) {
    $data = $request->getParsedBody() ?: [];

    $email = trim((string)($data['email'] ?? ''));
    $password = (string)($data['password'] ?? '');
    $firstName = trim((string)($data['first_name'] ?? ''));
    $lastName = trim((string)($data['last_name'] ?? ''));

    if ($email === '' || $password === '' || $firstName === '' || $lastName === '') {
        $response->getBody()->write(json_encode([
            'status' => 400,
            'message' => 'Los campos email, password, first_name y last_name son obligatorios'
        ]));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    if (strpos($email, '@') === false) {
        $response->getBody()->write(json_encode([
            'status' => 400,
            'message' => 'El email no tiene un formato válido'
        ]));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    $usr = new user();
    $result = $usr->createUser($email, $password, $firstName, $lastName);

    $response->getBody()->write(json_encode([
        'status'  => $result['status'],
        'message' => $result['message']
    ]));
    return $response
        ->withStatus((int)$result['status'])
        ->withHeader('Content-Type', 'application/json');
}); 




$app->patch('/user/{id}', function (Request $request, Response $response, array $args) {
    $id = (int)$args['id'];
    $data = $request->getParsedBody() ?: [];

   
    $currentUser = $request->getAttribute('user');
    if (!$currentUser || !isset($currentUser['id'])) {
        $response->getBody()->write(json_encode([
            'status' => 401,
            'message' => 'Usuario no autenticado'
        ]));
        return $response
            ->withStatus(401)
            ->withHeader('Content-Type', 'application/json');
    }

    $currentId = (int)$currentUser['id'];

    $nombre   = $data['first_name'] ?? null;
    $apellido = $data['last_name']  ?? null;
    $password = $data['password']   ?? null;

    $usr = new user();
    $result = $usr->editarUsuario($id, $currentId, $nombre, $apellido, $password);

    $response->getBody()->write(json_encode([
        'status'  => $result['status'],
        'message' => $result['message']
    ]));
    return $response
        ->withStatus((int)$result['status'])
        ->withHeader('Content-Type', 'application/json');
})->add($auth);



$app->delete('/user/{id}', function (Request $request, Response $response, array $args) {
    $id = (int)$args['id'];

    $current = $request->getAttribute('user'); 
    if (!$current || !isset($current['id'])) {
        $response->getBody()->write(json_encode(['status' => 401, 'message' => 'No autenticado']));
        return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
    }

    $currentId = (int)$current['id'];

    $usr = new user(); 
    $result = $usr->deleteUser($id, $currentId);

    $response->getBody()->write(json_encode([
        'status'  => $result['status'],
        'message' => $result['message']
    ]));
    return $response
        ->withStatus((int)$result['status'])
        ->withHeader('Content-Type', 'application/json');
})->add($auth); 



$app->get('/user/{id}', function (Request $request, Response $response, array $args) {
    $id = (int)$args['id'];

   
    $currentUser = $request->getAttribute('user');
    if (!$currentUser || !isset($currentUser['id'])) {
        $response->getBody()->write(json_encode([
            'status' => 401,
            'message' => 'Usuario no autenticado'
        ]));
        return $response
            ->withStatus(401)
            ->withHeader('Content-Type', 'application/json');
    }

    $currentId = (int)$currentUser['id'];

    $usr = new user();
    $result = $usr->getUserById($id, $currentId);

    $response->getBody()->write(json_encode([
        'status'  => $result['status'],
        'message' => $result['message']
    ]));
    return $response
        ->withStatus((int)$result['status'])
        ->withHeader('Content-Type', 'application/json');
})->add($auth); //revisado por el canguro



$app->get('/users', function (Request $request, Response $response) {
    $params = $request->getQueryParams();
    $search = $params['search'] ?? '';

    $usr = new user();
    $result = $usr->getAllUsers($search);

    $response->getBody()->write(json_encode([
        'status'  => $result['status'],
        'message' => $result['message']
    ]));
    return $response
        ->withStatus((int)$result['status'])
        ->withHeader('Content-Type', 'application/json');
});

//funca

/* ================ CANCHAS =================== */

$app->post('/court', function (Request $request, Response $response) {
    $data = $request->getParsedBody() ?: [];

    if (isset($data['id']) || isset($data['user_id'])) {
        $response->getBody()->write(json_encode(['error' => 'No envíes id/user_id en la creación']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    $name = trim($data['name'] ?? '');
    $desc = $data['description'] ?? null;

    if ($name === '' || mb_strlen($name) > 100) {
        $response->getBody()->write(json_encode(['error' => 'name es requerido (≤100 chars)']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    $court = new Court();
    $result = $court->crearCancha($name, $desc);

    $response->getBody()->write(json_encode([
        'status'  => $result['status'],
        'message' => $result['message']
    ]));
    return $response
        ->withStatus((int)$result['status'])
        ->withHeader('Content-Type', 'application/json');
})->add($admin)->add($auth);


$app->put('/court/{id}', function (Request $request, Response $response, array $args) {
    $id   = (int)$args['id'];
    $data = $request->getParsedBody();

    if (!is_array($data)) {
        $response->getBody()->write(json_encode([
            'status'  => 400,
            'message' => 'Body inválido'
        ]));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    $name = array_key_exists('name', $data) ? trim((string)$data['name']) : null;
    $desc = array_key_exists('description', $data) ? $data['description'] : null;

   
    if ($name === null && $desc === null) {
        $response->getBody()->write(json_encode([
            'status'  => 400,
            'message' => 'No se enviaron campos para actualizar'
        ]));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    
    if ($name !== null && mb_strlen($name) > 100) {
        $response->getBody()->write(json_encode([
            'status'  => 400,
            'message' => 'name debe tener 100 caracteres o menos'
        ]));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    $court  = new Court();
    $result = $court->actualizarCancha($id, $name, $desc);

    $response->getBody()->write(json_encode([
        'status'  => $result['status'],
        'message' => $result['message']
    ]));
    return $response
        ->withStatus((int)$result['status'])
        ->withHeader('Content-Type', 'application/json');
})->add($admin)->add($auth); //REVISADO ANTES


$app->get('/court/{id}', function (Request $request, Response $response, array $args) {
    $id   = (int)$args['id'];

    $court = new Court();
    
    $result = $court->getInfoCancha($id);

    $response->getBody()->write(json_encode([
        'status'  => $result['status'],
        'message' => $result['message']
    ]));
    return $response
        ->withStatus((int)$result['status'])
        ->withHeader('Content-Type', 'application/json');
});


$app->delete('/court/{id}', function (Request $request, Response $response, array $args) {
    $id    = (int)$args['id'];
    $court = new Court();

    $result = $court->eliminarCancha($id);

    $response->getBody()->write(json_encode([
        'status'  => $result['status'],
        'message' => $result['message']
    ]));
    return $response
        ->withStatus((int)$result['status'])
        ->withHeader('Content-Type', 'application/json');
})->add($admin)->add($auth); //revisado por el canguro

$app->get('/courts', function (Request $request, Response $response) {
    $court = new Court();
    $result = $court->listarCanchas();

    $response->getBody()->write(json_encode([
        'status'  => $result['status'],
        'message' => $result['message']
    ]));
    return $response
        ->withStatus((int)$result['status'])
        ->withHeader('Content-Type', 'application/json');
});

/* ================ RESERVAS =================== */
$app->post('/booking', function (Request $request, Response $response) {
    $user = $request->getAttribute('user'); 
    if (!$user) {
        $response->getBody()->write(json_encode(['status'=>401,'message'=>'No autenticado']));
        return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
    }

    $data = $request->getParsedBody();
    if (!is_array($data)) {
        $response->getBody()->write(json_encode(['status'=>400,'message'=>'Body inválido']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    $cancha_id        = isset($data['court_id']) ? (int)$data['court_id'] : 0;
    $fecha_inicio_raw = $data['booking_datetime'] ?? '';
    $duracion_bloques = isset($data['duration_blocks']) ? (int)$data['duration_blocks'] : 0;
    $participantes    = is_array($data['participants'] ?? null) ? $data['participants'] : [];

   
    $participantes = array_values(array_unique(array_map('intval', $participantes)));

    $usuario_creador = (int)$user['id'];

    $reserva = new Reserva();
    $result = $reserva->crearReserva($cancha_id, $usuario_creador, $fecha_inicio_raw, $duracion_bloques, $participantes);

    $response->getBody()->write(json_encode([
        'status'  => $result['status'],
        'message' => $result['message']
    ]));
    return $response
        ->withStatus((int)$result['status'])
        ->withHeader('Content-Type', 'application/json');
})->add($auth); //revisado por el canguro


$app->get('/booking', function (Request $solicitud, Response $respuesta) {
    $params = $solicitud->getQueryParams();
    $fecha  = trim((string)($params['date'] ?? ''));

    $reserva   = new Reserva();
    $resultado = $reserva->listarReservasPorDia($fecha);

    $respuesta->getBody()->write(json_encode([
        'status'  => $resultado['status'],
        'message' => $resultado['message']
    ]));
    return $respuesta
        ->withStatus((int)$resultado['status'])
        ->withHeader('Content-Type', 'application/json');
});




$app->delete('/booking/{id}', function (Request $solicitud, Response $respuesta, array $args) {
    $usuario   = $solicitud->getAttribute('user');
    $bookingId = (int)$args['id'];

    $reserva   = new Reserva();
    $resultado = $reserva->eliminarReserva($bookingId, (int)$usuario['id'], (int)$usuario['is_admin']);

    $respuesta->getBody()->write(json_encode([
        'status'  => $resultado['status'],
        'message' => $resultado['message']
    ]));
    return $respuesta
        ->withStatus((int)$resultado['status'])
        ->withHeader('Content-Type', 'application/json');
})->add($auth);






$app->put('/booking_participant/{id}', function (Request $solicitud, Response $respuesta, array $args) {
    $usuario    = $solicitud->getAttribute('user');
    $id_reserva = (int)$args['id'];
    $datos      = $solicitud->getParsedBody();

    if (!is_array($datos) || !array_key_exists('companeros', $datos)) {
        $respuesta->getBody()->write(json_encode([
            'status'  => 400,
            'message' => "Se requiere el campo 'companeros' en el body"
        ]));
        return $respuesta->withStatus(400)->withHeader('Content-Type', 'application/json');
    }
    if (!is_array($datos['companeros'])) {
        $respuesta->getBody()->write(json_encode([
            'status'  => 400,
            'message' => "'companeros' debe ser un array de IDs"
        ]));
        return $respuesta->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    $companeros = $datos['companeros'];

    $reserva   = new Reserva();
    $resultado = $reserva->modificarParticipantes($id_reserva, (int)$usuario['id'], $companeros);

    $respuesta->getBody()->write(json_encode([
        'status'  => $resultado['status'],
        'message' => $resultado['message']
    ]));
    return $respuesta
        ->withStatus((int)$resultado['status'])
        ->withHeader('Content-Type', 'application/json');
})->add($auth);



$app->run();
