<?php

namespace Tests\Unit\Models;

use FiapAdmin\Exceptions\ValidationException;
use FiapAdmin\Models\Email;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class EmailTest extends TestCase
{
    #[DataProvider('validEmailProvider')]
    public function testValidEmailCreatesInstance(string $input): void
    {
        $email = new Email($input);
        $this->assertSame($input, $email->value());
    }

    public static function validEmailProvider(): array
    {
        return [
            'simple lowercase'       => ['user@example.com'],
            'uppercase trimmed'      => ['  USER@EXAMPLE.COM  '],
            'mixed case'             => ['User.Name@Example.Co.Uk'],
            'with plus alias'        => ['alias+test@gmail.com'],
            'with subdomain'         => ['user@mail.server.example.com'],
        ];
    }


    #[DataProvider('invalidEmailProvider')]
    public function testInvalidEmailThrowsException(string $input): void
    {
        $this->expectException(ValidationException::class);
        new Email($input);
    }

    public static function invalidEmailProvider(): array
    {
        return [
            'no at'                  => ['userexample.com'],
            'multiple ats'           => ['user@@example.com'],
            'leading dot'            => ['.user@example.com'],
            'trailing dot'           => ['user.@example.com'],
            'consecutive dots'       => ['user..name@example.com'],
            'missing domain'         => ['user@'],
            'missing local part'     => ['@example.com'],
            'spaces inside'          => ['user@ example.com'],
            'unicode chars'          => ['usér@example.com'],
        ];
    }
}
