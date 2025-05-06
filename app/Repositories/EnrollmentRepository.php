<?php

namespace FiapAdmin\Repositories;

class EnrollmentRepository extends Repository
{
    protected string $table = 'enrollments';

    protected array $fillable = ['user_id', 'course_id'];
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
}