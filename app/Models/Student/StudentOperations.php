<?php

namespace FiapAdmin\Models\Student;

use DateMalformedStringException;
use DateTime;
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
        $student = $this->student($data);

        return [
            'success' => $this->repository->saveStudent($student),
            'data' => $data
        ];
    }

    /**
     * @throws DateMalformedStringException
     * @throws ValidationException
     */
    public function update(array $data): array
    {
        $student = $this->student($data);

        return [
            'success' => $this->repository->updateStudent($student),
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

    /**
     * @throws DateMalformedStringException
     * @throws ValidationException
     */
    public function student(array $data): Student
    {
        $roleId = $this->roleRepository->roleId('student');
        $name = new Name($data['name']);
        $birthdate = new DateTime($data['birthdate']);
        $cpf = new Cpf($data['cpf']);
        $email = new Email($data['email']);
        $password = new Password($data['password']);
        $id = empty($data['id']) ? null : $data['id'];

        return new Student(
            $id,
            $name,
            $cpf,
            $email,
            $birthdate,
            $password,
            $roleId
        );
    }
}