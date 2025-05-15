<?php

namespace FiapAdmin\Repositories;

use FiapAdmin\Exceptions\ValidationException;

class EnrollmentRepository extends Repository
{
    protected string $table = 'enrollments';

    protected array $fillable = ['user_id', 'course_id'];

    public function findOneById(int $id): ?array
    {
        return $this->findById($id);
    }

    public function findPaginated(int $page, int $limit): array
    {
        $offset = ($page - 1) * $limit;

        $sql = "SELECT 
                    e.id, 
                    e.user_id,
                    e.course_id,
                    u.name AS student_name,
                    c.name AS course_name
                FROM $this->table e
                INNER JOIN courses c ON c.id = e.course_id
                INNER JOIN users u ON u.id = e.user_id
                ORDER BY e.id 
                LIMIT $limit OFFSET $offset";

        return $this->query($sql);
    }

    public function countTotal(): int
    {
        $sql = "SELECT COUNT(*) total
            FROM $this->table";

        return $this->query($sql)[0]['total'];
    }
    public function isEnrolled(int $studentId, int $courseId): bool
    {
        $sql = "SELECT * FROM $this->table 
                WHERE user_id = :user_id 
                AND course_id = :course_id";
        $params = [
            'user_id' => $studentId,
            'course_id' => $courseId
        ];

        $result = $this->query($sql, $params);

        return !empty($result);
    }

    public function saveEnrollment(array $data): array
    {
        $newId = $this->insert($data);
        return $this->findById($newId);
    }

    public function listByCurses(int $courseId): array
    {
        $sql = "SELECT 
                    e.id,
                    u.name AS student_name
                FROM $this->table e
                INNER JOIN courses c ON c.id = e.course_id
                INNER JOIN users u ON u.id = e.user_id
                    WHERE course_id = :course_id";
        $params = [
            'course_id' => $courseId
        ];

        return $this->query($sql, $params);
    }

    public function updateEnrollment(int $id, array $data): bool
    {
        return $this->update($id, $data);
    }
}
