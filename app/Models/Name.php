<?php

namespace FiapAdmin\Models;

use FiapAdmin\Exceptions\ValidationException;

readonly class Name
{
    /**
     * @throws ValidationException
     */
    public function __construct(private string $name)
    {
        if (!$this->isValid()) {
            throw new ValidationException('name', 'Nome deve ter pelo menos 3 caracteres');
        }
    }

    public function value(): string
    {
        return $this->name;
    }

    private function isValid(): bool
    {
        return strlen($this->name) > 3;
    }
}