<?php

namespace FiapAdmin\Models\Admin;

use FiapAdmin\Repositories\AdminRepository;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use RuntimeException;

readonly class Admin
{
    private AdminRepository $adminRepository;
    private string $secretKey;

    public function __construct()
    {
        $this->adminRepository = new AdminRepository();
        $this->secretKey = $this->config()->secretKey;
    }

    public function getSecretKey(): string
    {
        return $this->secretKey;
    }

    public function authenticate(string $email, string $password): ?array
    {
        $admin = $this->adminRepository->findAdminByEmail($email);

        if ($admin && password_verify($password, $admin['password'])) {
            return $admin;
        }

        return null;
    }

    public function validateToken(string $token): array
    {
        if (empty($token)) {
            throw new RuntimeException('Token JWT obrigatório', 401);
        }

        $token = str_replace('Bearer ', '', $token);

        $decoded = JWT::decode(
            $token,
            new Key($this->secretKey, 'HS256')
        );

        if ($decoded->exp < time()) {
            throw new RuntimeException('Token expirado', 401);
        }

        if ($decoded->role !== 'admin') {
            throw new RuntimeException('Acesso restrito', 403);
        }

        return (array) $decoded;
    }

    private function config(): object
    {
        $file = file_get_contents(__DIR__ . '/../../../env.json');
        return json_decode($file, false, 512, JSON_THROW_ON_ERROR);
    }
}