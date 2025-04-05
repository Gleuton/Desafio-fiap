<?php

namespace Tests\Unit\Core;

use Core\Response;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class ResponseTest extends TestCase
{
    public function testReturnsExistingResponseWhenCallbackReturnsResponse(): void
    {
        $expectedResponse = $this->createMock(ResponseInterface::class);
        $callback = function () use ($expectedResponse) {
            return $expectedResponse;
        };

        $responseHandler = new Response();
        $result = $responseHandler($callback, []);

        $this->assertSame($expectedResponse, $result);
    }

    public function testReturnsHtmlResponseWhenCallbackReturnsString(): void
    {
        $content = 'Hello World!';
        $callback = function () use ($content) {
            return $content;
        };

        $responseHandler = new Response();
        $result = $responseHandler($callback, []);

        $this->assertInstanceOf(HtmlResponse::class, $result);
        $this->assertEquals($content, (string) $result->getBody());
    }

    public function testReturnsJsonResponseWhenCallbackReturnsNonResponse(): void
    {
        $data = ['message' => 'Success'];
        $callback = function () use ($data) {
            return $data;
        };

        $responseHandler = new Response();
        $result = $responseHandler($callback, []);

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(json_encode($data), (string) $result->getBody());
    }

    public function testHandlesExceptionWithHttpStatusCode(): void
    {
        $errorMessage = 'Validation error';
        $httpStatus = 400;
        $exception = new \Exception($errorMessage, $httpStatus);
        $callback = function () use ($exception) {
            throw $exception;
        };

        $responseHandler = new Response();
        $result = $responseHandler($callback, []);

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals($httpStatus, $result->getStatusCode());
        $body = json_decode((string) $result->getBody(), true);
        $this->assertEquals($errorMessage, $body['error']);
        $this->assertEquals($httpStatus, $body['code']);
    }

    public function testHandlesExceptionWithoutStatusCode(): void
    {
        $errorMessage = 'Internal error';
        $exception = new \Exception($errorMessage);
        $callback = function () use ($exception) {
            throw $exception;
        };

        $responseHandler = new Response();
        $result = $responseHandler($callback, []);

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(500, $result->getStatusCode());
        $body = json_decode((string) $result->getBody(), true);
        $this->assertEquals($errorMessage, $body['error']);
        $this->assertEquals(0, $body['code']);
    }
}
