<?php

use FiapAdmin\Controllers\AuthController;
use FiapAdmin\Controllers\CourseController;
use FiapAdmin\Controllers\EnrollmentsController;
use FiapAdmin\Controllers\StudentController;
use FiapAdmin\Middlewares\AuthMiddleware;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @var $router
 */

$router->add('GET', '/api/students/(\d+)', function (ServerRequestInterface $request, array $params) {
    $id = (int) $params[1];
    return new StudentController()->show($id);
}, [AuthMiddleware::class]);

$router->add('GET', '/api/students', function (ServerRequestInterface $request) {
    return new StudentController()->index($request);
}, [AuthMiddleware::class]);

$router->add('POST', '/api/students', function (ServerRequestInterface $request) {
    return new StudentController()->create($request);
}, [AuthMiddleware::class]);

$router->add('PUT', '/api/students/(\d+)', function (ServerRequestInterface $request, array $params) {
    $id = $params[1] ?? null;
    return new StudentController()->update($request, $id);
}, [AuthMiddleware::class]);

$router->add('DELETE', '/api/students/(\d+)', function (ServerRequestInterface $request, array $params) {
    $id = (int) $params[1];
    return new StudentController()->delete($id);
}, [AuthMiddleware::class]);

$router->add('GET', '/api/courses', function (ServerRequestInterface $request) {
    return new CourseController()->index($request);
}, [AuthMiddleware::class]);

$router->add('POST', '/api/courses', function (ServerRequestInterface $request) {
    return new CourseController()->create($request);
}, [AuthMiddleware::class]);

$router->add('GET', '/api/courses/(\d+)', function (ServerRequestInterface $request, array $params) {
    $id = (int) $params[1];
    return new CourseController()->show($id);
}, [AuthMiddleware::class]);

$router->add('PUT', '/api/courses/(\d+)', function (ServerRequestInterface $request, array $params) {
    $id = $params[1] ?? null;
    return new CourseController()->update($request, $id);
}, [AuthMiddleware::class]);

$router->add('DELETE', '/api/courses/(\d+)', function (ServerRequestInterface $request, array $params) {
    $id = (int) $params[1];
    return new CourseController()->delete($id);
}, [AuthMiddleware::class]);

$router->add('POST', '/api/enrollments', function (ServerRequestInterface $request) {
    return new EnrollmentsController()->create($request);
}, [AuthMiddleware::class]);

$router->add('DELETE', '/api/enrollments/(\d+)', function (ServerRequestInterface $request, array $params) {
    $id = (int) $params[1];
    return new EnrollmentsController()->delete($id);
}, [AuthMiddleware::class]);

$router->add('GET', '/api/courses/(\d+)/enrollments', function (ServerRequestInterface $request, array $params) {
    $courseId = (int) $params[1];
    return new EnrollmentsController()->listByCurses($courseId);
}, [AuthMiddleware::class]);

$router->add('POST', '/api/login', function (ServerRequestInterface $request) {
    return new AuthController()->login($request);
});

$router->add('GET', '/api/auth/check', function (ServerRequestInterface $request) {
    return new AuthController()->check($request);
});

$router->add('POST', '/api/logout', function (ServerRequestInterface $request) {
    return new AuthController()->logout($request);
});

