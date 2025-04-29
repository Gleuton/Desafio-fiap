<?php

namespace FiapAdmin\Controllers;

use FiapAdmin\Models\Admin\Admin;
use FiapAdmin\Repositories\TokenRepository;
use Firebase\JWT\JWT;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface as Response;

readonly class AuthController
{
    public function __construct(private Admin $admin, private TokenRepository $tokenRepository)
    {
    }

    public function check(ServerRequestInterface $request): Response
    {
        $headers = $request->getHeaders();
        $token = $headers['authorization'][0] ?? '';
        $payload = $this->admin->isTokenValid($token);

        if (!$payload) {
            return new JsonResponse(['error' => 'Token inválido ou sem permissão'], 401);
        }

        return new JsonResponse(['valid' => true], 200);
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

        $admin = $this->admin->authenticate($email, $password);

        if (!$admin) {
            return new JsonResponse(['error' => 'Credenciais inválidas'], 401);
        }


        return new JsonResponse(['token' => $this->newToken($admin)], 200);
    }

    public function logout(ServerRequestInterface $request): Response
    {
        $headers = $request->getHeaders();
        $authHeader = $headers['authorization'][0] ?? '';

        if (empty($authHeader) || !str_starts_with($authHeader, 'Bearer ')) {
            return new JsonResponse(['error' => 'Token não fornecido'], 401);
        }

        $token = str_replace('Bearer ', '', $authHeader);

        $found = $this->tokenRepository->findByToken($token);

        if (!$found || $found['revoked']) {
            return new JsonResponse(['error' => 'Token inválido ou já revogado'], 401);
        }

        $userId = $found['user_id'];
        $this->tokenRepository->revokeAllForUser((int) $userId);

        return new JsonResponse(['message' => 'Logout realizado com sucesso'], 200);
    }

    private function newToken(array $admin): string
    {
        $secretKey = $this->admin->getSecretKey();

        $this->tokenRepository->revokeAllForUser($admin['id']);

        $payload = [
            'admin_id' => $admin['id'],
            'exp' => time() + (60 * 60),
            'role' => $admin['role'],
        ];


        $token = JWT::encode($payload, $secretKey, 'HS256');
        $refreshToken = bin2hex(random_bytes(32));

        $this->tokenRepository->create(
            $admin['id'],
            $token,
            $refreshToken,
            date('Y-m-d H:i:s', $payload['exp'])
        );

        return $token;
    }
}