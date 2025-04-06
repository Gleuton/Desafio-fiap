<?php

namespace FiapAdmin\Models\Student;

use FiapAdmin\Repositories\RoleRepository;
use FiapAdmin\Repositories\StudentRepository;
use PHPUnit\TextUI\XmlConfiguration\Validator;

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

    public function all(): array
    {
        return $this->repository->findAll();
    }

    public function create(array $data): array
    {
        $validation = $this->validator->validate($data);
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
}