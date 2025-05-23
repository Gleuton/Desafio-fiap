<?php

namespace Tests\Unit\Controllers;

use FiapAdmin\Controllers\CourseController;
use FiapAdmin\Exceptions\ValidationException;
use FiapAdmin\Models\Course\Course;
use FiapAdmin\Models\Course\CourseService;
use Laminas\Diactoros\Response\JsonResponse;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\StreamInterface;

class CourseControllerTest extends TestCase
{
    private MockObject|CourseService $courseMock;
    private CourseController $controller;

    protected function setUp(): void
    {
        $this->courseMock = $this->createMock(CourseService::class);
        $this->controller = new CourseController($this->courseMock);
    }


    public function testIndexReturnsPaginatedData(): void
    {
        $request = $this->createMock(Request::class);
        $queryParams = ['page' => 1, 'limit' => 10];
        $requestData = ['data' => [], 'total' => 0];

        $request->method('getQueryParams')->willReturn($queryParams);
        $this->courseMock->method('index')->with(1, 10)->willReturn($requestData);

        $response = $this->controller->index($request);

        $this->assertSame($requestData, $response->getPayload());
    }

    public function testCreateReturns201OnSuccess(): void
    {
        $request = $this->createMock(Request::class);
        $body = $this->createMock(StreamInterface::class);

        $body->method('getContents')->willReturn(json_encode([
            'name' => 'PHP Course',
            'description' => 'Learn PHP programming with this comprehensive course'
        ]));
        $request->method('getBody')->willReturn($body);

        $this->courseMock->method('create')
            ->with($this->isInstanceOf(Course::class))
            ->willReturn([
                'success' => true,
                'id' => 1
            ]);

        $response = $this->controller->create($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testCreateReturns422OnValidationFailure(): void
    {
        $request = $this->createMock(Request::class);
        $body = $this->createMock(StreamInterface::class);

        $body->method('getContents')->willReturn(json_encode([
            'name' => '',
            'description' => 'Test description'
        ], JSON_THROW_ON_ERROR));
        $request->method('getBody')->willReturn($body);

        // Mock ValidationException being thrown when creating the Course
        $this->courseMock->method('create')
            ->will($this->throwException(new ValidationException('name', 'Name is required')));

        $response = $this->controller->create($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testShowReturnsCourseData(): void
    {
        $request = $this->createMock(Request::class);
        $courseData = ['id' => 1, 'name' => 'PHP Course'];

        $this->courseMock->method('findById')->with(1)->willReturn($courseData);

        $response = $this->controller->show($request, 1);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame($courseData, $response->getPayload());
    }

    public function testDeleteReturns201OnSuccess(): void
    {
        $request = $this->createMock(Request::class);

        $this->courseMock->method('delete')->with(1)->willReturn([
            'success' => true
        ]);

        $response = $this->controller->delete($request, 1);

        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testDeleteReturns422OnError(): void
    {
        $request = $this->createMock(Request::class);

        $this->courseMock->method('delete')->with(1)->willReturn([
            'success' => false,
            'errors' => ['message' => 'Cannot delete course in use']
        ]);

        $response = $this->controller->delete($request, 1);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testUpdateReturns201OnSuccess(): void
    {
        $request = $this->createMock(Request::class);
        $body = $this->createMock(StreamInterface::class);

        $body->method('getContents')->willReturn(json_encode([
            'name' => 'Updated Name',
            'description' => 'Updated description for the course'
        ]));
        $request->method('getBody')->willReturn($body);

        $this->courseMock->method('update')
            ->with($this->isInstanceOf(Course::class))
            ->willReturn([
                'success' => true
            ]);

        $response = $this->controller->update($request, 1);

        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testUpdateReturns422OnValidationError(): void
    {
        $request = $this->createMock(Request::class);
        $body = $this->createMock(StreamInterface::class);

        $body->method('getContents')->willReturn(json_encode([
            'name' => '',
            'description' => 'Test description'
        ]));
        $request->method('getBody')->willReturn($body);

        // Mock ValidationException being thrown when creating the Course
        $this->courseMock->method('update')
            ->will($this->throwException(new ValidationException('name', 'Name cannot be empty')));

        $response = $this->controller->update($request, 1);

        $this->assertEquals(422, $response->getStatusCode());
    }
}
