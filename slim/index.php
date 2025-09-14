<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/vendor/autoload.php';

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

// ACÁ VAN LOS ENDPOINTS

$app->post('/user/login', function (Request $request, Response $response){
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

$app->post('/user/logout', function (Request $request, Response $response){
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

$app->run();
