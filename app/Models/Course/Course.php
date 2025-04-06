<?php

namespace FiapAdmin\Models\Course;

use FiapAdmin\Repositories\CourseRepository;

readonly class Course
{
    private CourseRepository $repository;
    private CourseValidator $validator;

    public function __construct()
    {
        $this->repository = new CourseRepository();
        $this->validator = new CourseValidator();
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

    public function create(array $data): array
    {
        $validation = $this->validator->validateCreate($data);
        if (!empty($validation)) {
            return ['success' => false, 'errors' => $validation];
        }

        return [
            'success' => $this->repository->insert($data),
            'data' => $data
        ];
    }

    public function findById(int $id): array
    {
        return $this->repository->findOneById($id);
    }

    public function update(?int $id, array $data): array
    {
        $validation = $this->validator->validateUpdate($id, $data);
        if (!empty($validation)) {
            return ['success' => false, 'errors' => $validation];
        }

        return [
            'success' => $this->repository->update($id, $data),
            'data' => $data
        ];
    }

    public function delete(int $id): array {
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