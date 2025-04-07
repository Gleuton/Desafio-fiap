<?php

use Core\View;

/**
 * @var $router
 */

$router->add(
    'get',
    '/',
    function () {
        return View::render('main');
    },
);

$router->add(
    'get',
    '/students',
    function () {
        return View::render('students/list');
    }
);

$router->add(
    'get',
    '/students/form',
    function () {
        return View::render('students/form');
    }
);

$router->add(
    'get',
    '/courses',
    function () {
        return View::render('courses/list');
    }
);

$router->add(
    'get',
    '/courses/form',
    function () {
        return View::render('courses/form');
    }
);

$router->add(
    'get',
    '/enrollments',
    function () {
        return View::render('courses/enrollments');
    }
);

$router->add(
    'get',
    '/enrollments/form',
    function () {
        return View::render('courses/enrollment');
    }
);

$router->add('get', '/login', function () {
    return View::render('login');
});