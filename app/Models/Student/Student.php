<?php

namespace FiapAdmin\Models\Student;

use FiapAdmin\Repositories\RoleRepository;
use FiapAdmin\Repositories\StudentRepository;

class Student
{
    private StudentRepository $repository;
    private StudentValidator $validator;
    private RoleRepository $roleRepository;

    public function __construct()
    {
        $this->repository = new StudentRepository();
        $this->validator = new StudentValidator();
        $this->roleRepository = new RoleRepository();
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


}