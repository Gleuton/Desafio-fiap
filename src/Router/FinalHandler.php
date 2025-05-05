<?php

namespace Core\Router;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;

class FinalHandler implements RequestHandlerInterface
{
    private $callback;
    private array $params;

    public function __construct(callable $callback, array $params = [])
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
}
