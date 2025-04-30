<?php

namespace FiapAdmin\Models\Student;

use FiapAdmin\Repositories\StudentRepository;

readonly class StudentValidator
{
    public function __construct(private StudentRepository $student)
    {
    }

    public function validateCreate(array $data): array
    {
        $errors = [];

        $required = ['name', 'birthdate', 'cpf', 'email', 'password'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $errors[$field] = ucfirst($field) . ' é obrigatório';
            }
        }

        if (!empty($errors)) {
            return $errors;
        }

        return array_merge($errors, $this->validateCommon($data));
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

        if (isset($data['cpf']) && !$this->validateCPF($data['cpf'])) {
            $errors['cpf'] = 'CPF inválido';
        }

        if (isset($data['birthdate']) && !$this->validateDate($data['birthdate'])) {
            $errors['birthdate'] = 'Data de nascimento inválida';
        }

        if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'E-mail inválido';
        }

        return $errors;
    }

    private function validateCPF(string $cpf): bool
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        if (strlen($cpf) !== 11 || preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        for ($i = 0, $sum = 0, $weight = 10; $i < 9; $i++) {
            $sum += (int)$cpf[$i] * $weight--;
        }
        $checkDigit = ($sum % 11) < 2 ? 0 : 11 - ($sum % 11);
        if ($checkDigit != $cpf[9]) {
            return false;
        }

        for ($i = 0, $sum = 0, $weight = 11; $i < 10; $i++) {
            $sum += (int)$cpf[$i] * $weight--;
        }
        $checkDigit = ($sum % 11) < 2 ? 0 : 11 - ($sum % 11);
        return $checkDigit == $cpf[10];
    }

    private function validatePassword(string $password): bool
    {
        return (bool) preg_match(
            '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&.]{8,}$/u',
            $password
        );
    }

    private function validateDate(string $date): bool
    {
        return strtotime($date) !== false;
    }
}
