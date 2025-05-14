<?php

namespace FiapAdmin\Models\Student;

use FiapAdmin\Exceptions\ValidationException;
use FiapAdmin\Repositories\StudentRepository;

readonly class StudentService
{
    public function __construct(
        private StudentRepository $repository,
    ) {
    }

    public function findById(int $id): array
    {
        return $this->repository->findOneById($id);
    }

    public function all(?string $name, ?int $limit): ?array
    {
        if ($name === null) {
            return $this->repository->findAll();
        }
        return $this->repository->findAllByName($name, $limit);
    }

    /**
     * @throws ValidationException
     */
    public function create(Student $student): array
    {
        return [
            'success' => $this->repository->saveStudent($student),
        ];
    }

    /**
     * @throws ValidationException
     */
    public function update(Student $student): array
    {
        return [
            'success' => $this->repository->updateStudent($student),
        ];
    }

    public function delete(int $id): array
    {
        $errors = [];

        if ($this->repository->hasEnrollments($id)) {
            $errors['enrollment'] = 'Aluno possui matrículas ativas e não pode ser excluído';
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        return [
            'success' => $this->repository->delete($id),
            'id' => $id
        ];
    }
}