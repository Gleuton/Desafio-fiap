<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Student;

use DateTime;
use FiapAdmin\Models\Student\Student;
use FiapAdmin\Models\Name;
use FiapAdmin\Models\Cpf;
use FiapAdmin\Models\Email;
use FiapAdmin\Models\Password;
use PHPUnit\Framework\TestCase;

class StudentTest extends TestCase
{
    public function testGettersReturnCorrectValues(): void
    {
        $nameMock = $this->createMock(Name::class);
        $nameMock->method('value')->willReturn('Maria Silva');

        $cpfMock = $this->createMock(Cpf::class);
        $cpfMock->method('value')->willReturn('12345678909');

        $emailMock = $this->createMock(Email::class);
        $emailMock->method('value')->willReturn('maria.silva@example.com');

        $birthdate = new DateTime('2000-05-15');

        $passwordMock = $this->createMock(Password::class);
        $passwordMock->method('value')->willReturn('hashed_password');

        $student = new Student(
            42,
            $nameMock,
            $cpfMock,
            $emailMock,
            $birthdate,
            $passwordMock
        );

        $this->assertSame(42, $student->id());
        $this->assertSame('Maria Silva', $student->name());
        $this->assertSame('12345678909', $student->cpf());
        $this->assertSame('maria.silva@example.com', $student->email());
        $this->assertSame('2000-05-15', $student->birthdate());
        $this->assertSame('hashed_password', $student->password());
        $this->assertSame(Student::ROLE, 'student');
    }

    public function testNullableFields(): void
    {
        $nameMock = $this->createMock(Name::class);
        $cpfMock = $this->createMock(Cpf::class);
        $emailMock = $this->createMock(Email::class);

        $birthdate = new DateTime('1995-12-01');

        $student = new Student(
            null,
            $nameMock,
            $cpfMock,
            $emailMock,
            $birthdate,
            null
        );

        $this->assertNull($student->id());
        $this->assertSame('1995-12-01', $student->birthdate());
        $this->assertNull($student->password());
    }
}