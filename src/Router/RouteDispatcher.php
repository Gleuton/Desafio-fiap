<?php

namespace Core\Router;

use Core\Exceptions\HttpException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RouteDispatcher implements RequestHandlerInterface
{
    /**
     * @var Route[][]
     */
    private array $routes = [];

    public function add(string $method, string $pattern, callable $callback, array $middlewares = []): void
    {
        $route = new Route($pattern, $callback, $middlewares);
        $this->routes[strtolower($method)][] = $route;
    }

    public function run(ServerRequestInterface $request): ResponseInterface
    {
        $method = strtolower($request->getMethod());
        $uri = $request->getUri()->getPath();

        if (empty($this->routes[$method])) {
            throw new HttpException('Page not found', 404);
        }

        foreach ($this->routes[$method] as $route) {
            $params = [];
            if ($route->matches($uri, $params)) {
                $handler = new FinalHandler($route->getCallback(), $params);
                return new MiddlewareHandler($route->getMiddlewares(), $handler)->handle($request);
            }
        }

        throw new HttpException('Page not found', 404);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->run($request);
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }
}
