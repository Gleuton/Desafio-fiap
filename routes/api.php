<?php

use FiapAdmin\Controllers\StudentController;

/**
 * @var $router
 */

$router->add('get', '/api/students', function () {
    return new StudentController()->index();
});
