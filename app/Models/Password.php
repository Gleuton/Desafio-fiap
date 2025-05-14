<?php

namespace FiapAdmin\Models;

use FiapAdmin\Exceptions\ValidationException;

readonly class Password
{
    /**
     * @throws ValidationException
     */
    public function __construct(private string $password)
    {
        if (!$this->isValid()) {
            throw new ValidationException(
                'password',
                'Senha deve ter 8+ caracteres, com maiúsculas, minúsculas, números e símbolos'
            );
        }
    }

    public function value(): string
    {
        return $this->hash();
    }

    private function isValid(): bool
    {
        return (bool) preg_match(
            '/^(?=.*[\p{Ll}])(?=.*[\p{Lu}])(?=.*\d)(?=.*[@$!%*?&.])[\p{L}\d@$!%*?&.]{8,}$/u',
            $this->password
        );
    }

    private function hash(): string
    {
        return password_hash($this->password, PASSWORD_BCRYPT);
    }
}