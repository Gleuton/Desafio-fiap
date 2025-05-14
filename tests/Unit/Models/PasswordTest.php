<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use FiapAdmin\Exceptions\ValidationException;
use FiapAdmin\Models\Password;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class PasswordTest extends TestCase
{
    #[DataProvider('validPasswordProvider')]
    public function testValidPasswordCreatesHashAndVerifies(string $rawPassword): void
    {
        $password = new Password($rawPassword);
        $hashed = $password->value();

        $this->assertNotSame($rawPassword, $hashed);

        $this->assertTrue(password_verify($rawPassword, $hashed));
    }

    public static function validPasswordProvider(): array
    {
        return [
            'min requirements' => ['Abcdef1!'],
            'longer complex' => ['ComplexPass123$'],
            'symbols at end' => ['Aa1!aaaa'],
            'symbols throughout' => ['P@ssw0rd!Secure'],
        ];
    }

    #[DataProvider('invalidPasswordProvider')]
    public function testInvalidPasswordThrowsException(string $rawPassword): void
    {
        $this->expectException(ValidationException::class);
        new Password($rawPassword);
    }

    public static function invalidPasswordProvider(): array
    {
        return [
            'too short' => ['A1!a'],
            'no uppercase' => ['abcdef1!'],
            'no lowercase' => ['ABCDEF1!'],
            'no digit' => ['Abcdefg!'],
            'no symbol' => ['Abcdef12'],
            'missing multiple rules' => ['abc12345'],
            'spaces included' => ['Abc 123!'],
        ];
    }
}
