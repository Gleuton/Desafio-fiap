<?php

use FiapAdmin\Controllers\StudentController;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @var $router
 */

$router->add('GET', '/api/students', function() {
    return new StudentController()->index();
});
$router->add('POST', '/api/students', function (ServerRequestInterface $request) {
    return new StudentController()->create($request);
});
