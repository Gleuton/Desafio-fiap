<?php

namespace Core\Router;

use Exception;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MiddlewareHandler implements RequestHandlerInterface
{
    private array $middlewares;
    private RequestHandlerInterface $finalHandler;

    public function __construct(array $middlewares, RequestHandlerInterface $finalHandler)
    {
        $this->middlewares = $middlewares;
        $this->finalHandler = $finalHandler;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $handler = array_reduce(
            array_reverse($this->middlewares),
            function (RequestHandlerInterface $next, string $middlewareClass) {
                if (!class_exists($middlewareClass)) {
                    throw new Exception("Middleware {$middlewareClass} não encontrado");
                }

                $middleware = new $middlewareClass();

                if (!$middleware instanceof MiddlewareInterface) {
                    throw new Exception("Middleware {$middlewareClass} não implementa MiddlewareInterface");
                }

                return new class($middleware, $next) implements RequestHandlerInterface {
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
            },
            $this->finalHandler
        );

        return $handler->handle($request);
    }
}
