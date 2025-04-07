<?php

namespace Tests\Unit\Controllers;


use FiapAdmin\Controllers\CourseController;
use FiapAdmin\Models\Course\Course;
use Laminas\Diactoros\Response\JsonResponse;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

class CourseControllerTest extends TestCase
{
    private Course|MockObject $courseMock;
    private CourseController|MockObject $controller;

    protected function setUp(): void
    {
        $this->courseMock = $this->createMock(Course::class);
        $this->controller = new CourseController();
        $this->setPrivateProperty($this->controller, 'course', $this->courseMock);
    }

    private function setPrivateProperty($object, $property, $value): void
    {
        $reflection = new \ReflectionClass($object);
        $reflectionProperty = $reflection->getProperty($property);
        $reflectionProperty->setValue($object, $value);
    }

    public function testIndex(): void
    {
        $requestMock = $this->createMock(ServerRequestInterface::class);
        $requestMock->method('getQueryParams')->willReturn(['page' => 2, 'limit' => 5]);

        $this->courseMock->expects($this->once())
            ->method('index')
            ->with(2, 5)
            ->willReturn(['data' => ['course1', 'course2']]);

        $response = $this->controller->index($requestMock);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(['data' => ['course1', 'course2']], json_decode($response->getBody(), true));
    }

    public function testCreateSuccess(): void
    {
        $requestMock = $this->createMock(ServerRequestInterface::class);
        $streamMock = $this->createMock(StreamInterface::class);
        $streamMock->method('getContents')->willReturn(json_encode(['name' => 'New Course']));
        $requestMock->method('getBody')->willReturn($streamMock);

        $this->courseMock->expects($this->once())
            ->method('create')
            ->with(['name' => 'New Course'])
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
        $streamMock->method('getContents')->willReturn(json_encode(['name' => 'Invalid Course']));
        $requestMock->method('getBody')->willReturn($streamMock);

        $this->courseMock->expects($this->once())
            ->method('create')
            ->with(['name' => 'Invalid Course'])
            ->willReturn(['success' => false, 'errors' => ['Invalid data']]);

        $response = $this->controller->create($requestMock);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(['Invalid data'], json_decode($response->getBody(), true));
        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testShow(): void
    {
        $id = '123';
        $this->courseMock->expects($this->once())
            ->method('findById')
            ->with($id)
            ->willReturn(['id' => $id, 'name' => 'Course Name']);

        $response = $this->controller->show($id);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(['id' => $id, 'name' => 'Course Name'], json_decode($response->getBody(), true));
    }

    public function testDeleteSuccess(): void
    {
        $id = 1;

        $this->courseMock->expects($this->once())
            ->method('delete')
            ->with($id)
            ->willReturn(['success' => true]);

        $response = $this->controller->delete($id);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals([], json_decode($response->getBody(), true));
        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testUpdateSuccess(): void
    {
        $id = 1;
        $requestData = ['name' => 'Updated Course'];

        $requestMock = $this->createMock(ServerRequestInterface::class);
        $streamMock = $this->createMock(StreamInterface::class);
        $streamMock->method('getContents')->willReturn(json_encode($requestData));
        $requestMock->method('getBody')->willReturn($streamMock);

        $this->courseMock->expects($this->once())
            ->method('update')
            ->with($id, $requestData)
            ->willReturn(['success' => true, 'data' => ['id' => $id, 'name' => 'Updated Course']]);

        $response = $this->controller->update($requestMock, $id);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(['success' => true, 'data' => ['id' => $id, 'name' => 'Updated Course']], json_decode($response->getBody(), true));
        $this->assertEquals(201, $response->getStatusCode());
    }
}
