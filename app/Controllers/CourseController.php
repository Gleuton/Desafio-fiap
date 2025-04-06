<?php

namespace FiapAdmin\Controllers;

use FiapAdmin\Models\Course\Course;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;

class CourseController
{
    private Course $course;

    public function __construct()
    {
        $this->course = new Course();
    }

    public function index(ServerRequestInterface $request): JsonResponse
    {
        $page = $request->getQueryParams()['page'] ?? 1;
        $limit = $request->getQueryParams()['limit'] ?? 10;

        return new JsonResponse($this->course->index($page, $limit));
    }
}