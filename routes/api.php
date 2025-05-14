<?php

use FiapAdmin\Controllers\AuthController;
use FiapAdmin\Controllers\CourseController;
use FiapAdmin\Controllers\EnrollmentsController;
use FiapAdmin\Controllers\StudentController;
use FiapAdmin\Middlewares\AuthMiddleware;

/**
 * @var $router
 * @var $container
 */

$router->get('/api/students', [StudentController::class, 'index'], [AuthMiddleware::class]);
$router->get('/api/students/(\d+)', [StudentController::class, 'show'], [AuthMiddleware::class]);
$router->post('/api/students', [StudentController::class, 'create'], [AuthMiddleware::class]);
$router->put('/api/students/(\d+)', [StudentController::class, 'update'], [AuthMiddleware::class]);
$router->delete('/api/students/(\d+)', [StudentController::class, 'delete'], [AuthMiddleware::class]);

$router->get('/api/courses', [CourseController::class, 'index'], [AuthMiddleware::class]);
$router->post('/api/courses', [CourseController::class, 'create'], [AuthMiddleware::class]);
$router->get('/api/courses/(\d+)', [CourseController::class, 'show'], [AuthMiddleware::class]);
$router->put('/api/courses/(\d+)', [CourseController::class, 'update'], [AuthMiddleware::class]);
$router->delete('/api/courses/(\d+)', [CourseController::class, 'delete'], [AuthMiddleware::class]);

$router->post('/api/enrollments', [EnrollmentsController::class, 'create'], [AuthMiddleware::class]);
$router->delete('/api/enrollments/(\d+)', [EnrollmentsController::class, 'delete'], [AuthMiddleware::class]);
$router->get('/api/courses/(\d+)/enrollments', [EnrollmentsController::class, 'listByCurses'], [AuthMiddleware::class]);

$router->post('/api/login', [AuthController::class, 'login']);
$router->post('/api/refresh', [AuthController::class, 'refresh']);
$router->get('/api/auth/check', [AuthController::class, 'check'], [AuthMiddleware::class]);
$router->post('/api/logout', [AuthController::class, 'logout']);
