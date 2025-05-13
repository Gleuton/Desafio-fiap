<?php

namespace Tests\Unit\Controllers;

use FiapAdmin\Controllers\StudentController;
use FiapAdmin\Exceptions\ValidationException;
use FiapAdmin\Models\Student\Student;
use FiapAdmin\Models\Student\StudentOperations;
use Laminas\Diactoros\Response\JsonResponse;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\StreamInterface;

class StudentControllerTest extends TestCase
{
    private MockObject|StudentOperations $studentOperationsMock;
    private StudentController $controller;

    protected function setUp(): void
    {
        $this->studentOperationsMock = $this->createMock(StudentOperations::class);
        $this->controller = new StudentController($this->studentOperationsMock);
    }

    public function testIndexCallsAllWithQueryParams(): void
    {
        $queryParams = ['name' => 'John', 'limit' => 5];
        $responseData = [['id' => 1, 'name' => 'John Doe']];

        $request = $this->createMock(Request::class);
        $request->method('getQueryParams')->willReturn($queryParams);
        $this->studentOperationsMock->method('all')->with('John', 5)->willReturn($responseData);

        $response = $this->controller->index($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals($responseData, $response->getPayload());
    }

    public function testCreateReturns201OnSuccess(): void
    {
        $data = [
            'name' => 'John Doe',
            'cpf' => '123.456.789-09',
            'email' => 'john@example.com',
            'birthdate' => '1990-01-01',
            'password' => 'Password@123',
        ];

        $result = ['success' => true, 'id' => 1];

        $stream = $this->createMock(StreamInterface::class);
        $stream->method('getContents')->willReturn(json_encode($data));

        $request = $this->createMock(Request::class);
        $request->method('getBody')->willReturn($stream);

        $this->studentOperationsMock->method('create')->willReturn($result);

        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('student');
        $method->invoke($this->controller, $data);

        $response = $this->controller->create($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals($result, $response->getPayload());
    }

    public function testCreateReturns422OnValidationExceptionFromStudentOperations(): void
    {
        $data = [
            'name' => 'John Doe',
            'cpf' => '123.456.789-09',
            'email' => 'john@example.com',
            'birthdate' => '1990-01-01',
            'password' => 'Password@123',
        ];

        $stream = $this->createMock(StreamInterface::class);
        $stream->method('getContents')->willReturn(json_encode($data));

        $request = $this->createMock(Request::class);
        $request->method('getBody')->willReturn($stream);

        $exceptionMessage = 'Name is too short';
        $this->studentOperationsMock->method('create')->willThrowException(new ValidationException('name', $exceptionMessage));

        $response = $this->controller->create($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(422, $response->getStatusCode());

        $this->assertEquals(['error' => ['name' => 'Name is too short']], $response->getPayload());
    }

    public function testCreateReturns422WhenFactoryThrowsValidationException(): void
    {
        $data = [
            'name' => '',
            'cpf' => 'invalid',
            'email' => 'invalid',
            'birthdate' => 'invalid',
        ];

        $stream = $this->createMock(StreamInterface::class);
        $stream->method('getContents')->willReturn(json_encode($data));

        $request = $this->createMock(Request::class);
        $request->method('getBody')->willReturn($stream);

        $this->studentOperationsMock->expects($this->never())->method('create');

        $response = $this->controller->create($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(422, $response->getStatusCode());
        $this->assertArrayHasKey('error', $response->getPayload());
    }

    public function testCreateReturns500OnJsonException(): void
    {
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('getContents')->willReturn('invalid-json');

        $request = $this->createMock(Request::class);
        $request->method('getBody')->willReturn($stream);

        $response = $this->controller->create($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertArrayHasKey('Error', $response->getPayload());
    }

    public function testCreateReturns500OnOtherException(): void
    {
        $data = [
            'name' => 'John Doe',
            'cpf' => '123.456.789-09',
            'email' => 'john@example.com',
            'birthdate' => '1990-01-01',
        ];

        $stream = $this->createMock(StreamInterface::class);
        $stream->method('getContents')->willReturn(json_encode($data));

        $request = $this->createMock(Request::class);
        $request->method('getBody')->willReturn($stream);

        $this->studentOperationsMock->method('create')->willThrowException(new \Exception('Database error'));

        $response = $this->controller->create($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals(['Error' => 'Database error'], $response->getPayload());
    }

    public function testShowReturnsStudentData(): void
    {
        $studentData = ['id' => 1, 'name' => 'John Doe'];

        $request = $this->createMock(Request::class);
        $this->studentOperationsMock->method('findById')->with(1)->willReturn($studentData);

        $response = $this->controller->show($request, 1);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals($studentData, $response->getPayload());
    }

    public function testUpdateReturns201OnSuccess(): void
    {
        $data = [
            'id' => 1,
            'name' => 'John Doe',
            'cpf' => '123.456.789-09',
            'email' => 'john@example.com',
            'birthdate' => '1990-01-01',
            'password' => 'Password@123',
        ];

        $studentMock = $this->createMock(Student::class);
        $result = ['success' => true];

        $stream = $this->createMock(StreamInterface::class);
        $stream->method('getContents')->willReturn(json_encode($data));

        $request = $this->createMock(Request::class);
        $request->method('getBody')->willReturn($stream);

        $this->studentOperationsMock->method('update')->willReturn($result);

        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('student');
        $method->invoke($this->controller, $data);

        $response = $this->controller->update($request, 1);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals($result, $response->getPayload());
    }

    public function testDeleteReturns201OnSuccess(): void
    {
        $request = $this->createMock(Request::class);
        $this->studentOperationsMock->method('delete')->with(1)->willReturn(['success' => true]);

        $response = $this->controller->delete($request, 1);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals([], $response->getPayload());
    }

    public function testDeleteReturns422OnError(): void
    {
        $request = $this->createMock(Request::class);
        $this->studentOperationsMock->method('delete')->with(1)->willReturn([
            'success' => false,
            'errors' => ['message' => 'Cannot delete student with enrollments'],
        ]);

        $response = $this->controller->delete($request, 1);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(422, $response->getStatusCode());
        $this->assertEquals(['message' => 'Cannot delete student with enrollments'], $response->getPayload());
    }

    public function testUpdateReturns422WhenValidationFails(): void
    {
        $data = [
            'id' => 1,
            'name' => '',
            'cpf' => 'invalid',
            'email' => 'invalid',
            'birthdate' => 'invalid',
        ];

        $stream = $this->createMock(StreamInterface::class);
        $stream->method('getContents')->willReturn(json_encode($data));

        $request = $this->createMock(Request::class);
        $request->method('getBody')->willReturn($stream);

        $this->studentOperationsMock->expects($this->never())->method('update');

        $response = $this->controller->update($request, 1);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(422, $response->getStatusCode());
        $this->assertArrayHasKey('error', $response->getPayload());
    }

    public function testUpdateReturns500OnJsonException(): void
    {
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('getContents')->willReturn('invalid-json');

        $request = $this->createMock(Request::class);
        $request->method('getBody')->willReturn($stream);

        $response = $this->controller->update($request, 1);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertArrayHasKey('Error', $response->getPayload());
    }

    public function testUpdateReturns500OnOtherException(): void
    {
        $data = [
            'id' => 1,
            'name' => 'John Doe',
            'cpf' => '123.456.789-09',
            'email' => 'john@example.com',
            'birthdate' => '1990-01-01',
        ];

        $stream = $this->createMock(StreamInterface::class);
        $stream->method('getContents')->willReturn(json_encode($data));

        $request = $this->createMock(Request::class);
        $request->method('getBody')->willReturn($stream);

        $this->studentOperationsMock->method('update')->willThrowException(new \Exception('Database error'));

        $response = $this->controller->update($request, 1);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals(['Error' => 'Database error'], $response->getPayload());
    }
}