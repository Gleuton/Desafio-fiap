<?php

namespace FiapAdmin\Models\Admin;

use FiapAdmin\Repositories\AdminRepository;
use FiapAdmin\Repositories\TokenRepository;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

readonly class Admin
{
    private string $secretKey;

    public function __construct(private AdminRepository $adminRepository, private TokenRepository $tokenRepository)
    {
        $this->secretKey = $this->config()->secretKey;
    }

    public function getSecretKey(): string
    {
        return $this->secretKey;
    }

    public function authenticate(string $email, string $password): ?array
    {
        $admin = $this->adminRepository->findAdminByEmail($email);

        if (!$admin || !$this->isPasswordValid($admin, $password)) {
            return null;
        }

        return $admin;
    }

    private function isPasswordValid(array $admin, string $password): bool
    {
        return password_verify($password, $admin['password']);
    }

    public function isTokenValid(string $bearerToken, string $requiredRole = 'admin'): ?array
    {
        $token = str_replace('Bearer ', '', $bearerToken);

        $tokenData = $this->tokenRepository->findByToken($token);

        if (!$tokenData) {
            return null;
        }

        if ($tokenData['revoked']) {
            return null;
        }

        if (strtotime($tokenData['expires_at']) < time()) {
            return null;
        }

        try {
            $payload = (array) JWT::decode($token, new Key($this->getSecretKey(), 'HS256'));

            if (($payload['role'] ?? '') !== $requiredRole) {
                return null;
            }

            return $payload;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function config(): object
    {
        $file = file_get_contents(__DIR__ . '/../../../env.json');
        return json_decode($file, false, 512, JSON_THROW_ON_ERROR);
    }
}