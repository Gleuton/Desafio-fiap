<?php

namespace FiapAdmin\Models\Student;

use FiapAdmin\Repositories\StudentRepository;

class StudentValidator
{
    private StudentRepository $student;
    public function __construct()
    {
        $this->student = new StudentRepository();
    }

    public function validate(array $data): array
    {
        $errors = [];

        $required = ['name', 'birthdate', 'cpf', 'email', 'password'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $errors[$field] = ucfirst($field) . ' é obrigatório';
            }
        }

        if ($this->student->cpfExists($data['cpf'])) {
            $errors['cpf'] = 'CPF já cadastrado';
        }


        if ($this->student->emailExists($data['email'])) {
            $errors['email'] = 'E-mail já cadastrado';
        }

        if (isset($data['name']) && strlen($data['name']) < 3) {
            $errors['name'] = 'Nome deve ter pelo menos 3 caracteres';
        }

        if (isset($data['password']) && !$this->validatePassword($data['password'])) {
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

    public function uniqueCpf():bool
    {

    }

    private function validateCPF(string $cpf): bool
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) return false;

        for ($i = 0, $sum = 0, $weight = 10; $i < 9; $i++) {
            $sum += (int)$cpf[$i] * $weight--;
        }
        $checkDigit = ($sum % 11) < 2 ? 0 : 11 - ($sum % 11);
        if ($checkDigit != $cpf[9]) return false;

        for ($i = 0, $sum = 0, $weight = 11; $i < 10; $i++) {
            $sum += (int)$cpf[$i] * $weight--;
        }
        $checkDigit = ($sum % 11) < 2 ? 0 : 11 - ($sum % 11);
        return $checkDigit == $cpf[10];
    }

    private function validatePassword(string $password): bool
    {
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password);
    }

    private function validateDate(string $date): bool
    {
        return strtotime($date) !== false;
    }
}