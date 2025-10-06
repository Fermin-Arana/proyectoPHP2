<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/Conexion.php';
require_once __DIR__ . '/user.php';
require_once __DIR__ . '/Court.php';
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
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    $usr = new user();

    $result = $usr->login($email, $password);

    
    $response->getBody()->write(json_encode([
        'status'  => $result['status'],
        'message' => $result['message']
    ]));
    return $response
        ->withStatus((int)$result['status'])
        ->withHeader('Content-Type', 'application/json');
});

//funca


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

 //funca

/* ================ USUARIOS ==================== */
$app->post('/user', function (Request $request, Response $response) {
    $data = $request->getParsedBody() ?: [];
    
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    $firstName = $data['first_name'] ?? '';
    $lastName = $data['last_name'] ?? '';

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

//funca


$app->patch('/user/{id}', function (Request $request, Response $response, array $args) {
    $id = (int)$args['id'];
    $data = $request->getParsedBody() ?: [];

    $nombre   = $data['first_name'] ?? null;
    $apellido = $data['last_name']  ?? null;
    $password = $data['password']   ?? null;

    $usr = new user();
    $result = $usr->editarUsuario($id, $nombre, $apellido, $password);

    $response->getBody()->write(json_encode([
        'status'  => $result['status'],
        'message' => $result['message']
    ]));
    return $response
        ->withStatus((int)$result['status'])
        ->withHeader('Content-Type', 'application/json');
});

//funca

$app->delete('/user/{id}', function (Request $request, Response $response, array $args) {
    $id = (int)$args['id'];
    $data = $request->getParsedBody() ?: [];
    $currentId = $data['currentId'] ?? null;

    $usr = new user();
    $result = $usr->deleteUser($id, $currentId);

    $response->getBody()->write(json_encode([
        'status'  => $result['status'],
        'message' => $result['message']
    ]));
    return $response
        ->withStatus((int)$result['status'])
        ->withHeader('Content-Type', 'application/json');
});
//funca pero falta probar con reservas

$app->get('/user/{id}', function (Request $request, Response $response, array $args) {
    $id = (int)$args['id'];

    // Obtener el cuerpo de la solicitud
    $body = $request->getParsedBody();
    $currentId = (int)($body['currentId'] ?? null); // Obtener currentId del cuerpo de la solicitud

    $usr = new user();
    $result = $usr->getUserById($id, $currentId);

    $response->getBody()->write(json_encode([
        'status'  => $result['status'],
        'message' => $result['message']
    ]));
    return $response
        ->withStatus((int)$result['status'])
        ->withHeader('Content-Type', 'application/json');
});

//funca

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
    $data = $request->getParsedBody() ?: [];

    $name = array_key_exists('name', $data) ? trim((string)$data['name']) : null;
    $desc = array_key_exists('description', $data) ? $data['description'] : null;

    if ($name !== null && mb_strlen($name) > 100) {
        $response->getBody()->write(json_encode(['error' => 'name ≤ 100 chars']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    $court = new Court();
    
    $result = $court->actualizarCancha($id, $name, $desc);

    $response->getBody()->write(json_encode([
        'status'  => $result['status'],
        'message' => $result['message']
    ]));
    return $response
        ->withStatus((int)$result['status'])
        ->withHeader('Content-Type', 'application/json');
})->add($admin)->add($auth);

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
})->add($admin)->add($auth);

/* ================ RESERVAS =================== */
$app->post('/reserva', function (Request $solicitud, Response $respuesta) {
    $usuario = $solicitud->getAttribute('user');

    $datos = $solicitud->getParsedBody() ?: [];

    $creadorId  = (int)$usuario['id'];
    $canchaId   = (int)($datos['cancha_id'] ?? 0);
    $inicio     = trim((string)($datos['fecha_hora'] ?? ''));
    $bloques    = (int)($datos['bloques'] ?? 0);
    $companeros = is_array($datos['companeros'] ?? null) ? $datos['companeros'] : [];

    $reserva   = new Reserva();
    $resultado = $reserva->crearReserva($creadorId, $canchaId, $inicio, $bloques, $companeros);

    $respuesta->getBody()->write(json_encode([
        'status'  => $resultado['status'],
        'message' => $resultado['message']
    ]));
    return $respuesta
        ->withStatus((int)$resultado['status'])
        ->withHeader('Content-Type', 'application/json');
})->add($auth);


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





/* ================ PARTICIPANTES =================== */

$app->put('/booking_participant/{id}', function (Request $solicitud, Response $respuesta, array $args) {
    $usuario = $solicitud->getAttribute('user');
    $reservaId = (int)$args['id'];
    $datos = $solicitud->getParsedBody() ?: [];

    $nuevosParticipantes = is_array($datos['companeros'] ?? null) ? $datos['companeros'] : [];

    $reserva = new Reserva();
    $resultado = $reserva->modificarParticipantes($reservaId, (int)$usuario['id'], $nuevosParticipantes);

    $respuesta->getBody()->write(json_encode([
        'status'  => $resultado['status'],
        'message' => $resultado['message']
    ]));
    return $respuesta
        ->withStatus((int)$resultado['status'])
        ->withHeader('Content-Type', 'application/json');
})->add($auth);



$app->run();
