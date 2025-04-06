<?php

namespace FiapAdmin\Repositories;

class CourseRepository extends Repository
{
    protected string $table = 'courses';
    protected array $fillable = ['name', 'description'];

    public function findPaginated(int $page, int $limit): array
    {
        $offset = ($page - 1) * $limit;

        $sql = "SELECT 
                    c.id, 
                    c.name, 
                    c.description, 
                    COUNT(e.id) AS students 
                FROM $this->table c
                LEFT JOIN enrollments e ON c.id = e.course_id 
                GROUP BY c.id, c.name 
                ORDER BY c.name 
                LIMIT $limit OFFSET $offset";

        return $this->conn->query($sql);
    }

    public function countTotal(): int
    {
        $sql = "SELECT COUNT(*) total
            FROM $this->table";

        return $this->conn->query($sql)[0]['total'];
    }

    public function insert(array $data): array
    {
        $newId = $this->conn->insert($data);
        return $this->findById($newId);
    }

    public function nameExists(string $name, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) total
            FROM $this->table c
            WHERE c.name = ?";

        $params = [$name];

        if ($excludeId) {
            $sql .= " AND c.id <> ?";
            $params[] = $excludeId;
        }

        return $this->conn->query($sql, $params)[0]['total'] > 0;
    }

    public function findOneById(int $id): ?array
    {
        return $this->conn->findById($id);
    }

    public function update(int $id, array $data): bool
    {
       return $this->conn->update($id, $data);
    }

    public function hasEnrollments(int $id): bool
    {
        $sql = "SELECT COUNT(*) total
            FROM $this->table c
            INNER JOIN enrollments e ON c.id = e.course_id
            WHERE c.id = ?";

        $params = [$id];
        return $this->conn->query($sql, $params)[0]['total'] > 0;
    }
}