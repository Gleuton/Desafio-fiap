<?php
use Core\Router;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Core\Exceptions\HttpException;

require_once __DIR__ . '/vendor/autoload.php';

$router = new Router();

require_once __DIR__ . '/routes/web.php';
require_once __DIR__ . '/routes/api.php';

$request = ServerRequestFactory::fromGlobals();

try {
    $response = $router->handle($request);
} catch (HttpException $e) {
    $response = new JsonResponse([
        'error' => $e->getMessage(),
        'code' => $e->getCode()
    ], $e->getCode(), [], JSON_THROW_ON_ERROR);
}

$emitter = new SapiEmitter();
$emitter->emit($response);