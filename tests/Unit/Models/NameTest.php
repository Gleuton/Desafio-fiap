<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use FiapAdmin\Exceptions\ValidationException;
use FiapAdmin\Models\Name;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class NameTest extends TestCase
{
    #[DataProvider('validNameProvider')]
    public function testValidNameCreatesInstance(string $input): void
    {
        $name = new Name($input);
        $this->assertSame($input, $name->value());
    }

    public static function validNameProvider(): array
    {
        return [
            'exactly 4 chars' => ['John'],
            'longer name' => ['Elizabeth'],
            'with spaces' => ['Ana Maria'],
            'with hyphen' => ['Jean-Luc'],
            'with numbers' => ['John123'],
            'with apostrophe' => ['O\'Connor'],
            'with leading/trailing spaces' => [' John '],
        ];
    }

    #[DataProvider('invalidNameProvider')]
    public function testInvalidNameThrowsException(string $input): void
    {
        $this->expectException(ValidationException::class);
        new Name($input);
    }

    public static function invalidNameProvider(): array
    {
        return [
            'too short' => ['Tom'],
            'empty string' => [''],
            'three spaces' => ['   '],
            'three chars letters' => ['Ana'],
            'four chars with spaces' => ['A B'],
            'exactly 3 chars with trailing space' => ['Ana '],
            'exactly 3 chars with leading space' => [' Ana'],
        ];
    }
}
