<?php

namespace FiapAdmin\Repositories;

class CourseRepository extends Repository
{
    protected string $table = 'courses';
    protected array $fillable = ['name', 'description'];

    public function findPaginated(int $page, int $limit): array
    {
        $offset = ($page - 1) * $limit;

        $params = [
            $limit,
            $offset
        ];

        $sql = "SELECT 
                    c.id, 
                    c.name, 
                    c.description, 
                    COUNT(e.id) AS students 
                FROM $this->table c
                LEFT JOIN enrollments e ON c.id = e.course_id 
                GROUP BY c.id, c.name 
                ORDER BY c.name 
                LIMIT ? OFFSET ?";

        return $this->conn->query($sql, $params);
    }

    public function countTotal(): int
    {
        $sql = "SELECT COUNT(*) total
            FROM $this->table";

        return $this->conn->query($sql)[0]['total'];
    }
}