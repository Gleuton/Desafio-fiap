<?php

namespace Tests\Unit\Models\Student;

use FiapAdmin\Models\Student\Student;
use FiapAdmin\Models\Student\StudentService;
use FiapAdmin\Repositories\StudentRepository;
use PHPUnit\Framework\TestCase;

class StudentServiceTest extends TestCase
{
    private StudentRepository $repository;
    private StudentService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(StudentRepository::class);
        $this->service = new StudentService($this->repository);
    }

    public function testFindByIdReturnsRepositoryResult(): void
    {
        $expected = ['id' => 1, 'name' => 'Test'];
        $this->repository->expects($this->once())
            ->method('findOneById')
            ->with(1)
            ->willReturn($expected);

        $result = $this->service->findById(1);
        $this->assertSame($expected, $result);
    }

    public function testAllWithoutNameCallsFindAll(): void
    {
        $expected = [['id' => 1], ['id' => 2]];
        $this->repository->expects($this->once())
            ->method('findAll')
            ->willReturn($expected);

        $result = $this->service->all(null, null);
        $this->assertSame($expected, $result);
    }

    public function testAllWithNameCallsFindAllByName(): void
    {
        $expected = [['id' => 3]];
        $this->repository->expects($this->once())
            ->method('findAllByName')
            ->with('Maria', 5)
            ->willReturn($expected);

        $result = $this->service->all('Maria', 5);
        $this->assertSame($expected, $result);
    }

    public function testCreateReturnsSuccess(): void
    {
        $studentMock = $this->createMock(Student::class);

        $this->repository->expects($this->once())
            ->method('saveStudent')
            ->with($studentMock)
            ->willReturn([]);

        $result = $this->service->create($studentMock);
        $this->assertSame(['success' => []], $result);
    }

    public function testDeleteReturnsErrorWhenHasEnrollments(): void
    {
        $this->repository->expects($this->once())
            ->method('hasEnrollments')
            ->with(10)
            ->willReturn(true);

        $result = $this->service->delete(10);
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('enrollment', $result['errors']);
    }

    public function testDeleteCallsRepositoryWhenNoEnrollments(): void
    {
        $this->repository->method('hasEnrollments')->willReturn(false);
        $this->repository->expects($this->once())
            ->method('delete')
            ->with(20)
            ->willReturn(true);

        $result = $this->service->delete(20);
        $this->assertTrue($result['success']);
        $this->assertSame(20, $result['id']);
    }
}
