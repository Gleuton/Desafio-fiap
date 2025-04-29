<?php

use Core\View;

/**
 * @var $router
 */

$router->get('/', function () {
    return View::render('main');
});

$router->get('/students', function () {
    return View::render('students/list');
});

$router->get('/students/form', function () {
    return View::render('students/form');
});

$router->get('/courses', function () {
    return View::render('courses/list');
});

$router->get('/courses/form', function () {
    return View::render('courses/form');
});

$router->get('/enrollments', function () {
    return View::render('courses/enrollments');
});

$router->get('/enrollments/form', function () {
    return View::render('courses/enrollment');
});

$router->get('/login', function () {
    return View::render('login');
});