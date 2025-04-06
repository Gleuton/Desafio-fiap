<?php

namespace FiapAdmin\Models\Course;

use FiapAdmin\Repositories\CourseRepository;

readonly class Course
{
    private CourseRepository $repository;

    public function __construct()
    {
        $this->repository = new CourseRepository();
    }

    public function index(int $page, int $limit): array
    {
        $total = $this->repository->countTotal();
        $totalPages = ceil($total / $limit);

        $courses = $this->repository->findPaginated($page, $limit);

        return [
            'courses' => $courses,
            'totalPages' => $totalPages,
            'currentPage' => $page
        ];
    }
}