<?php

namespace FiapAdmin\Middlewares;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

readonly class AuthMiddleware implements MiddlewareInterface
{
    private string $secretKey;

    public function __construct()
    {
        $this->secretKey = $this->config()->secretKey;
    }

    private function config(): object
    {
        $file = file_get_contents(__DIR__ . '/../../env.json');

        return json_decode($file, false, 512, JSON_THROW_ON_ERROR);
    }


    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $headers = $request->getHeaders();
        $token = $headers['authorization'][0] ?? '';

        if (empty($token)) {
            return new JsonResponse(['error' => 'Token JWT obrigatório'], 401);
        }

        try {
            $token = str_replace('Bearer ', '', $token);

            $decoded = JWT::decode(
                $token,
                new Key($this->secretKey, 'HS256')
            );


            if ($decoded->exp < time()) {
                return new JsonResponse(['error' => 'Token expirado'],401);
            }

            if ($decoded->role !== 'admin') {
                return new JsonResponse(['error' => 'Acesso restrito'],403);
            }

            $request = $request->withAttribute('admin', (array) $decoded);

            return $handler->handle($request);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Token inválido'], );
        }
    }
}