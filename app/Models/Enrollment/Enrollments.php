<?php

namespace FiapAdmin\Models\Enrollment;

use FiapAdmin\Repositories\EnrollmentRepository;

readonly class Enrollments
{
    private EnrollmentRepository $repository;
    private EnrollmentValidator $validator;

    public function __construct()
    {
        $this->repository = new EnrollmentRepository();
        $this->validator = new EnrollmentValidator();
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

    public function delete(int $id): void
    {
        $this->repository->delete($id);
    }

    public function listByCurses(int $courseId): array
    {
        return $this->repository->listByCurses($courseId);
    }
}