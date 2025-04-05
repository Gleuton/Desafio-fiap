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
        return View::render('layout');
    },
    [AuthMiddleware::class]
);

$router->add('get', '/login', function () {
    return View::render('login');
});