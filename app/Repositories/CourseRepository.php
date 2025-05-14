<?php

namespace FiapAdmin\Repositories;

use FiapAdmin\Models\Course\Course;

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

        return $this->query($sql);
    }

    public function countTotal(): int
    {
        $sql = "SELECT COUNT(*) total
            FROM $this->table";

        return $this->query($sql)[0]['total'];
    }

    public function saveCourse(Course $course): array
    {
        $data = [
            'name' => $course->name()->value(),
            'description' => $course->description()->value()
        ];
        $newId = $this->insert($data);
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

        return $this->query($sql, $params)[0]['total'] > 0;
    }

    public function findOneById(int $id): ?array
    {
        return $this->findById($id);
    }

    public function updateCourse(Course $course): bool
    {
        $id = $course->id();
        $data = [
            'name' => $course->name()->value(),
            'description' => $course->description()->value()
        ];
        return $this->update($id, $data);
    }

    public function hasEnrollments(int $id): bool
    {
        $sql = "SELECT COUNT(*) total
            FROM $this->table c
            INNER JOIN enrollments e ON c.id = e.course_id
            WHERE c.id = ?";

        $params = [$id];
        return $this->query($sql, $params)[0]['total'] > 0;
    }
}
