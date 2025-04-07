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

            $finalHandler = new class($data['callback'], $params) implements RequestHandlerInterface {
                private $callback;
                private array $params;

                public function __construct(callable $callback, array $params)
                {
                    $this->callback = $callback;
                    $this->params = $params;
                }

                public function handle(ServerRequestInterface $request): ResponseInterface
                {
                    $result = call_user_func($this->callback, $request, $this->params);

                    if ($result instanceof ResponseInterface) {
                        return $result;
                    }

                    if (is_string($result)) {
                        return new HtmlResponse($result);
                    }

                    return new JsonResponse($result);
                }
            };


            $handler = $finalHandler;
            foreach (array_reverse($data['middlewares']) as $middlewareClass) {
                if (!class_exists($middlewareClass)) {
                    throw new Exception("Middleware {$middlewareClass} não encontrado");
                }

                $middleware = new $middlewareClass();

                if (!$middleware instanceof MiddlewareInterface) {
                    throw new Exception("Middleware {$middlewareClass} não implementa MiddlewareInterface");
                }

                $handler = new class($middleware, $handler) implements RequestHandlerInterface {
                    private MiddlewareInterface $middleware;
                    private RequestHandlerInterface $nextHandler;

                    public function __construct(MiddlewareInterface $middleware, RequestHandlerInterface $nextHandler)
                    {
                        $this->middleware = $middleware;
                        $this->nextHandler = $nextHandler;
                    }

                    public function handle(ServerRequestInterface $request): ResponseInterface
                    {
                        return $this->middleware->process($request, $this->nextHandler);
                    }
                };
            }

            return $handler->handle($request);
        }

        throw new HttpException('Page not found', 404);
    }


    public function getRoutes(): array
    {
        return $this->routes;
    }
}