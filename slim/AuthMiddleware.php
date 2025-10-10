<?php
// slim/AuthMiddleware.php
require_once __DIR__ . '/Conexion.php';

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ResponseFactoryInterface;

class AuthMiddleware
{
    public function __construct(private ResponseFactoryInterface $rf) {}

    public function __invoke(Request $request, Handler $handler): Response
    {
        $auth = $request->getHeaderLine('Authorization'); 
        if (!preg_match('/^Bearer\s+(.+)$/i', $auth, $m)) {
            return $this->json(401, ['error' => 'Falta header Authorization Bearer']);
        }
        $token = $m[1];

        try {
            $cn = new \Conexion();
            $db = $cn->getDb();

            $q = $db->prepare("SELECT id, email, is_admin FROM users WHERE token = :t AND expired > NOW()");
            $q->execute([':t' => $token]);
            $user = $q->fetch(\PDO::FETCH_ASSOC);

            if (!$user) {
                return $this->json(401, ['error' => 'Token inválido o vencido']);
            }

            // Normalización de tipos (id e is_admin a int)
            $user = [
                'id'       => (int)$user['id'],
                'email'    => $user['email'],
                'is_admin' => (int)$user['is_admin'],
            ];

            // Renovar vencimiento (keep-alive de sesión)
            $db->prepare("UPDATE users SET expired = DATE_ADD(NOW(), INTERVAL 5 MINUTE) WHERE id = :id")
               ->execute([':id' => $user['id']]);

            // Inyectar user normalizado en el request
            $request = $request->withAttribute('user', $user);

            return $handler->handle($request);

        } catch (\Throwable $e) {
            return $this->json(500, ['error' => 'Error de autenticación', 'detail' => $e->getMessage()]);
        }
    }

    private function json(int $status, array $payload): Response
    {
        $response = $this->rf->createResponse($status);
        $response->getBody()->write(json_encode($payload));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
