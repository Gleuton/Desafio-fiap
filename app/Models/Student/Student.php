<?php

namespace FiapAdmin\Models\Student;

use FiapAdmin\Repositories\RoleRepository;
use FiapAdmin\Repositories\StudentRepository;

readonly class Student
{
    public function __construct(
        private StudentRepository $repository,
        private StudentValidator $validator,
        private RoleRepository $roleRepository
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

    public function create(array $data): array
    {
        $validation = $this->validator->validateCreate($data);

        if (!empty($validation)) {
            return ['success' => false, 'errors' => $validation];
        }

        $data['role_id'] = $this->roleRepository->roleId('student');

        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);

        return [
            'success' => $this->repository->saveStudent($data),
            'data' => $data
        ];
    }

    public function update(?int $id, array $data): array
    {
        $validation = $this->validator->validateUpdate($id, $data);
        if (!empty($validation)) {
            return ['success' => false, 'errors' => $validation];
        }

        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);

        return [
            'success' => $this->repository->updateStudent($id, $data),
            'data' => $data
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