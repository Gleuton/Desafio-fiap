<?php

namespace FiapAdmin\Models\Student;

use FiapAdmin\Repositories\EnrollmentsRepository;
use FiapAdmin\Repositories\RoleRepository;
use FiapAdmin\Repositories\StudentRepository;

class Student
{
    private readonly StudentRepository $repository;
    private readonly StudentValidator $validator;
    private readonly RoleRepository $roleRepository;
    private readonly EnrollmentsRepository $enrollmentsRepository;

    public function __construct()
    {
        $this->repository = new StudentRepository();
        $this->validator = new StudentValidator();
        $this->roleRepository = new RoleRepository();
        $this->enrollmentsRepository = new EnrollmentsRepository();
    }

    public function findById(int $id): array
    {
        return $this->repository->findOneById($id);
    }

    public function all(): array
    {
        return $this->repository->findAll();
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
            'success' => $this->repository->insert($data),
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
            'success' => $this->repository->update($id, $data),
            'data' => $data
        ];
    }

    public function delete(int $id): array {
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