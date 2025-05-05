<?php

namespace Core\Router;

use Exception;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MiddlewareHandler implements RequestHandlerInterface
{
    private array $middlewares;
    private RequestHandlerInterface $finalHandler;
    private ContainerInterface $container;

    public function __construct(array $middlewares, RequestHandlerInterface $finalHandler, ContainerInterface $container)
    {
        $this->middlewares = $middlewares;
        $this->finalHandler = $finalHandler;
        $this->container = $container;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $handler = array_reduce(
            array_reverse($this->middlewares),
            function (RequestHandlerInterface $next, string $middlewareClass) {
                if (!class_exists($middlewareClass)) {
                    throw new Exception("Middleware {$middlewareClass} não encontrado");
                }
                $middleware = $this->container->get($middlewareClass);

                if (!$middleware instanceof MiddlewareInterface) {
                    throw new Exception("Middleware {$middlewareClass} não implementa MiddlewareInterface");
                }

                return new MiddlewareRequestHandler($middleware, $next);
            },
            $this->finalHandler
        );

        return $handler->handle($request);
    }
}