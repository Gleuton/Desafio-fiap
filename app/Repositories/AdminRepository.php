<?php

namespace FiapAdmin\Repositories;

class AdminRepository extends Repository
{
    protected string $table = 'users';

    public function findAdminByEmail(string $email): array
    {
        $params = ['email' => $email, 'role' => 'admin'];
        $sql = "SELECT 
                    u.id,
                    u.email,
                    u.password,
                    r.name as role
                FROM $this->table u
                inner join roles r on r.id = u.role_id
                    WHERE email = :email
                    AND r.name = :role";

        return $this->conn->query($sql, $params)[0] ?? [];
    }
}