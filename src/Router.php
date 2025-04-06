<?php

namespace Core;

use Core\Exceptions\HttpException;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;

class Router implements RequestHandlerInterface
{
    private array $routes = [];

    public function add(string $method, string $pattern, callable $callback, array $middlewares = []): void
    {
        $pattern = '/^' . str_replace('/', '\/', $pattern) . '$/';
        $this->routes[strtolower($method)][$pattern] = [
            'callback' => $callback,
            'middlewares' => $middlewares
        ];
    }

    public function run(ServerRequestInterface $request): ResponseInterface
    {
        $method = strtolower($request->getMethod());
        $uri = $request->getUri()->getPath();

        if (!empty($this->routes[$method])) {
            return $this->forEachRoute($method, $uri, $request);
        }

        throw new HttpException('Page not found', 404);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->run($request);
    }

    private function forEachRoute(string $method, string $uri, ServerRequestInterface $request): ResponseInterface
    {
        foreach ($this->routes[$method] as $route => $data) {
            if (!preg_match($route, $uri, $params)) {
                continue;
            }

            unset($params[0]);

            $handler = $this;
            foreach (array_reverse($data['middlewares']) as $middlewareClass) {
                if (!class_exists($middlewareClass)) {
                    throw new Exception("Middleware {$middlewareClass} não encontrado");
                }

                $middleware = new $middlewareClass();

                if (!$middleware instanceof MiddlewareInterface) {
                    throw new Exception("Middleware {$middlewareClass} não implementa MiddlewareInterface");
                }

                $handler = static function (ServerRequestInterface $request) use ($middleware, $handler) {
                    return $middleware->process($request, $handler);
                };
            }

            $callback = $data['callback'];
            $result = $callback($request, $params);

            if ($result instanceof ResponseInterface) {
                return $result;
            }

            if (is_string($result)) {
                return new HtmlResponse($result);
            }

            return new JsonResponse($result);
        }

        throw new HttpException('Page not found', 404);
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }
}