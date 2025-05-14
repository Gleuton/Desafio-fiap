<?php

namespace FiapAdmin\Controllers;

use FiapAdmin\Models\Admin\AuthService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface as Response;

readonly class AuthController
{
    public function __construct(private AuthService $authService)
    {
    }

    public function check(ServerRequestInterface $request): Response
    {
        $headers = $request->getHeaders();
        $token = $headers['authorization'][0] ?? '';
        $payload = $this->authService->validateToken($token);

        if (!$payload) {
            return new JsonResponse(['error' => 'Token inválido ou sem permissão'], 401);
        }

        $adminId = $payload['admin_id'] ?? null;
        if (!$adminId) {
            return new JsonResponse(['error' => 'Token inválido ou sem permissão'], 401);
        }

        return new JsonResponse($token, 200);
    }

    public function login(ServerRequestInterface $request): Response
    {
        $body = $request->getBody();
        $data = json_decode($body->getContents(), true, 512, JSON_THROW_ON_ERROR);

        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        if (empty($email) || empty($password)) {
            return new JsonResponse(['error' => 'E-mail e senha são obrigatórios'], 400);
        }

        $admin = $this->authService->authenticate($email, $password);

        if (!$admin) {
            return new JsonResponse(['error' => 'Credenciais inválidas'], 401);
        }

        return new JsonResponse($this->authService->generateToken($admin), 200);
    }

    public function refresh(ServerRequestInterface $request): Response
    {
        $body = $request->getBody();
        $data = json_decode($body->getContents(), true, 512, JSON_THROW_ON_ERROR);

        $refreshToken = $data['refresh_token'] ?? '';

        if (empty($refreshToken)) {
            return new JsonResponse(['error' => 'Refresh token é obrigatório'], 400);
        }

        $tokens = $this->authService->refreshToken($refreshToken);

        if (!$tokens) {
            return new JsonResponse(['error' => 'Refresh token inválido ou expirado'], 401);
        }

        return new JsonResponse($tokens, 200);
    }

    public function logout(ServerRequestInterface $request): Response
    {
        $headers = $request->getHeaders();
        $authHeader = $headers['authorization'][0] ?? '';

        $success = $this->authService->logout($authHeader);

        if (!$success) {
            return new JsonResponse(['error' => 'Token inválido ou já revogado'], 401);
        }

        return new JsonResponse(['message' => 'Logout realizado com sucesso'], 200);
    }
}
