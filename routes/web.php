<?php

use Core\View;
use FiapAdmin\Middlewares\AuthMiddleware;

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
        return View::render('students/list');
    },
    [AuthMiddleware::class]
);

$router->add(
    'get',
    '/students/form',
    function () {
        return View::render('students/form');
    },
    [AuthMiddleware::class]
);

$router->add(
    'get',
    '/courses',
    function () {
        return View::render('courses/list');
    },
    [AuthMiddleware::class]
);

$router->add(
    'get',
    '/courses/form',
    function () {
        return View::render('courses/form');
    },
    [AuthMiddleware::class]
);

$router->add(
    'get',
    '/enrollments',
    function () {
        return View::render('courses/enrollments');
    },
    [AuthMiddleware::class]
);

$router->add(
    'get',
    '/enrollments/form',
    function () {
        return View::render('courses/enrollment');
    },
    [AuthMiddleware::class]
);

$router->add('get', '/login', function () {
    return View::render('login');
});