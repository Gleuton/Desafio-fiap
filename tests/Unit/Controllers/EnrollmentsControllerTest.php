<?php

namespace Tests\Unit\Controllers;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use FiapAdmin\Controllers\EnrollmentsController;
use FiapAdmin\Models\Enrollment\Enrollments;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

class EnrollmentsControllerTest extends TestCase
{
    private MockObject|Enrollments $enrollmentsMock;
    private MockObject|EnrollmentsController $controller;

    protected function setUp(): void
    {
        $this->enrollmentsMock = $this->createMock(Enrollments::class);
        $this->controller = new EnrollmentsController();
        $this->setPrivateProperty($this->controller, $this->enrollmentsMock);
    }

    private function setPrivateProperty($object, $value): void
    {
        $reflection = new \ReflectionClass($object);
        $reflectionProperty = $reflection->getProperty('enrollments');
        $reflectionProperty->setValue($object, $value);
    }

    public function testCreateSuccess(): void
    {
        $requestMock = $this->createMock(ServerRequestInterface::class);
        $streamMock = $this->createMock(StreamInterface::class);
        $streamMock->method('getContents')->willReturn(json_encode(['student_id' => 1, 'course_id' => 2]));
        $requestMock->method('getBody')->willReturn($streamMock);

        $this->enrollmentsMock->expects($this->once())
            ->method('create')
            ->with(['student_id' => 1, 'course_id' => 2])
            ->willReturn(['success' => true, 'data' => ['id' => 1]]);

        $response = $this->controller->create($requestMock);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(['success' => true, 'data' => ['id' => 1]], json_decode($response->getBody(), true));
        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testCreateFailure(): void
    {
        $requestMock = $this->createMock(ServerRequestInterface::class);
        $streamMock = $this->createMock(StreamInterface::class);
        $streamMock->method('getContents')->willReturn(json_encode(['student_id' => 1, 'course_id' => 2]));
        $requestMock->method('getBody')->willReturn($streamMock);

        $this->enrollmentsMock->expects($this->once())
            ->method('create')
            ->with(['student_id' => 1, 'course_id' => 2])
            ->willReturn(['success' => false, 'errors' => ['Invalid data']]);

        $response = $this->controller->create($requestMock);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(['Invalid data'], json_decode($response->getBody(), true));
        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testListByCurses(): void
    {
        $courseId = 1;

        $this->enrollmentsMock->expects($this->once())
            ->method('listByCurses')
            ->with($courseId)
            ->willReturn(['enrollments' => [['id' => 1, 'student_id' => 10, 'course_id' => 1]]]);

        $response = $this->controller->listByCurses($courseId);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(['enrollments' => [['id' => 1, 'student_id' => 10, 'course_id' => 1]]], json_decode($response->getBody(), true));
    }

    public function testDelete(): void
    {
        $id = 1;

        $this->enrollmentsMock->expects($this->once())
            ->method('delete')
            ->with($id);

        $response = $this->controller->delete($id);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals([], json_decode($response->getBody(), true));
        $this->assertEquals(201, $response->getStatusCode());
    }
}