<?php
use Core\Router\RouteDispatcher;
use Core\Router\Router;
use DI\ContainerBuilder;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Core\Exceptions\HttpException;

require_once __DIR__ . '/vendor/autoload.php';

$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions(__DIR__ . '/src/config/container.php');

$container = $containerBuilder->build();

$dispatcher = new RouteDispatcher($container);
$router = new Router($dispatcher, $container);

require_once __DIR__ . '/routes/web.php';
require_once __DIR__ . '/routes/api.php';

$request = ServerRequestFactory::fromGlobals();

try {
    $response = $dispatcher->handle($request);
} catch (HttpException $e) {
    $response = new JsonResponse([
        'error' => $e->getMessage(),
        'code' => $e->getCode()
    ], $e->getCode(), [], JSON_THROW_ON_ERROR);
}

$emitter = new SapiEmitter();
$emitter->emit($response);