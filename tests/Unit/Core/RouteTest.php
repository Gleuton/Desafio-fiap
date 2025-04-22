<?php

namespace Tests\Unit\Core;

use Core\Router\Route;
use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase
{
    public function testMatchesWithExactPattern(): void
    {
        $route = new Route('/home', function () {});
        $params = [];
        $this->assertTrue($route->matches('/home', $params));
        $this->assertEmpty($params);
    }

    public function testMatchesWithParameters(): void
    {
        $route = new Route('/user/{id}', function () {});
        $params = [];
        $this->assertTrue($route->matches('/user/123', $params));
        $this->assertEquals(['1' => '123'], $params);
    }

    public function testDoesNotMatchWithIncorrectPattern(): void
    {
        $route = new Route('/home', function () {});
        $params = [];
        $this->assertFalse($route->matches('/about', $params));
        $this->assertEmpty($params);
    }

    public function testGetCallbackReturnsCallable(): void
    {
        $callback = function () {};
        $route = new Route('/home', $callback);
        $this->assertSame($callback, $route->getCallback());
    }

    public function testGetMiddlewaresReturnsEmptyArrayByDefault(): void
    {
        $route = new Route('/home', function () {});
        $this->assertEmpty($route->getMiddlewares());
    }

    public function testGetMiddlewaresReturnsProvidedMiddlewares(): void
    {
        $middlewares = ['auth', 'log'];
        $route = new Route('/home', function () {}, $middlewares);
        $this->assertEquals($middlewares, $route->getMiddlewares());
    }

    public function testGetPatternReturnsEscapedPattern(): void
    {
        $route = new Route('/user/{id}', function () {});
        $expectedPattern = '/^\/user\/([^\/]+)$/';
        $this->assertEquals($expectedPattern, $route->getPattern());
    }
}
