<?php

namespace Tests\Unit\Controllers;

use FiapAdmin\Controllers\EnrollmentsController;
use FiapAdmin\Models\Enrollment\Enrollments;
use Laminas\Diactoros\Response\JsonResponse;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ServerRequestInterface as RequestInterface;
use Psr\Http\Message\StreamInterface;
use PHPUnit\Framework\TestCase;

class EnrollmentsControllerTest extends TestCase
{
    private MockObject|Enrollments $enrollmentsMock;
    private EnrollmentsController $controller;

    protected function setUp(): void
    {
        $this->enrollmentsMock = $this->createMock(Enrollments::class);
        $this->controller = new EnrollmentsController($this->enrollmentsMock);
    }

    public function testCreateReturns201OnSuccess(): void
    {
        $requestData = ['student_id' => 1, 'course_id' => 2];
        $responseData = ['success' => true, 'id' => 1];

        $stream = $this->createMock(StreamInterface::class);
        $stream->method('getContents')->willReturn(json_encode($requestData));

        $request = $this->createMock(RequestInterface::class);
        $request->method('getBody')->willReturn($stream);

        $this->enrollmentsMock->method('create')->with($requestData)->willReturn($responseData);

        $response = $this->controller->create($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals($responseData, $response->getPayload());
    }

    public function testCreateReturns422OnValidationFailure(): void
    {
        $requestData = ['student_id' => '', 'course_id' => 2];
        $responseData = ['success' => false, 'errors' => ['student_id' => 'Invalid student ID']];

        $stream = $this->createMock(StreamInterface::class);
        $stream->method('getContents')->willReturn(json_encode($requestData));

        $request = $this->createMock(RequestInterface::class);
        $request->method('getBody')->willReturn($stream);

        $this->enrollmentsMock->method('create')->with($requestData)->willReturn($responseData);

        $response = $this->controller->create($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(422, $response->getStatusCode());
        $this->assertEquals($responseData['errors'], $response->getPayload());
    }

    public function testCreateThrowsJsonExceptionOnInvalidJson(): void
    {
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('getContents')->willReturn('invalid-json');

        $request = $this->createMock(RequestInterface::class);
        $request->method('getBody')->willReturn($stream);

        $this->expectException(\JsonException::class);

        $this->controller->create($request);
    }

    public function testListByCursesReturnsData(): void
    {
        $courseId = 1;
        $responseData = [
            ['id' => 1, 'student_id' => 100, 'course_id' => $courseId],
            ['id' => 2, 'student_id' => 101, 'course_id' => $courseId],
        ];

        $this->enrollmentsMock->method('listByCurses')->with($courseId)->willReturn($responseData);

        $response = $this->controller->listByCurses($this->createMock(RequestInterface::class), $courseId);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($responseData, $response->getPayload());
    }

    public function testDeleteCallsModelAndReturns201(): void
    {
        $enrollmentId = 1;

        $this->enrollmentsMock->expects($this->once())->method('delete')->with($enrollmentId);

        $response = $this->controller->delete($this->createMock(RequestInterface::class), $enrollmentId);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals([], $response->getPayload());
    }
}