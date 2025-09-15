<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/vendor/autoload.php';
Require_once __DIR__ . '/Conexion.php';
Require_once __DIR__ . '/user.php';

$app = AppFactory::create();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);
$app->add( function ($request, $handler) {
    $response = $handler->handle($request);

    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'OPTIONS, GET, POST, PUT, PATCH, DELETE')
        ->withHeader('Content-Type', 'application/json')
    ;
});


$app->post('/login', function (Request $request, Response $response){
    $data = $request->getParsedBody();
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    $usr = new user();

    $result = $usr->login($email,$password);
    $response->getBody()->write(json_encode([
        'status'=>$result['status'],
        'message'=>$result['message']
    ]));
    
    return $response
        ->withStatus($result['status'])
        ->withHeader('Content-Type', 'aplication/json');
});

$app->post('/logout', function (Request $request, Response $response){
    $data = $request->getParsedBody();
    $id = $data['id'] ?? '';
    $usr = new user();

    $result = $usr->logout($id);
    $response->getBody()->write(json_encode([
        'status'=>$result['status'],
        'message'=>$result['message']
    ]));
    
    return $response
        ->withStatus($result['status'])
        ->withHeader('Content-Type', 'aplication/json');
});

$app->patch('/user/{id}', function (Request $request, Response $response, array $args) {
    $id = $args['id'];
    $data = $request->getParsedBody();

    $currentId = $data['currentId'] ?? null;

    $nombre = $data['first_name'] ?? null;
    $apellido = $data['last_name'] ?? null;
    $password = $data['password'] ?? null;

    $usr = new user();
    $result = $usr->editarUsuario($id, $nombre, $apellido, $password);

    $response->getBody()->write(json_encode([
        'status' => $result['status'],
        'message' => $result['message']
    ]));

    return $response
        ->withStatus($result['status'])
        ->withHeader('Content-Type', 'application/json');
});

$app->delete('/user/{id}', function (Request $request, Response $response, array $args) {
    $id = $args['id'];
    $data = $request->getParsedBody();

    $currentId = $data['currentId'] ?? null;

    $usr = new user();
    $result = $usr->deleteUser($id, $currentId);

    $response->getBody()->write(json_encode([
        'status' => $result['status'],
        'message' => $result['message']
    ]));

    return $response
        ->withStatus($result['status'])
        ->withHeader('Content-Type', 'application/json');
});

$app->get('/user/{id}', function (Request $request, Response $response, array $args) {
    $id = $args['id'];
    $data = $request->getQueryParams();

    $currentId = $data['currentId'] ?? null;

    $usr = new user();
    $result = $usr->getUserById($id, $currentId);

    $response->getBody()->write(json_encode([
        'status' => $result['status'],
        'message' => $result['message']
    ]));

    return $response
        ->withStatus($result['status'])
        ->withHeader('Content-Type', 'application/json');
});

$app->get('/users', function (Request $request, Response $response) {
    $params = $request->getQueryParams();
    $search = $params['search'] ?? '';

    $usr = new user();
    $result = $usr->getAllUsers($search);

    $response->getBody()->write(json_encode([
        'status' => $result['status'],
        'message' => $result['message']
    ]));

    return $response
        ->withStatus($result['status'])
        ->withHeader('Content-Type', 'application/json');
});



$app->run();
