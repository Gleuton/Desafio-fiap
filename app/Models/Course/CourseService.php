<?php

namespace FiapAdmin\Models\Course;

use FiapAdmin\Repositories\CourseRepository;
use FiapAdmin\Exceptions\ValidationException;

readonly class CourseService
{
    public function __construct(private CourseRepository $repository)
    {
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

    public function create(Course $course): array
    {
        try {
            $result = $this->repository->saveCourse($course);
            return [
                'success' => true,
                'data' => $result
            ];
        } catch (ValidationException $e) {
            return [
                'success' => false,
                'errors' => [$e->getField() => $e->getMessage()]
            ];
        }
    }

    public function findById(int $id): array
    {
        return $this->repository->findOneById($id);
    }

    public function update(Course $course): array
    {
        try {
            $result = $this->repository->updateCourse($course);
            return [
                'success' => $result,
                'data' => [
                    'name' => $course->name()->value(),
                    'description' => $course->description()->value()
                ]
            ];
        } catch (ValidationException $e) {
            return [
                'success' => false,
                'errors' => [$e->getField() => $e->getMessage()]
            ];
        }
    }

    public function delete(int $id): array
    {
        $errors = [];

        if ($this->repository->hasEnrollments($id)) {
            $errors['enrollment'] = 'Turma possui alunos matriculados e não pode ser excluído';
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        return [
            'success' => $this->repository->delete($id),
            'id' => $id
        ];
    }
}
