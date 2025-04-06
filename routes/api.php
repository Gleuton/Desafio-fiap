<?php

use FiapAdmin\Controllers\StudentController;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @var $router
 */

$router->add('GET', '/api/students/(\d+)', function(ServerRequestInterface $request, array $params) {
    $id = (int)$params[1];
    return new StudentController()->show($id);
});

$router->add('GET', '/api/students', function() {
    return new StudentController()->index();
});
$router->add('POST', '/api/students', function (ServerRequestInterface $request) {
    return new StudentController()->create($request);
});

$router->add('PUT', '/api/students/(\d+)', function (ServerRequestInterface $request, array $params) {
    $id = $params[1] ?? null;
    return new StudentController()->update($request, $id);
});

$router->add('DELETE', '/api/students/(\d+)', function(ServerRequestInterface $request, array $params) {
    $id = (int)$params[1];
    return new StudentController()->delete($id);
});

