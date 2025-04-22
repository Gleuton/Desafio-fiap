<?php

namespace FiapAdmin\Models\Enrollment;

use FiapAdmin\Repositories\EnrollmentRepository;

class EnrollmentValidator
{
    private EnrollmentRepository $repository;

    public function __construct()
    {
        $this->repository = new EnrollmentRepository;
    }

    public function validateCreate(array $data): array
    {
        $errors = [];

        if (empty($data['user_id'])) {
            $errors['user_id'] = 'O ID do aluno é obrigatório';
        }

        if (empty($data['course_id'])) {
            $errors['course_id'] = 'O ID da turma é obrigatório';
        }

        if (empty($errors) && $this->repository->isEnrolled($data['user_id'], $data['course_id'])) {
            $errors['enrollment'] = 'Este aluno já está matriculado nesta turma!';
        }

        return $errors;
    }
}