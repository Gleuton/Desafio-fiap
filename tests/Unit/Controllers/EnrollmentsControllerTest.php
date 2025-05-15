<?php

namespace Tests\Unit\Controllers;

use FiapAdmin\Controllers\EnrollmentsController;
use FiapAdmin\Exceptions\ValidationException;
use FiapAdmin\Models\Enrollment\Enrollment;
use FiapAdmin\Models\Enrollment\EnrollmentService;
use Laminas\Diactoros\Response\JsonResponse;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ServerRequestInterface as RequestInterface;
use Psr\Http\Message\StreamInterface;
use PHPUnit\Framework\TestCase;

class EnrollmentsControllerTest extends TestCase
{
    private MockObject|EnrollmentService $enrollmentServiceMock;
    private EnrollmentsController $controller;

    protected function setUp(): void
    {
        $this->enrollmentServiceMock = $this->createMock(EnrollmentService::class);
        $this->controller = new EnrollmentsController($this->enrollmentServiceMock);
    }

    public function testIndexReturnsData(): void
    {
        $page = 1;
        $limit = 10;
        $responseData = [
            'enrollments' => [
                ['id' => 1, 'user_id' => 100, 'course_id' => 200],
                ['id' => 2, 'user_id' => 101, 'course_id' => 201],
            ],
            'totalPages' => 1,
            'currentPage' => 1
        ];

        $request = $this->createMock(RequestInterface::class);
        $request->method('getQueryParams')->willReturn(['page' => $page, 'limit' => $limit]);

        $this->enrollmentServiceMock->method('index')->with($page, $limit)->willReturn($responseData);

        $response = $this->controller->index($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($responseData, $response->getPayload());
    }

    public function testCreateReturns201OnSuccess(): void
    {
        $requestData = ['student_id' => 1, 'course_id' => 2];
        $responseData = ['success' => true, 'data' => ['id' => 1, 'user_id' => 1, 'course_id' => 2]];

        $stream = $this->createMock(StreamInterface::class);
        $stream->method('getContents')->willReturn(json_encode($requestData));

        $request = $this->createMock(RequestInterface::class);
        $request->method('getBody')->willReturn($stream);

        $this->enrollmentServiceMock->method('create')
            ->willReturnCallback(function (Enrollment $enrollment) use ($responseData) {
                $this->assertEquals(1, $enrollment->userId());
                $this->assertEquals(2, $enrollment->courseId());
                return $responseData;
            });

        $response = $this->controller->create($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals($responseData, $response->getPayload());
    }

    public function testCreateReturns422OnValidationFailure(): void
    {
        $requestData = ['student_id' => 1, 'course_id' => 2];
        $field = 'enrollment';
        $errorMessage = 'Este aluno já está matriculado nesta turma!';

        $stream = $this->createMock(StreamInterface::class);
        $stream->method('getContents')->willReturn(json_encode($requestData));

        $request = $this->createMock(RequestInterface::class);
        $request->method('getBody')->willReturn($stream);

        $this->enrollmentServiceMock->method('create')
            ->willThrowException(new ValidationException($field, $errorMessage));

        $response = $this->controller->create($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(422, $response->getStatusCode());
        $this->assertEquals(['error' => [$field => $errorMessage]], $response->getPayload());
    }

    public function testShowReturnsData(): void
    {
        $enrollmentId = 1;
        $responseData = ['id' => $enrollmentId, 'user_id' => 100, 'course_id' => 200];

        $this->enrollmentServiceMock->method('findById')->with($enrollmentId)->willReturn($responseData);

        $response = $this->controller->show($this->createMock(RequestInterface::class), $enrollmentId);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($responseData, $response->getPayload());
    }

    public function testListByCursesReturnsData(): void
    {
        $courseId = 1;
        $responseData = [
            ['id' => 1, 'student_id' => 100, 'course_id' => $courseId],
            ['id' => 2, 'student_id' => 101, 'course_id' => $courseId],
        ];

        $this->enrollmentServiceMock->method('findByCourseId')->with($courseId)->willReturn($responseData);

        $response = $this->controller->listByCurses($this->createMock(RequestInterface::class), $courseId);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($responseData, $response->getPayload());
    }

    public function testUpdateReturns201OnSuccess(): void
    {
        $enrollmentId = 1;
        $requestData = ['user_id' => 1, 'course_id' => 2];
        $responseData = ['success' => true, 'data' => ['user_id' => 1, 'course_id' => 2]];

        $stream = $this->createMock(StreamInterface::class);
        $stream->method('getContents')->willReturn(json_encode($requestData));

        $request = $this->createMock(RequestInterface::class);
        $request->method('getBody')->willReturn($stream);

        $this->enrollmentServiceMock->method('update')
            ->willReturnCallback(function (Enrollment $enrollment) use ($responseData, $enrollmentId) {
                $this->assertEquals($enrollmentId, $enrollment->id());
                $this->assertEquals(1, $enrollment->userId());
                $this->assertEquals(2, $enrollment->courseId());
                return $responseData;
            });

        $response = $this->controller->update($request, $enrollmentId);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals($responseData, $response->getPayload());
    }

    public function testDeleteCallsServiceAndReturns201(): void
    {
        $enrollmentId = 1;
        $responseData = ['success' => true, 'id' => $enrollmentId];

        $this->enrollmentServiceMock->method('delete')->with($enrollmentId)->willReturn($responseData);

        $response = $this->controller->delete($this->createMock(RequestInterface::class), $enrollmentId);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals([], $response->getPayload());
    }
}
