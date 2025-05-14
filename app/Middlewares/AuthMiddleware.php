<?php

namespace FiapAdmin\Middlewares;

use FiapAdmin\Models\Admin\Admin;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

readonly class AuthMiddleware implements MiddlewareInterface
{
    public function __construct(private Admin $admin)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $headers = $request->getHeaders();
        $token = $headers['authorization'][0] ?? '';

        try {
            $adminModel = $this->admin;
            $decoded = $adminModel->isTokenValid($token);

            if (!$decoded) {
                return new JsonResponse(['error' => 'Token inválido ou sem permissão'], 401);
            }

            $request = $request->withAttribute('admin', $decoded);
            return $handler->handle($request);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], $e->getCode() ?: 401);
        }
    }
}
