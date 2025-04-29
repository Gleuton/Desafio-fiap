<?php

namespace Core\Router;

use DI\Container;
use Psr\Http\Message\ServerRequestInterface;

readonly class Router
{
    public function __construct(private RouteDispatcher $router, private Container $container)
    {
    }

    public function get(string $uri, array|callable $action, array $middlewares = []): void
    {
        $this->register('GET', $uri, $action, $middlewares);
    }

    public function post(string $uri, array|callable $action, array $middlewares = []): void
    {
        $this->register('POST', $uri, $action, $middlewares);
    }

    public function put(string $uri, array|callable $action, array $middlewares = []): void
    {
        $this->register('PUT', $uri, $action, $middlewares);
    }

    public function delete(string $uri, array|callable $action, array $middlewares = []): void
    {
        $this->register('DELETE', $uri, $action, $middlewares);
    }

    private function register(string $method, string $uri, array|callable $action, array $middlewares): void
    {
        $callback = is_callable($action)
            ? $action
            : fn(ServerRequestInterface $request, array $params) => $this->callAction($action[0], $action[1], $request, $params);

        $this->router->add($method, $uri, $callback, $middlewares);
    }

    private function callAction(string $class, string $method, ServerRequestInterface $request, array $params): mixed
    {
        return $this->container->get($class)->$method($request, ...array_values($params));
    }
}