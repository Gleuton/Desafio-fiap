<?php

namespace FiapAdmin\Models\Student;

use DateTime;
use FiapAdmin\Models\Cpf;
use FiapAdmin\Models\Email;
use FiapAdmin\Models\Name;
use FiapAdmin\Models\Password;

readonly class Student
{
    public const string ROLE = 'student';

    public function __construct(
        private ?int $id,
        private Name $name,
        private Cpf $cpf,
        private Email $email,
        private DateTime $birthdate,
        private ?Password $password,
    ) {
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name->value();
    }

    public function cpf(): string
    {
        return $this->cpf->value();
    }

    public function email(): string
    {
        return $this->email->value();
    }

    public function birthdate(): string
    {
        return $this->birthdate->format('Y-m-d');
    }

    public function password(): ?string
    {
        return $this->password->value();
    }
}