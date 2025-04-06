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
            FROM $this->table u
            INNER JOIN roles r ON u.role_id = r.id
            WHERE r.name = 'student'
            ORDER BY u.name ASC";

        return $this->conn->query($sql);
    }

    public function insert(array $data): array
    {
        $newId = $this->conn->insert($data);
        return $this->findById($newId);
    }

    public function update(int $id, array $data): array
    {
        $newId = $this->conn->update($id, $data);
        return $this->findById($newId);
    }

    public function cpfExists(string $cpf, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) total
            FROM $this->table u
            INNER JOIN roles r ON u.role_id = r.id
            WHERE u.cpf = ? 
            AND r.name = 'student'";

        $params = [$cpf];

        if ($excludeId) {
            $sql .= " AND u.id <> ?";
            $params[] = $excludeId;
        }

        return $this->conn->query($sql, $params)[0]['total'] > 0;
    }

    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) total
            FROM $this->table u
            INNER JOIN roles r ON u.role_id = r.id
            WHERE u.email = ? 
            AND r.name = 'student'";

        $params = [$email];

        if ($excludeId) {
            $sql .= " AND u.id <> ?";
            $params[] = $excludeId;
        }

        return $this->conn->query($sql, $params)[0]['total'] > 0;
    }

    public function findOneById(int $id): array
    {
        $fields = ['id', 'name', 'birthdate', 'cpf', 'email'];
        return $this->findById($id, $fields);
    }

    public function hasEnrollments(int $id): bool
    {
        $sql = "SELECT COUNT(*) total
            FROM $this->table u
            INNER JOIN enrollments e ON u.id =e.user_id
            WHERE u.id = ?";

        $params = [$id];
        return $this->conn->query($sql, $params)[0]['total'] > 0;
    }
}