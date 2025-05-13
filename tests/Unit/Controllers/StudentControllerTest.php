<?php

namespace Tests\Unit\Controllers;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use FiapAdmin\Controllers\StudentController;
use FiapAdmin\Models\Student\StudentOperations;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

class StudentControllerTest extends TestCase
{
    private MockObject|StudentOperations $studentMock;
    private MockObject|StudentController $controller;

    protected function setUp(): void
    {
        $this->studentMock = $this->createMock(StudentOperations::class);
        $this->controller = new StudentController();
        $this->setPrivateProperty($this->controller, 'student', $this->studentMock);
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
        $requestMock->method('getQueryParams')->willReturn(['name' => 'John', 'limit' => 10]);

        $this->studentMock->expects($this->once())
            ->method('all')
            ->with('John', 10)
            ->willReturn(['students' => [['id' => 1, 'name' => 'John Doe']]]);

        $response = $this->controller->index($requestMock);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(['students' => [['id' => 1, 'name' => 'John Doe']]], json_decode($response->getBody(), true));
    }

    public function testCreateSuccess(): void
    {
        $requestMock = $this->createMock(ServerRequestInterface::class);
        $streamMock = $this->createMock(StreamInterface::class);
        $streamMock->method('getContents')->willReturn(json_encode(['name' => 'Jane Doe']));
        $requestMock->method('getBody')->willReturn($streamMock);

        $this->studentMock->expects($this->once())
            ->method('create')
            ->with(['name' => 'Jane Doe'])
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
        $streamMock->method('getContents')->willReturn(json_encode(['name' => 'Invalid Name']));
        $requestMock->method('getBody')->willReturn($streamMock);

        $this->studentMock->expects($this->once())
            ->method('create')
            ->with(['name' => 'Invalid Name'])
            ->willReturn(['success' => false, 'errors' => ['Invalid data']]);

        $response = $this->controller->create($requestMock);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(['Invalid data'], json_decode($response->getBody(), true));
        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testShow(): void
    {
        $id = '1';

        $this->studentMock->expects($this->once())
            ->method('findById')
            ->with($id)
            ->willReturn(['id' => $id, 'name' => 'John Doe']);

        $response = $this->controller->show($id);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(['id' => $id, 'name' => 'John Doe'], json_decode($response->getBody(), true));
    }

    public function testUpdateSuccess(): void
    {
        $id = 1;
        $requestData = ['name' => 'Updated Name'];

        $requestMock = $this->createMock(ServerRequestInterface::class);
        $streamMock = $this->createMock(StreamInterface::class);
        $streamMock->method('getContents')->willReturn(json_encode($requestData));
        $requestMock->method('getBody')->willReturn($streamMock);

        $this->studentMock->expects($this->once())
            ->method('update')
            ->with($id, $requestData)
            ->willReturn(['success' => true, 'data' => ['id' => $id, 'name' => 'Updated Name']]);

        $response = $this->controller->update($requestMock, $id);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(['success' => true, 'data' => ['id' => $id, 'name' => 'Updated Name']], json_decode($response->getBody(), true));
        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testUpdateFailure(): void
    {
        $id = 1;
        $requestData = ['name' => 'Invalid Name'];

        $requestMock = $this->createMock(ServerRequestInterface::class);
        $streamMock = $this->createMock(StreamInterface::class);
        $streamMock->method('getContents')->willReturn(json_encode($requestData));
        $requestMock->method('getBody')->willReturn($streamMock);

        $this->studentMock->expects($this->once())
            ->method('update')
            ->with($id, $requestData)
            ->willReturn(['success' => false, 'errors' => ['Invalid data']]);

        $response = $this->controller->update($requestMock, $id);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(['Invalid data'], json_decode($response->getBody(), true));
        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testDeleteSuccess(): void
    {
        $id = 1;

        $this->studentMock->expects($this->once())
            ->method('delete')
            ->with($id)
            ->willReturn(['success' => true]);

        $response = $this->controller->delete($id);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals([], json_decode($response->getBody(), true));
        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testDeleteFailure(): void
    {
        $id = 1;

        $this->studentMock->expects($this->once())
            ->method('delete')
            ->with($id)
            ->willReturn(['success' => false, 'errors' => ['Not found']]);

        $response = $this->controller->delete($id);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(['Not found'], json_decode($response->getBody(), true));
        $this->assertEquals(422, $response->getStatusCode());
    }
}