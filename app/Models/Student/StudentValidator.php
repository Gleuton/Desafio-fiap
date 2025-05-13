<?php

namespace FiapAdmin\Models\Student;

use FiapAdmin\Repositories\StudentRepository;

readonly class StudentValidator
{
    public function __construct(private StudentRepository $student)
    {
    }

    public function validateUpdate(?int $id, array $data): array
    {
        $errors = [];

        if (is_null($id)) {
            $errors['id'] = 'ID é obrigatório';
        }

        $required = ['name', 'birthdate', 'cpf', 'email'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $errors[$field] = ucfirst($field) . ' é obrigatório';
            }
        }

        if (!empty($errors)) {
            return $errors;
        }

        $errors = array_merge($errors, $this->validateCommon($data, $id));

        if (!empty($data['password']) && !$this->validatePassword($data['password'])) {
            $errors['password'] = 'Senha deve ter 8+ caracteres, com maiúsculas, minúsculas, números e símbolos';
        }

        return $errors;
    }


    private function validateCommon(array $data, $excludeId = null): array
    {
        $errors = [];

        if ($this->student->cpfExists($data['cpf'], $excludeId)) {
            $errors['cpf'] = 'CPF já cadastrado';
        }

        if ($this->student->emailExists($data['email'], $excludeId)) {
            $errors['email'] = 'E-mail já cadastrado';
        }

        if (isset($data['name']) && strlen($data['name']) < 3) {
            $errors['name'] = 'Nome deve ter pelo menos 3 caracteres';
        }

        if (!empty($data['password']) && !$this->validatePassword($data['password'])) {
            $errors['password'] = 'Senha deve ter 8+ caracteres, com maiúsculas, minúsculas, números e símbolos';
        }

        if (isset($data['birthdate']) && !$this->validateDate($data['birthdate'])) {
            $errors['birthdate'] = 'Data de nascimento inválida';
        }

        if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'E-mail inválido';
        }

        return $errors;
    }

    private function validatePassword(string $password): bool
    {
        return (bool) preg_match(
            '/^(?=.*[\p{Ll}])(?=.*[\p{Lu}])(?=.*\d)(?=.*[@$!%*?&.])[\p{L}\d@$!%*?&.]{8,}$/u',
            $password
        );
    }

    private function validateDate(string $date): bool
    {
        return strtotime($date) !== false;
    }
}
