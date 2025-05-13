<?php

namespace FiapAdmin\Models;

use FiapAdmin\Exceptions\ValidationException;

readonly class Cpf
{
    /**
     * @throws ValidationException
     */
    public function __construct(private string $cpf)
    {
        if (!$this->isValid()) {
            throw new ValidationException('cpf', 'Cpf inválido');
        }
    }

    public function value(): string
    {
        return $this->cpf;
    }

    private function isValid(): bool
    {
        $cleanedCpf = $this->sanitizeCpf();

        if ($this->hasInvalidLength($cleanedCpf) || $this->hasRepeatedDigits($cleanedCpf)) {
            return false;
        }

        return $this->hasValidCheckDigits($cleanedCpf);
    }

    private function sanitizeCpf(): string
    {
        return preg_replace('/[^0-9]/', '', $this->cpf);
    }

    private function hasInvalidLength(string $cpf): bool
    {
        return strlen($cpf) !== 11;
    }

    private function hasRepeatedDigits(string $cpf): bool
    {
        return preg_match('/(\d)\1{10}/', $cpf);
    }

    private function hasValidCheckDigits(string $cpf): bool
    {
        $sum = 0;
        for ($i = 0, $weight = 10; $i < 9; $i++) {
            $sum += (int) $cpf[$i] * $weight--;
        }
        $firstCheckDigit = ($sum % 11) < 2 ? 0 : 11 - ($sum % 11);

        if ($firstCheckDigit !== (int) $cpf[9]) {
            return false;
        }

        $sum = 0;
        for ($i = 0, $weight = 11; $i < 10; $i++) {
            $sum += (int) $cpf[$i] * $weight--;
        }
        $secondCheckDigit = ($sum % 11) < 2 ? 0 : 11 - ($sum % 11);

        return $secondCheckDigit === (int) $cpf[10];
    }
}