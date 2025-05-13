<?php

namespace FiapAdmin\Models\Student;

use DateMalformedStringException;
use DateTime;
use FiapAdmin\Exceptions\DuplicationException;
use FiapAdmin\Exceptions\ValidationException;
use FiapAdmin\Models\Cpf;
use FiapAdmin\Models\Email;
use FiapAdmin\Models\Name;
use FiapAdmin\Models\Password;
use FiapAdmin\Repositories\RoleRepository;
use FiapAdmin\Repositories\StudentRepository;

readonly class StudentOperations
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

    /**
     * @throws DateMalformedStringException
     * @throws ValidationException
     */
    public function create(array $data): array
    {
        $roleId = $this->roleRepository->roleId('student');
        $name = new Name($data['name']);
        $birthdate = new DateTime($data['birthdate']);
        $cpf = new Cpf($data['cpf']);
        $email = new Email($data['email']);
        $password = new Password($data['password']);

        $student = new Student(
            null,
            $name,
            $cpf,
            $email,
            $birthdate,
            $password,
            $roleId
        );

        return [
            'success' => $this->repository->saveStudent($student),
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