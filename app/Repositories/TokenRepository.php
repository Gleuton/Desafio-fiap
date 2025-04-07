<?php

namespace FiapAdmin\Repositories;

class TokenRepository extends Repository
{
    protected string $table = 'tokens';

    protected array $fillable = [
        'user_id',
        'token',
        'refresh_token',
        'expires_at',
        'created_at',
        'revoked',
    ];
    public function findByToken(string $token): ?array
    {
        $params = ['token' => $token];
        $sql = 'WHERE token = :token LIMIT 1';

        return $this->conn->findBy($sql, $params);
    }

    public function revokeAllForUser(int $userId): bool
    {
        $sql = 'UPDATE tokens SET revoked = true WHERE user_id = :user_id';
        $params = ['user_id' => $userId];
        return $this->conn->execute($sql, $params);
    }

    public function create(int $userId, string $token, string $refreshToken, string $expiresAt): ?int
    {
        $params = [
            'user_id'       => $userId,
            'token'         => $token,
            'refresh_token' => $refreshToken,
            'expires_at'    => $expiresAt,
            'revoked'       => 0
        ];

        return $this->conn->insert($params);
    }
}