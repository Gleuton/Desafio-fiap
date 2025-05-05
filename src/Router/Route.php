<?php

namespace Core\Router;

class Route
{
    private string $pattern;
    private $callback;
    private array $middlewares;

    public function __construct(string $pattern, callable $callback, array $middlewares = [])
    {
        $this->pattern = '/^' . preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^\/]+)', str_replace('/', '\/', $pattern)) . '$/';
        $this->callback = $callback;
        $this->middlewares = $middlewares;
    }

    public function matches(string $uri, ?array &$params = []): bool
    {
        if (preg_match($this->pattern, $uri, $matches)) {
            unset($matches[0]);
            $params = $matches;
            return true;
        }

        return false;
    }

    public function getCallback(): callable
    {
        return $this->callback;
    }

    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    public function getPattern(): string
    {
        return $this->pattern;
    }
}
