<?php

namespace FiapAdmin\Controllers;

use FiapAdmin\Models\Admin\Admin;
use Firebase\JWT\JWT;
use JsonException;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface as Response;

class AuthController
{
    private Admin $admin;

    public function __construct()
    {
        $this->admin = new Admin();
    }

    public function check(ServerRequestInterface $request): Response
    {
        $headers = $request->getHeaders();
        $token = $headers['authorization'][0] ?? '';
        $this->admin->validateToken($token);

        return new JsonResponse(['valid' => true], 200);
    }

    /**
     * @throws JsonException
     */
    public function login(ServerRequestInterface $request): Response
    {
        $secretKey = $this->admin->getSecretKey();

        $body = $request->getBody();
        $data = json_decode($body->getContents(), true, 512, JSON_THROW_ON_ERROR);

        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        if (empty($email) || empty($password)) {
            return new JsonResponse(['error' => 'E-mail e senha são obrigatórios'], 400);
        }

        $admin = $this->admin->authenticate($email, $password);

        if ($admin) {
            $payload = [
                'admin_id' => $admin['id'],
                'exp' => time() + (60 * 60),
                'role' => $admin['role'],
            ];
            $token = JWT::encode($payload, $secretKey, 'HS256');

            return new JsonResponse(['token' => $token], 200);
        }

        return new JsonResponse(['error' => 'Credenciais inválidas'], 401);
    }
}