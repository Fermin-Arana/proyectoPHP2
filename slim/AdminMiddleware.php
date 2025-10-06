<?php
// slim/AdminMiddleware.php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ResponseFactoryInterface;

class AdminMiddleware
{
    public function __construct(private ResponseFactoryInterface $rf) {}

    public function __invoke(Request $request, Handler $handler): Response
    {
        $user = $request->getAttribute('user'); // lo puso AuthMiddleware
        if (!$user || (int)$user['is_admin'] !== 1) {
            $r = $this->rf->createResponse(403);
            $r->getBody()->write(json_encode(['error' => 'Solo administradores']));
            return $r->withHeader('Content-Type', 'application/json');
        }

        return $handler->handle($request);
    }
}
