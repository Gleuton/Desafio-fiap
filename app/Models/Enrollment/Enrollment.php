<?php

namespace FiapAdmin\Models\Enrollment;

use FiapAdmin\Exceptions\ValidationException;

readonly class Enrollment
{
    /**
     * @throws ValidationException
     */
    public function __construct(
        private ?int $id,
        private int $userId,
        private int $courseId
    ) {
        $this->validate();
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function userId(): int
    {
        return $this->userId;
    }

    public function courseId(): int
    {
        return $this->courseId;
    }

    /**
     * @throws ValidationException
     */
    private function validate(): void
    {
        if (empty($this->userId)) {
            throw new ValidationException('O ID do aluno é obrigatório', 'user_id');
        }

        if (empty($this->courseId)) {
            throw new ValidationException('O ID da turma é obrigatório', 'course_id');
        }
    }
    
    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'course_id' => $this->courseId
        ];
    }
}
