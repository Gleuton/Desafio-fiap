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
        if (empty($this->name)) {
            throw new ValidationException('name', 'Nome é um campo obrigatório');
        }

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
        return strlen(trim($this->name)) > 2;
    }
}
