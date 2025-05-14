<?php

namespace FiapAdmin\Models\Admin;

use FiapAdmin\Repositories\TokenRepository;
use Firebase\JWT\JWT;

readonly class AuthService
{
    public function __construct(
        private Admin $admin,
        private TokenRepository $tokenRepository
    ) {
    }

    public function validateToken(string $token): ?array
    {
        return $this->admin->isTokenValid($token);
    }

    public function authenticate(string $email, string $password): ?array
    {
        if (empty($email) || empty($password)) {
            return null;
        }

        return $this->admin->authenticate($email, $password);
    }

    public function logout(string $token): bool
    {
        if (empty($token) || !str_starts_with($token, 'Bearer ')) {
            return false;
        }

        $token = str_replace('Bearer ', '', $token);

        $found = $this->tokenRepository->findByToken($token);

        if (!$found || $found['revoked']) {
            return false;
        }

        $userId = $found['user_id'];
        $this->tokenRepository->revokeAllForUser((int) $userId);

        return true;
    }

    public function generateToken(array $admin): array
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

        return [
            'token' => $token,
            'refresh_token' => $refreshToken
        ];
    }

    public function refreshToken(string $refreshToken): ?array
    {
        if (empty($refreshToken)) {
            return null;
        }

        $tokenData = $this->tokenRepository->findByRefreshToken($refreshToken);

        if (!$tokenData) {
            return null;
        }

        $admin = [
            'id' => $tokenData['user_id'],
            'role' => 'admin'
        ];

        return $this->generateToken($admin);
    }
}
