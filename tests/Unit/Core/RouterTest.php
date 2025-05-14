<?php

namespace Tests\Unit\Core;

use Core\Exceptions\HttpException;
use Core\Router\Route;
use Core\Router\RouteDispatcher;
use Exception;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\Diactoros\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

class RouterTest extends TestCase
{
    private RouteDispatcher $router;
    private ServerRequestInterface $request;
    private ContainerInterface $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = $this->createMock(ContainerInterface::class);
        $this->router = new RouteDispatcher($this->container);

        $serverParams = [
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/',
            'SCRIPT_NAME' => '/index.php',
            'PHP_SELF' => '/index.php',
            'SERVER_NAME' => 'localhost',
            'SERVER_PORT' => '80',
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'HTTP_HOST' => 'localhost',
            'FILES' => []
        ];

        $this->request = ServerRequestFactory::fromGlobals($serverParams);
    }

    public function testAddRouteStoresCorrectly(): void
    {
        $callback = function () { return 'response'; };
        $this->router->add('GET', '/test', $callback);

        $routes = $this->router->getRoutes();

        $this->assertArrayHasKey('get', $routes);
        $this->assertNotEmpty($routes['get']);
        $this->assertInstanceOf(Route::class, $routes['get'][0]);
        $this->assertEquals('/^\/test$/', $routes['get'][0]->getPattern());
    }

    public function testHandleValidRouteWithCallback(): void
    {
        $callback = static fn() => 'Hello World!';
        $this->router->add('GET', '/test', $callback);

        $request = $this->request->withMethod('GET')
            ->withUri(new Uri('/test'));

        $response = $this->router->handle($request);

        $this->assertInstanceOf(HtmlResponse::class, $response);
        $this->assertEquals('Hello World!', (string) $response->getBody());
    }

    public function testInvalidMiddlewareClass(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Middleware InvalidMiddleware não encontrado");

        $this->router->add('GET', '/test', function () {}, ['InvalidMiddleware']);
        $request = $this->request->withMethod('GET')->withUri(new Uri('/test'));

        $this->router->handle($request);
    }

    public function testMiddlewareNotImplementingInterface(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Middleware stdClass não implementa MiddlewareInterface");

        $this->router->add('GET', '/test', function () {}, [stdClass::class]);
        $request = $this->request->withMethod('GET')->withUri(new Uri('/test'));

        $this->router->handle($request);
    }

    public function testNoMatchingRouteThrows404(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionCode(404);

        $request = $this->request->withMethod('GET')
            ->withUri(new Uri('/invalid'));
        $this->router->handle($request);
    }

    public function testResponseFromCallback(): void
    {
        $callback = static fn() => ['data' => 'success'];
        $this->router->add('GET', '/json', $callback);
        $request = $this->request->withMethod('GET')
            ->withUri(new Uri('/json'));
        $response = $this->router->handle($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(['data' => 'success'], json_decode((string) $response->getBody(), true));

        $responseObj = new HtmlResponse('Custom Response');
        $callback = static fn() => $responseObj;
        $this->router->add('GET', '/custom', $callback);
        $request = $this->request->withMethod('GET')
            ->withUri(new Uri('/custom'));
        $this->assertSame($responseObj, $this->router->handle($request));
    }
}
