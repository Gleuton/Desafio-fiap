<?php

namespace Tests\Unit\Middlewares;

use FiapAdmin\Middlewares\AuthMiddleware;
use FiapAdmin\Models\Admin\Admin;
use FiapAdmin\Models\Admin\AuthService;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\JsonResponse;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthMiddlewareTest extends TestCase
{
    private MockObject|Admin $adminMock;
    private AuthMiddleware $middleware;

    protected function setUp(): void
    {
        $this->adminMock = $this->createMock(Admin::class);
        $this->middleware = new AuthMiddleware($this->adminMock);
    }

    public function testProcessReturns401WhenTokenIsInvalid(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);

        $request->method('getHeaders')->willReturn(['authorization' => ['Bearer invalid-token']]);
        $this->adminMock->method('isTokenValid')->willReturn(null);

        $response = $this->middleware->process($request, $handler);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals(['error' => 'Token inválido ou sem permissão'], $response->getPayload());
    }

    public function testProcessAddsAdminAttributeToRequest(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);

        $response = new Response();

        $decodedToken = [
            'admin_id' => 1,
            'role' => 'admin'
        ];

        $request->method('getHeaders')->willReturn(['authorization' => ['Bearer valid-token']]);
        $this->adminMock->method('isTokenValid')->willReturn($decodedToken);

        $request->expects($this->once())
            ->method('withAttribute')
            ->with('admin', $decodedToken)
            ->willReturnSelf();

        $handler->method('handle')->willReturn($response);

        $result = $this->middleware->process($request, $handler);

        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    public function testProcessHandlesExceptions(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);

        $request->method('getHeaders')->willReturn(['authorization' => ['Bearer valid-token']]);
        $this->adminMock->method('isTokenValid')->willThrowException(new \Exception('Test exception', 403));

        $response = $this->middleware->process($request, $handler);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals(['error' => 'Test exception'], $response->getPayload());
    }
}
