<?php

namespace FiapAdmin\Models;

use FiapAdmin\Exceptions\ValidationException;

readonly class Email
{
    /**
     * @throws ValidationException
     */
    public function __construct(
        private string $email
    ) {
        if (empty($this->email)) {
            throw new ValidationException('email', 'E-mail é um campo obrigatório');
        }
        if (!$this->isValid()) {
            throw new ValidationException('email', 'E-mail inválido');
        }
    }

    public function value(): string
    {
        return $this->email;
    }

    private function isValid(): bool
    {
        $normalizedEmail = $this->normalize();

        if ($this->hasInvalidFormat($normalizedEmail)) {
            return false;
        }

        return true;
    }

    private function normalize(): string
    {
        return strtolower(trim($this->email));
    }

    private function hasInvalidFormat(string $email): bool
    {
        return !filter_var($email, FILTER_VALIDATE_EMAIL);
    }
}