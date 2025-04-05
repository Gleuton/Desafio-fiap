<?php

use Core\View;
use FiapAdmin\Middleware\AuthMiddleware;

/**
 * @var $router
 */

$router->add(
    'get',
    '/',
    function () {
        return View::render('main');
    },
    [AuthMiddleware::class]
);

$router->add(
    'get',
    '/students',
    function () {
        return View::render('students');
    },
    [AuthMiddleware::class]
);

$router->add('get', '/login', function () {
    return View::render('login');
});