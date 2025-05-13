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
        if (!$this->isValid()) {
            throw new ValidationException('email', 'E-Mail inválido');
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