<?php

namespace FiapAdmin\Middlewares;

use FiapAdmin\Models\Admin\Admin;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

readonly class AuthMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $headers = $request->getHeaders();
        $token = $headers['authorization'][0] ?? '';

        try {
            $adminModel = new Admin();
            $decoded = $adminModel->validateToken($token);

            $request = $request->withAttribute('admin', $decoded);
            return $handler->handle($request);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], $e->getCode() ?: 401);
        }
    }
}