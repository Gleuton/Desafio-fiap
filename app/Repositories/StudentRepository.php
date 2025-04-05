<?php

namespace FiapAdmin\Repositories;

class StudentRepository extends Repository
{
    protected string $table = 'users';
    protected array $fillable = [
        'name',
        'birthdate',
        'cpf',
        'email',
        'password',
        'role_id'
    ];

    public function findAll(): array
    {
        $sql = "SELECT 
                u.id,
                u.name,
                u.birthdate,
                u.cpf,
                u.email
            FROM users u
            INNER JOIN roles r ON u.role_id = r.id
            WHERE r.name = 'student'
            ORDER BY u.name ASC";

        return $this->conn->query($sql);
    }
}