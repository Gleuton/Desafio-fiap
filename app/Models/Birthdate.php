<?php

namespace FiapAdmin\Models;

use DateTime;
use FiapAdmin\Exceptions\ValidationException;

readonly class Birthdate
{
    private DateTime $date;

    /**
     * @throws ValidationException
     */
    public function __construct(string $birthdate)
    {
        if (empty($birthdate)) {
            throw new ValidationException('birthdate', 'Data de nascimento é um campo obrigatório');
        }

        try {
            $this->date = new DateTime($birthdate);
        } catch (\Exception $e) {
            throw new ValidationException('birthdate', 'Data de nascimento inválida');
        }

        if (!$this->isValid()) {
            throw new ValidationException('birthdate', 'Data de nascimento não pode ser no futuro');
        }
    }

    public function value(): string
    {
        return $this->date->format('Y-m-d');
    }

    private function isValid(): bool
    {
        $now = new DateTime();
        return $this->date <= $now;
    }
}