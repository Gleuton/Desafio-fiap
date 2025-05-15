<?php

namespace Tests\Unit\Models;

use FiapAdmin\Exceptions\ValidationException;
use FiapAdmin\Models\Cpf;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class CpfTest extends TestCase
{
    #[DataProvider('validCpfProvider')]
    public function testValidCpfCreatesInstance(string $input, string $expected): void
    {
        $cpf = new Cpf($input);
        $this->assertSame($expected, $cpf->value());
    }

    public static function validCpfProvider(): array
    {
        return [
            'plain digits' => ['11144477735', '11144477735'],
            'formatted with dots and dash' => ['111.444.777-35', '11144477735'],
        ];
    }

    #[DataProvider('invalidCpfProvider')]
    public function testInvalidCpfThrowsException(string $input): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Cpf inválido');
        new Cpf($input);
    }

    public static function invalidCpfProvider(): array
    {
        return [
            'too short' => ['123'],
            'too long' => ['123456789012'],
            'repeated digits' => ['11111111111'],
            'invalid check digits' => ['11144477734'],
            'formatted invalid' => ['111.444.777-34'],
        ];
    }
}
