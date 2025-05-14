<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use FiapAdmin\Exceptions\ValidationException;
use FiapAdmin\Models\Description;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class DescriptionTest extends TestCase
{
    #[DataProvider('validDescriptionProvider')]
    public function testValidDescriptionCreatesInstance(string $input): void
    {
        $description = new Description($input);
        $this->assertSame($input, $description->value());
    }

    public static function validDescriptionProvider(): array
    {
        return [
            'exactly 10 chars' => ['Descrição1'],
            'longer description' => ['Esta é uma descrição mais longa para o teste'],
            'with special chars' => ['Descrição com caracteres especiais: @#$%&*()'],
            'with numbers' => ['Descrição 12345 com números'],
        ];
    }

    #[DataProvider('invalidDescriptionProvider')]
    public function testInvalidDescriptionThrowsException(string $input): void
    {
        $this->expectException(ValidationException::class);
        new Description($input);
    }

    public static function invalidDescriptionProvider(): array
    {
        return [
            'too short' => ['Curta'],
            'empty string' => [''],
            'only spaces' => ['          '],
            'nine chars' => ['Descrição'],
        ];
    }
}