<?php

namespace Tests\Unit\Models;

use FiapAdmin\Exceptions\ValidationException;
use FiapAdmin\Models\Birthdate;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use DateTime;

class BirthdateTest extends TestCase
{
    #[DataProvider('validBirthdateProvider')]
    public function testValidBirthdateCreatesInstance(string $input, string $expected): void
    {
        $birthdate = new Birthdate($input);
        $this->assertSame($expected, $birthdate->value());
    }

    public static function validBirthdateProvider(): array
    {
        return [
            'standard format' => ['1990-01-15', '1990-01-15'],
            'past date' => ['2000-12-31', '2000-12-31'],
        ];
    }

    public function testTodayDateIsValid(): void
    {
        $today = (new DateTime())->format('Y-m-d');
        $birthdate = new Birthdate($today);
        $this->assertSame($today, $birthdate->value());
    }

    public function testEmptyBirthdateThrowsException(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Data de nascimento é um campo obrigatório');
        new Birthdate('');
    }

    #[DataProvider('invalidDateFormatProvider')]
    public function testInvalidDateFormatThrowsException(string $input): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Data de nascimento inválida');
        new Birthdate($input);
    }

    public static function invalidDateFormatProvider(): array
    {
        return [
            'invalid format' => ['not-a-date'],
            'invalid month' => ['2000-13-01'],
            'completely invalid date' => ['2000-02-XYZ'],
        ];
    }

    public function testFutureBirthdateThrowsException(): void
    {
        $futureDate = (new DateTime())->modify('+1 day')->format('Y-m-d');

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Data de nascimento não pode ser no futuro');
        new Birthdate($futureDate);
    }
}
