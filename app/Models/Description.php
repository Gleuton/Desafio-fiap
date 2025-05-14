<?php

namespace FiapAdmin\Models;

use FiapAdmin\Exceptions\ValidationException;

readonly class Description
{
    /**
     * @throws ValidationException
     */
    public function __construct(private string $description)
    {
        if (empty($this->description)) {
            throw new ValidationException('description', 'Descrição é um campo obrigatório');
        }

        if (!$this->isValid()) {
            throw new ValidationException('description', 'Descrição deve ter pelo menos 10 caracteres');
        }
    }

    public function value(): string
    {
        return $this->description;
    }

    private function isValid(): bool
    {
        return mb_strlen(trim($this->description)) >= 10;
    }
}
