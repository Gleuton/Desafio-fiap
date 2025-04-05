<?php

namespace Core;

use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;

class Response
{
    public function __invoke(callable $callback, array $params): ResponseInterface
    {
        try {
            $result = $callback(...$params);
            if ($result instanceof ResponseInterface) {
                return $result;
            }
            if (is_string($result)) {
                return new HtmlResponse($result);
            }
            return new JsonResponse($result);
        } catch (\Exception $e) {
            $httpStatus = $e->getCode() ?: 500;
            if ($httpStatus < 100 || $httpStatus > 599) {
                $httpStatus = 500;
            }

            return new JsonResponse(
                [
                    'error' => $e->getMessage(),
                    'code' => $e->getCode()
                ],
                $httpStatus,
                [],
                JSON_THROW_ON_ERROR
            );
        }
    }
}